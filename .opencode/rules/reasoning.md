---
description: >
  Meta-skill: forces structured planning, constraint elicitation, and self-critique
  before writing any code or generating any artefact. Load this file FIRST on any
  non-trivial task — any request involving architecture, new features, refactoring,
  security decisions, or ambiguous requirements. Prevents premature code generation
  and "happy-path tunnel vision".
---

# Reasoning — Meta-Skill 2026

> Structured thinking protocol: restate → constrain → diverge → evaluate → propose → review.
> Never write a line of code until all six steps are complete.

---

## WHY THIS EXISTS

AI models are pattern-matchers. Without a forcing function they will:
- Jump to the most common solution (which may be wrong for this context)
- Miss implicit constraints (auth model, existing tech debt, team conventions)
- Generate confident but incorrect code in unfamiliar domains
- Fail to surface the ambiguity that a human expert would immediately flag

This skill installs that forcing function.

---

## THE SIX-STEP REASONING WORKFLOW

Run every step in order. Do not skip. Do not merge steps.

---

### STEP 1 — RESTATE THE GOAL

Paraphrase the request in your own words. Be specific. Include:
- **What** the user wants to achieve (outcome, not activity)
- **Who** is the end user or beneficiary
- **What success looks like** (measurable if possible)
- **What is explicitly out of scope** (if stated)

```
Example restatement:
"The user wants a multi-step checkout form for an e-commerce site (Premium Modern
design language). Success = user can complete purchase in < 90 s on mobile.
Out of scope: payment processing logic — only the UI layer."
```

If the restatement changes what the user seemed to ask for, **stop and confirm** before continuing.

---

### STEP 2 — LIST ALL CONSTRAINTS

Enumerate every constraint, grouped by category. Ask clarifying questions for anything
marked **[?]**.

```markdown
## Constraints

### Technical
- Framework: Next.js 14 App Router [confirmed / ?]
- Styling: Tailwind v4 + CSS custom properties [confirmed / ?]
- Browser support: modern evergreen + Safari 16+ [?]
- Must work without JS? (progressive enhancement) [?]

### Design System
- Design language: Premium Modern [confirmed / ?]
- Tokens already defined in tokens.css? [confirmed / ?]
- Dark mode required? [confirmed — law #6]

### Business / Product
- Must ship by: [?]
- Analytics / tracking requirements? [?]
- Legal / compliance (GDPR, WCAG, HIPAA)? [?]

### Team
- Will other devs maintain this? Skill level? [?]
- Existing component library to reuse? [?]
- PR / code review process affects scope? [?]
```

**Rule:** Only proceed to Step 3 after all `[?]` items are either answered or explicitly
deferred with a stated assumption.

---

### STEP 3 — GENERATE ≥ 3 APPROACHES

Propose at least three meaningfully different solutions. Do not evaluate yet — just describe.
Each approach should be distinct in **strategy**, not just implementation detail.

```markdown
## Approach A — [Name]
Description: …
Stack/technique: …
Rough complexity: Low / Medium / High

## Approach B — [Name]
Description: …
Stack/technique: …
Rough complexity: Low / Medium / High

## Approach C — [Name] (stretch / experimental)
Description: …
Stack/technique: …
Rough complexity: Low / Medium / High
```

---

### STEP 4 — EVALUATE TRADE-OFFS

Create a trade-off matrix. Score each approach on the axes that matter for **this**
request (not a generic list).

```markdown
| Criterion              | Approach A | Approach B | Approach C |
|------------------------|-----------|-----------|-----------|
| Implementation speed   | ★★★★☆     | ★★☆☆☆     | ★☆☆☆☆     |
| Maintainability        | ★★★☆☆     | ★★★★☆     | ★★★★★     |
| Performance (CWV)      | ★★★☆☆     | ★★★★☆     | ★★★☆☆     |
| Accessibility          | ★★★★☆     | ★★★★★     | ★★★☆☆     |
| Aligns with design sys | ★★★★★     | ★★★☆☆     | ★★☆☆☆     |
| Risk / unknowns        | Low       | Medium    | High      |
```

State the **recommended approach** and why in one sentence.

---

### STEP 5 — PROPOSE THE SOLUTION

Write a concrete implementation plan **before** writing code:

```markdown
## Implementation Plan

### Files to create
- `components/CheckoutForm/index.tsx` — root component
- `components/CheckoutForm/StepOne.tsx` — address
- `components/CheckoutForm/StepTwo.tsx` — payment
- `components/CheckoutForm/useCheckout.ts` — state machine
- `components/CheckoutForm/CheckoutForm.test.tsx` — integration tests

### Files to modify
- `app/checkout/page.tsx` — mount component
- `tokens.css` — add --checkout-step-indicator token if needed

### Sequence
1. Token audit — confirm all design tokens exist
2. Markup skeleton — accessible form structure first
3. State machine — useCheckout hook
4. Styling — tokens only, no hardcoded values
5. States — all 9 states per law #13
6. Tests — Playwright happy path + error path
7. axe-core audit — zero violations before PR

### Estimated effort
S / M / L / XL — [state and justify]
```

---

### STEP 6 — SELF-REVIEW (RED-TEAM YOUR OWN PLAN)

Before writing any code, run the following checklist against the plan:

```markdown
## Self-Review Checklist

### Correctness
- [ ] Does the plan solve the stated goal (Step 1)?
- [ ] Does it handle the unhappy path / error states?
- [ ] Does it handle empty states? (law #19)
- [ ] Does it degrade gracefully without JS?

### Design System Compliance
- [ ] All spacing on 8px scale? (law #18)
- [ ] No hardcoded colours — only CSS tokens?
- [ ] Dark mode accounted for? (law #6)
- [ ] All 9 component states planned? (law #13)

### Performance
- [ ] Any N+1 renders / unnecessary re-renders?
- [ ] Images use <picture> + AVIF + WebP? (law #20)
- [ ] No animation of top/left/width/height? (law #12)

### Security
- [ ] No user input reflected without sanitisation?
- [ ] No secrets in client-side code?
- [ ] Auth state checked before rendering sensitive data?

### Accessibility
- [ ] ARIA landmarks present?
- [ ] Focus management handled (especially for multi-step)?
- [ ] Keyboard navigation tested in the plan?
- [ ] Colour contrast meets 4.5:1? (law #16)

### What could go wrong?
State ≥ 2 specific risks and how the plan mitigates them.
```

Only after the self-review passes — or risks are explicitly accepted — does code generation begin.

---

## CLARIFYING QUESTIONS — WHEN TO ASK

Ask before planning (not after) when any of the following are true:

| Signal | Question to ask |
|--------|----------------|
| No design language stated | "Which of the 7 design languages applies?" |
| No framework stated | "What is the tech stack (framework, CSS approach, TypeScript?)?" |
| Scope is very large | "Should I break this into phases? Which phase first?" |
| Two valid interpretations | "Do you mean X or Y? Here's how they differ: …" |
| Security-sensitive domain | "What is the auth model and trust boundary for this feature?" |
| Performance-critical | "What are the target Lighthouse / CWV scores?" |

**Format for clarifying questions:**
```
Before I plan this, I need to clarify 2 things:
1. [Question] — I'll assume [X] if not specified.
2. [Question] — affects [Y] significantly.
Reply with answers or "use your assumptions" to proceed.
```

Never ask more than 3 questions at once. Group related questions.

---

## REASONING SHORTCUTS (for simple tasks)

For requests that are clearly simple (< 30 min, single component, fully specified),
use the abbreviated workflow:

```markdown
**Goal:** [one sentence]
**Constraints:** [bullet list, ≤ 5 items]
**Approach:** [chosen approach in one sentence]
**Risk:** [one risk + mitigation]
**Plan:** [file list + sequence, ≤ 8 items]
→ Proceeding to implementation.
```

If any item in the abbreviated workflow reveals complexity, fall back to the full six-step process.

---

## ANTI-PATTERNS TO AVOID

| Anti-pattern | Symptom | Correct behaviour |
|---|---|---|
| Premature coding | Writing code before constraints are clear | Run Steps 1–2 first |
| Approach anchoring | Only proposing one solution | Always generate ≥ 3 (Step 3) |
| Happy-path only | No error/empty states in plan | Step 6 checklist enforces this |
| Assumption invisibility | Making assumptions without stating them | State every assumption explicitly |
| Over-engineering | Proposing XL solution for S problem | Step 4 matrix exposes this |
| Under-engineering | Skipping tests, a11y, states | Step 6 checklist catches this |
