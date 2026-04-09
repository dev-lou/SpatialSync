---
description: >
  Threat modelling skill using the STRIDE framework. Load BEFORE writing any new
  feature that involves authentication, user data, payments, file uploads, API
  endpoints, or admin functionality. Produces a structured threat model and
  mitigation plan. Load alongside ai-red-teaming.md for AI-specific threats.
  Trigger phrases: "threat model", "security review", "before I build", "is this secure".
---

# Threat Modelling 2026

> STRIDE threat model → trust boundary diagram → threat table → mitigations → 
> security test cases. Run before writing security-sensitive code.

---

## THE STRIDE MODEL

| Letter | Threat | Violates | Example |
|--------|--------|----------|---------|
| **S** | Spoofing | Authentication | Attacker impersonates admin user |
| **T** | Tampering | Integrity | Attacker modifies request payload |
| **R** | Repudiation | Non-repudiation | User denies making a purchase |
| **I** | Information disclosure | Confidentiality | API leaks other users' data |
| **D** | Denial of service | Availability | Flood endpoint to make it unreachable |
| **E** | Elevation of privilege | Authorization | Regular user accesses admin endpoint |

---

## STEP 1 — DECOMPOSE THE SYSTEM

Before applying STRIDE, map the system using Data Flow Diagrams (text format).

```markdown
## System Decomposition

### Actors (external entities)
- **Anonymous user** — unauthenticated visitor
- **Authenticated user** — logged-in member
- **Admin** — internal staff with elevated access
- **Third-party service** — Stripe, OAuth provider, email service

### Processes
- **Auth service** — login, registration, session management
- **API gateway** — routes, rate limiting, auth middleware
- **Business logic** — core feature processing
- **Background workers** — async jobs (emails, reports)

### Data stores
- **Primary DB** — user records, transactions
- **Cache** — session tokens, temporary data
- **File storage** — user uploads (S3 / R2)
- **Audit log** — immutable event log

### Trust boundaries (draw these explicitly)
─────────────────────────────────────────
  [ Internet / untrusted zone ]
  ↓ HTTPS ↓
  [ Load balancer / WAF ]          ← Trust boundary 1
  ↓
  [ API gateway + rate limiter ]   ← Trust boundary 2
  ↓
  [ Application server ]
  ↓                    ↓
  [ Primary DB ]   [ External APIs ]  ← Trust boundary 3
─────────────────────────────────────────
```

---

## STEP 2 — APPLY STRIDE TO EACH COMPONENT

For each process and data store, systematically apply all six STRIDE categories.

### Template

```markdown
### Component: [Name]

**S — Spoofing**
- Threat: [describe]
- Likelihood: High / Medium / Low
- Impact: High / Medium / Low
- Mitigation: [specific control]

**T — Tampering**
- Threat: [describe]
- …

**R — Repudiation**
- Threat: [describe]
- …

**I — Information Disclosure**
- Threat: [describe]
- …

**D — Denial of Service**
- Threat: [describe]
- …

**E — Elevation of Privilege**
- Threat: [describe]
- …
```

### Example: Login Endpoint (`POST /auth/login`)

```markdown
**S — Spoofing**
Threat: Attacker brute-forces credentials or uses credential stuffing from leaked DB.
Mitigation:
  - Rate limit: 5 attempts per IP per minute, 20 per account per hour.
  - Progressive lockout: 15 min lock after 10 failures.
  - CAPTCHA after 3 failures.
  - Breach-password check via HaveIBeenPwned API on registration.

**T — Tampering**
Threat: Attacker intercepts and modifies login request (MITM).
Mitigation:
  - HTTPS enforced (HSTS header, min TLS 1.2).
  - No sensitive data in URL params.
  - CSRF token on login form.

**R — Repudiation**
Threat: User claims they never logged in (audit dispute).
Mitigation:
  - Immutable audit log: { userId, ip, userAgent, timestamp, success } on every attempt.
  - Log stored in separate append-only store, not deletable via app.

**I — Information Disclosure**
Threat: Error message reveals whether email exists ("No account found" vs "Wrong password").
Mitigation:
  - Generic error: "Invalid email or password." — always identical.
  - Timing-safe comparison to prevent timing attacks on password check.
  - No stack traces in production responses.

**D — Denial of Service**
Threat: Attacker floods login endpoint to exhaust server / DB connections.
Mitigation:
  - Rate limiting per IP (see S).
  - Connection pool limits.
  - WAF rule: block IPs with > 100 req/min to /auth/*.

**E — Elevation of Privilege**
Threat: Attacker obtains admin session by exploiting session fixation or token forgery.
Mitigation:
  - Regenerate session ID on login (invalidate pre-auth session).
  - JWT: short expiry (15 min access, 7 day refresh). HS256 minimum, RS256 preferred.
  - Role stored server-side, never in JWT payload without server validation.
  - Admin actions require step-up auth (re-prompt password or MFA).
```

---

## STEP 3 — THREAT PRIORITY MATRIX

After documenting all threats, prioritise by risk = Likelihood × Impact.

```markdown
## Threat Priority Matrix

| ID | Threat | STRIDE | Likelihood | Impact | Risk | Status |
|----|--------|--------|-----------|--------|------|--------|
| T1 | Credential stuffing on login | S | High | High | 🔴 Critical | Mitigated |
| T2 | IDOR on /api/orders/{id} | E | High | High | 🔴 Critical | Open |
| T3 | SQL injection in search | T | Medium | High | 🟠 High | Mitigated |
| T4 | JWT secret brute-force | S | Low | High | 🟡 Medium | Mitigated |
| T5 | Unauth file download | I | Medium | Medium | 🟡 Medium | Open |
| T6 | Upload of malicious files | T | Low | High | 🟡 Medium | Open |
| T7 | Admin endpoint DoS | D | Low | Medium | 🔵 Low | Accepted |
```

**Action rule:** All 🔴 Critical threats must be mitigated before code is written.
🟠 High threats must be mitigated before feature ships.

---

## STEP 4 — MITIGATION PATTERNS (Code Library)

### IDOR Prevention (T — Tampering / E — Elevation of Privilege)

```javascript
// ❌ Insecure — trusts user-supplied ID directly
app.get('/api/orders/:id', async (req, res) => {
  const order = await db.orders.findById(req.params.id);
  res.json(order);
});

// ✅ Secure — scopes query to authenticated user
app.get('/api/orders/:id', requireAuth, async (req, res) => {
  const order = await db.orders.findOne({
    id:     req.params.id,
    userId: req.user.id,      // ← ownership check
  });
  if (!order) return res.status(404).json({ error: 'Not found' });
  res.json(order);
});
```

### Input Validation & Injection Prevention (T — Tampering)

```javascript
// ✅ Parameterised queries — never string concatenation
const user = await db.query(
  'SELECT * FROM users WHERE email = $1',
  [req.body.email]   // never: `... WHERE email = '${email}'`
);

// ✅ Schema validation on all inputs
import { z } from 'zod';
const LoginSchema = z.object({
  email:    z.string().email().max(254),
  password: z.string().min(8).max(128),
});
const parsed = LoginSchema.safeParse(req.body);
if (!parsed.success) return res.status(400).json(parsed.error.flatten());
```

### Rate Limiting (D — Denial of Service)

```javascript
import rateLimit from 'express-rate-limit';
import RedisStore from 'rate-limit-redis';

const authLimiter = rateLimit({
  windowMs:   60 * 1000,     // 1 minute
  max:        5,             // 5 attempts per IP
  standardHeaders: true,
  legacyHeaders:   false,
  store: new RedisStore({ client: redisClient }),
  handler: (req, res) => res.status(429).json({
    error: 'Too many attempts. Try again in 1 minute.',
  }),
});
app.post('/auth/login', authLimiter, loginHandler);
```

### Secrets Management (I — Information Disclosure)

```javascript
// ❌ Never commit secrets
const apiKey = 'sk-proj-abc123';                // ❌

// ✅ Environment variables, never in source
const apiKey = process.env.OPENAI_API_KEY;      // ✅

// ✅ Validate secrets are present at startup
function validateEnv() {
  const required = ['DATABASE_URL', 'JWT_SECRET', 'OPENAI_API_KEY'];
  const missing  = required.filter(k => !process.env[k]);
  if (missing.length) throw new Error(`Missing env vars: ${missing.join(', ')}`);
}
validateEnv(); // call before app.listen()
```

### Audit Logging (R — Repudiation)

```javascript
// Append-only audit log — critical for repudiation resistance
async function auditLog(event) {
  await db.auditLogs.insertOne({
    ...event,
    timestamp:  new Date().toISOString(),
    id:         crypto.randomUUID(),
    // Never allow update or delete on this table — DB-level constraint
  });
}
// Usage
await auditLog({ type: 'login.success', userId, ip, userAgent });
await auditLog({ type: 'order.placed',  userId, orderId, amount });
await auditLog({ type: 'admin.delete',  adminId, targetUserId });
```

### File Upload Security (T — Tampering)

```javascript
import fileType from 'file-type';
import crypto from 'crypto';

async function validateUpload(buffer, declaredMime) {
  // 1. Check actual file type — never trust Content-Type header
  const detected = await fileType.fromBuffer(buffer);
  const ALLOWED  = new Set(['image/jpeg', 'image/png', 'image/webp', 'application/pdf']);

  if (!detected || !ALLOWED.has(detected.mime)) {
    throw new Error(`File type not allowed: ${detected?.mime ?? 'unknown'}`);
  }
  if (detected.mime !== declaredMime) {
    throw new Error('Declared and actual MIME type mismatch');
  }

  // 2. Size limit
  if (buffer.length > 10 * 1024 * 1024) throw new Error('File exceeds 10 MB limit');

  // 3. Randomise stored filename — never trust original filename
  const ext  = detected.ext;
  const name = `${crypto.randomUUID()}.${ext}`;
  return { name, mime: detected.mime };
}
```

---

## STEP 5 — GENERATE THE THREAT MODEL DOCUMENT

```markdown
# Threat Model: [Feature Name]

**Date:** {date}
**Author:** {name / AI}
**Scope:** {endpoints, components, data stores in scope}
**Out of scope:** {what is excluded and why}

---

## Architecture Overview
{data flow diagram — text format}

## Trust Boundaries
{list with descriptions}

## Threat Inventory
{full threat table from Step 3}

## Open Threats (not yet mitigated)
{subset of table where Status = Open}

## Security Test Cases
{list of tests derived from threats — link to ai-red-teaming.md for AI-specific ones}

## Sign-off Checklist
- [ ] All 🔴 Critical threats mitigated
- [ ] All 🟠 High threats mitigated or accepted with documented rationale
- [ ] Audit logging implemented for all sensitive operations
- [ ] Rate limiting implemented on all auth endpoints
- [ ] Input validation with schema on all endpoints
- [ ] Secrets in env vars, not in code
- [ ] HTTPS enforced with HSTS
- [ ] Threat model reviewed by second person before feature ships
```

---

## THREAT MODELLING TRIGGERS

Run a threat model whenever:
- Adding a new authentication flow or OAuth provider
- Handling payments or financial data
- Implementing file upload / user-generated content
- Creating admin or elevated-privilege endpoints
- Integrating with third-party APIs that receive user data
- Adding any AI / LLM feature (→ also run ai-red-teaming.md)
- Storing sensitive PII (health, financial, location data)
