---
description: Skill to verify third-party libraries before recommending them. Checks npm/GitHub existence, update recency, download counts, and license compatibility.
---

# Library Verification Skill

> Before recommending any third-party library, AI must verify its health and suitability.

---

## VERIFICATION CHECKLIST

**Rule**: Never recommend a library without completing this verification. Output results in a standardized table.

### Required Checks

| Check | Criteria | Action if Failed |
|-------|----------|------------------|
| **Exists** | Package found on npm or GitHub | Do not recommend |
| **Last Update** | ≤ 12 months ago | Warn user, suggest alternatives |
| **Downloads** | ≥ 10,000/week (popular) or ≥ 1,000/week (niche) | Warn user |
| **License** | MIT, Apache 2.0, BSD, ISC, or MIT-compatible | Do not recommend without explicit user consent |
| **Bundle Size** | Reasonable for category | Note in recommendation |
| **TypeScript** | Has types (built-in or @types/*) | Note if missing |

---

## OUTPUT FORMAT

Always output verification results in this table format:

```markdown
### Library Verification Report

| Library | Exists? | Weekly Downloads | Last Update | License | Bundle Size | Recommendation |
|---------|---------|------------------|-------------|---------|-------------|----------------|
| fuse.js | ✅ Yes | 1.2M | 2 months ago | Apache-2.0 | 24KB | ✅ Recommended |
| some-lib | ✅ Yes | 500 | 18 months ago | MIT | 8KB | ⚠️ Low activity |
| fake-lib | ❌ No | N/A | N/A | N/A | N/A | ❌ Does not exist |
```

---

## VERIFICATION PROCESS

### Step 1: Check npm Registry

```javascript
// Verification script (run via Node.js or use npm API)
async function verifyPackage(packageName) {
  const response = await fetch(`https://registry.npmjs.org/${packageName}`);
  
  if (!response.ok) {
    return { exists: false, reason: 'Package not found on npm' };
  }
  
  const data = await response.json();
  const latest = data['dist-tags']?.latest;
  const latestVersion = data.versions?.[latest];
  
  return {
    exists: true,
    name: data.name,
    version: latest,
    license: latestVersion?.license || 'Unknown',
    lastPublish: data.time?.[latest],
    repository: latestVersion?.repository?.url,
    types: latestVersion?.types || latestVersion?.typings || 
           data.name.startsWith('@types/') || 
           !!latestVersion?.devDependencies?.['@types/' + data.name]
  };
}
```

### Step 2: Check Download Statistics

```javascript
async function getDownloadStats(packageName) {
  const response = await fetch(
    `https://api.npmjs.org/downloads/point/last-week/${packageName}`
  );
  
  if (!response.ok) return { downloads: 0 };
  
  const data = await response.json();
  return { downloads: data.downloads };
}
```

### Step 3: Assess Recency

```javascript
function assessRecency(lastPublishDate) {
  const now = new Date();
  const published = new Date(lastPublishDate);
  const monthsAgo = (now - published) / (1000 * 60 * 60 * 24 * 30);
  
  if (monthsAgo <= 6) return { status: 'active', label: '✅ Active' };
  if (monthsAgo <= 12) return { status: 'maintained', label: '⚠️ Maintained' };
  return { status: 'stale', label: '❌ Stale (>12 months)' };
}
```

---

## CATEGORY-SPECIFIC THRESHOLDS

| Category | Min Downloads/Week | Notes |
|----------|-------------------|-------|
| UI Framework | 100,000+ | React, Vue, Svelte |
| Component Library | 50,000+ | shadcn, Radix, Headless UI |
| Utility Library | 10,000+ | lodash, date-fns, fuse.js |
| Animation | 5,000+ | GSAP, Framer Motion |
| Niche/Specialized | 1,000+ | Specific use case |
| New/Emerging | 500+ | Requires manual review |

---

## LICENSE COMPATIBILITY

### Allowed Licenses (No Restrictions)

```
MIT, Apache-2.0, BSD-2-Clause, BSD-3-Clause, ISC, 0BSD, Unlicense, CC0-1.0
```

### Allowed with Attribution

```
CC-BY-4.0, MPL-2.0
```

### Requires Review

```
LGPL-2.1, LGPL-3.0 — OK for dynamic linking, review for bundling
```

### Not Recommended for Commercial

```
GPL-2.0, GPL-3.0, AGPL-3.0 — Copyleft, requires legal review
```

---

## ALTERNATIVES SUGGESTION

When a library fails verification, suggest alternatives:

```markdown
### ❌ `moment.js` Failed Verification

**Reason**: Deprecated, large bundle size (329KB)

**Recommended Alternatives**:
| Library | Size | Status | Migration Guide |
|---------|------|--------|-----------------|
| date-fns | 13KB (tree-shakeable) | ✅ Active | [Link] |
| dayjs | 2KB | ✅ Active | [Link] |
| Temporal API | Native | 🔄 Emerging | [MDN] |
```

---

## INTEGRATION WITH TOKEN SYSTEM

When recommending UI libraries, verify they support:

1. **CSS Custom Properties** — Can use `var(--token)` for theming
2. **Dark Mode** — Supports `[data-theme]` or similar
3. **Motion Control** — Respects `prefers-reduced-motion`

```markdown
### Token Compatibility Check

| Feature | Supported? | Notes |
|---------|------------|-------|
| CSS Variables | ✅ Yes | Use `--component-*` tokens |
| Dark Mode | ✅ Yes | Via data attribute |
| Reduced Motion | ⚠️ Partial | May need CSS override |
```

---

## AI WORKFLOW

```
1. User requests library recommendation
2. AI identifies candidate libraries
3. FOR EACH candidate:
   a. Check npm registry
   b. Get download stats
   c. Verify license
   d. Check last update
   e. Assess bundle size
4. Output verification table
5. Provide recommendation with rationale
6. If all fail, suggest building custom solution
```

---

## EXAMPLE OUTPUT

```markdown
## Library Verification: Date Picker

User requested a date picker library compatible with the design system.

### Verification Results

| Library | Exists? | Downloads/wk | Updated | License | Size | Tokens? | Verdict |
|---------|---------|--------------|---------|---------|------|---------|---------|
| flatpickr | ✅ | 890K | 4mo | MIT | 16KB | ✅ | ✅ Recommended |
| react-datepicker | ✅ | 1.2M | 2mo | MIT | 45KB | ⚠️ | ✅ Good |
| pikaday | ✅ | 95K | 14mo | MIT | 12KB | ❌ | ⚠️ Stale |
| vanillajs-datepicker | ✅ | 8K | 6mo | MIT | 20KB | ✅ | ✅ Good |

### Recommendation

**Use Flatpickr** — Already in CDN stack, excellent token support via CSS 
overrides, actively maintained, MIT license.

**Token Integration**:
```css
.flatpickr-calendar {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  font-family: var(--font-body);
}
```
```

---

## CHECKLIST FOR AI

Before recommending any library:

- [ ] Verified package exists on npm
- [ ] Checked weekly download count
- [ ] Confirmed last update within 12 months
- [ ] License is compatible
- [ ] Bundle size is reasonable
- [ ] TypeScript support noted
- [ ] Token compatibility assessed
- [ ] Alternatives provided if needed
