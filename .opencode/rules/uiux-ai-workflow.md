# UI/UX AI Workflow 2026

> AI-native engineering patterns: using AI tools (Copilot, Claude, Cursor, Lovable)
> as design-system-aware partners, not generic code generators.
> Token enforcement, boilerplate generation, prompt patterns, and review checklists.

---

## CORE PRINCIPLE — Constrain the AI

An AI that doesn't know your design system will generate:
- `padding: 10px` instead of `var(--space-3)`
- `color: #3B82F6` instead of `var(--accent)`
- `border-radius: 8px` instead of `var(--radius-md)`
- `Inter` font instead of your brand font
- `window.alert()` instead of SweetAlert2

**Solution:** Prime every AI prompt with your system context. This skill IS that context.

---

## STEP 1 — AI SYSTEM PROMPT (paste at start of every session)

```
You are a senior frontend engineer building [PROJECT NAME].

Design language: [LANGUAGE — e.g., Premium Modern]
Tech stack: [e.g., Next.js 14, Tailwind v4, TypeScript]

RULES — non-negotiable:
1. All spacing uses CSS tokens: var(--space-1) through var(--space-40). Never arbitrary px.
2. All colors use semantic tokens: var(--bg), var(--surface), var(--text), var(--accent), etc.
3. All fonts via: var(--font-display), var(--font-body), var(--font-mono).
4. Border radius via: var(--radius-sm), var(--radius-md), var(--radius-lg).
5. No window.alert/confirm/prompt — use the swal object from uiux-components.md.
6. Every component handles all 8 states (default/hover/focus/active/loading/disabled/error/success).
7. Every <img> has width, height, loading, alt.
8. No inline styles except for dynamic/computed values.
9. Mobile-first: start at 390px.
10. Dark mode via [data-theme="dark"] on <html>. Colors must work in both themes.

Token file excerpt:
[paste relevant section of tokens.css here]
```

---

## STEP 2 — PROMPT PATTERNS

### Generate a New Component

```
Create a [COMPONENT NAME] component for a [LANGUAGE] design system.

Requirements:
- Uses only var(--*) tokens for colors, spacing, radius
- Handles all 8 states: default, hover, focus, active, loading, disabled, error, success
- Mobile-first, responsive
- Accessible: correct ARIA roles, keyboard navigable
- Follows the button/card/form patterns in [uiux-interactive.md / uiux-states.md]

Output: HTML + CSS (no Tailwind). Use BEM-adjacent naming (.component__element--modifier).
```

### Review Existing Code for Token Compliance

```
Review this component for design system violations:

[PASTE CODE]

Check for:
1. Hardcoded px values not on the 8px scale
2. Hardcoded color hex values instead of CSS variables
3. Missing component states (hover, focus, disabled)
4. Missing accessibility (aria-*, role, focus-visible)
5. Images missing width/height/loading/alt
6. window.alert/confirm usage
7. transition: all (should specify properties)
8. Arbitrary font-size/font-weight not using scale tokens
9. Non-mobile-first media queries (max-width instead of min-width)

Output: List of violations with exact line references and correct token replacements.
```

### Upgrade AI-Slop to Production

```
This component was AI-generated and has quality issues. Upgrade it to production standard:

[PASTE CODE]

Apply:
1. Replace all hardcoded values with tokens from this system
2. Add missing hover/focus/disabled states
3. Fix any accessibility issues
4. Make it mobile-first
5. Ensure dark mode works
6. Replace any window.alert with swal.toast or swal.confirm

Keep the same functionality. Only improve code quality.
```

### Generate Page Section

```
Generate the [SECTION NAME] section for a [LANGUAGE] website.

Section purpose: [e.g., "showcase 3 pricing tiers"]
Content: [describe what goes in it]
Unique requirement: [e.g., "use bento grid", "feature an image background with overlay"]

Rules:
- Use placeholder images from: https://picsum.photos/seed/[unique-seed]/[w]/[h]
- Use AOS data-aos="fade-up" for entrance animations
- Section has padding-block from the spacing table for [LANGUAGE]
- No Lorem ipsum — use realistic placeholder content for [INDUSTRY]
```

---

## STEP 3 — LOVABLE / V0 / BOLT PROMPT PATTERNS

These tools generate full UIs. Prime them before generating.

### Lovable System Prompt

```
Build a [TYPE] app using these exact constraints:

Design language: [LANGUAGE]
Color tokens:
  Light: --bg: [VALUE]; --surface: [VALUE]; --accent: [VALUE]; --text: [VALUE];
  Dark:  --bg: [VALUE]; --surface: [VALUE]; --accent: [VALUE]; --text: [VALUE];
Font: [Display font] for headings, [Body font] for body text.
Border radius: [VALUE]px on all components.
Spacing: 8px grid (4, 8, 12, 16, 24, 32, 48, 64px only).

Do NOT use:
- window.alert — use toast notifications
- arbitrary pixel values outside the spacing scale
- inline style colors — use CSS variables
- generic fonts (Inter, Arial, Roboto) — use specified fonts

Include: dark/light mode toggle, mobile responsive, accessible.
```

### v0 / Cursor Prompt

```
/ui [COMPONENT]

Constraints:
- Design language: [LANGUAGE] from this token system
- All values via CSS custom properties (var(--accent), not #hex)
- shadcn/ui components as base if available, else Radix UI headless
- All 8 component states handled
- Tailwind v4 with @theme tokens OR plain CSS modules
```

---

## STEP 4 — AI CODE REVIEW CHECKLIST

Use this as a PR review checklist when AI generated the code.

### Token Compliance Scan

```bash
# Search for hardcoded values in CSS/JSX (run in terminal)
# Flags potential violations for review:

grep -rn "color: #"          src/ --include="*.css" --include="*.module.css"
grep -rn "padding: [0-9]"    src/ --include="*.css"
grep -rn "margin: [0-9]"     src/ --include="*.css"
grep -rn "font-size: [0-9]"  src/ --include="*.css"
grep -rn "border-radius: [0-9]" src/ --include="*.css"
grep -rn "window\.alert"     src/ --include="*.js" --include="*.jsx" --include="*.ts" --include="*.tsx"
grep -rn "transition: all"   src/ --include="*.css"
grep -rn "localhost"         src/ --include="*.js" --include="*.jsx" --include="*.ts" --include="*.tsx"
```

### Quick Mental Review (5 minutes)

```
□ Does it use var(--*) or hardcoded values?
□ Does the component look right in dark mode?
□ Is it usable at 390px width?
□ Can it be navigated by keyboard alone?
□ Does the loading state show something (skeleton/spinner)?
□ What happens if the API call fails? (error state)
□ Are all images lazy-loaded with dimensions?
□ Does it use the correct border-radius for this design language?
□ Does it feel native to the design language or generic?
```

---

## STEP 5 — FIGMA ↔ CODE TOKEN SYNC

### Figma Variables → CSS Tokens

Figma Variables (design) and CSS Custom Properties (code) should be identical.

```
Figma Variable Name  →  CSS Token
────────────────────────────────────────
Color/Background     →  --bg
Color/Surface        →  --surface
Color/Surface Up     →  --surface-up
Color/Text Primary   →  --text
Color/Text Secondary →  --text-2
Color/Muted          →  --muted
Color/Border         →  --border
Color/Accent         →  --accent
Color/Accent Hover   →  --accent-h

Spacing/4            →  --space-1 (4px)
Spacing/8            →  --space-2 (8px)
Spacing/16           →  --space-4 (16px)
... etc

Radius/Small         →  --radius-sm
Radius/Medium        →  --radius-md
Radius/Large         →  --radius-lg
```

### Token Export (Figma Plugin → Style Dictionary)

```bash
# Install Style Dictionary
npm install --save-dev style-dictionary

# tokens.json exported from Figma via Tokens Studio or Variables to JSON plugin
```

```json
// tokens.json (DTCG format — W3C standard)
{
  "color": {
    "bg":      { "$value": "#FFFFFF",  "$type": "color" },
    "surface": { "$value": "#F7F8FA",  "$type": "color" },
    "accent":  { "$value": "#0066FF",  "$type": "color" }
  },
  "spacing": {
    "space-4":  { "$value": "16px",  "$type": "dimension" },
    "space-6":  { "$value": "24px",  "$type": "dimension" }
  },
  "borderRadius": {
    "radius-md": { "$value": "12px", "$type": "dimension" }
  }
}
```

```javascript
// style-dictionary.config.js
export default {
  source: ['tokens.json'],
  platforms: {
    css: {
      transformGroup: 'css',
      prefix: '',
      files: [{
        destination: 'src/styles/tokens.generated.css',
        format: 'css/variables',
        options: { selector: ':root' },
      }],
    },
    js: {
      transformGroup: 'js',
      files: [{
        destination: 'src/styles/tokens.generated.js',
        format: 'javascript/es6',
      }],
    },
  },
};
// Run: npx style-dictionary build
// Output: tokens.generated.css with all :root CSS variables
```

### CI/CD Token Sync (GitHub Action)

```yaml
# .github/workflows/tokens.yml
name: Sync Design Tokens
on:
  workflow_dispatch:  # manual trigger from Figma webhook
  push:
    paths: ['tokens.json']
jobs:
  build-tokens:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: npm ci
      - run: npx style-dictionary build
      - name: Commit generated tokens
        run: |
          git config user.email "tokens-bot@yourcompany.com"
          git config user.name  "Tokens Bot"
          git add src/styles/tokens.generated.css
          git diff --staged --quiet || git commit -m "chore: sync design tokens from Figma"
          git push
```

---

## QUICK REFERENCE — AI Tools + Best Use

| Tool | Best for | Prime with |
|------|---------|-----------|
| **Claude** | Architecture, review, complex logic | Full system prompt + token file |
| **Cursor** | In-editor coding with context | .cursorrules file with system prompt |
| **Lovable** | Full-page UI generation | Lovable prompt pattern (Step 3) |
| **v0 (Vercel)** | shadcn/Tailwind components | Token constraints in prompt |
| **Bolt** | Full-stack prototypes | Constraint prompt + token values |
| **GitHub Copilot** | Autocomplete | .github/copilot-instructions.md |

### .github/copilot-instructions.md

```markdown
# Copilot Instructions

This project uses a design token system. Follow these rules:

- All spacing: var(--space-1) through var(--space-40) — never raw px
- All colors: var(--bg), var(--surface), var(--text), var(--accent), var(--border), etc.
- Border radius: var(--radius-sm / md / lg / full)
- No window.alert() — use the swal helper object
- All images: add width, height, loading="lazy", alt attributes
- Dark mode: colors work in both [data-theme="light"] and [data-theme="dark"]
- Mobile-first: min-width media queries only
```
