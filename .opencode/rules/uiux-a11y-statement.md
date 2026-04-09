---
description: Generate WCAG compliance statement template. Includes date, scope, exceptions, testing methods, and contact information.
---

# UI/UX Accessibility Statement Generator

> Generate professional WCAG compliance statements.
> Includes all required sections per W3C template.

---

## OVERVIEW

This skill generates accessibility statements that include:
1. Conformance status
2. Scope and limitations
3. Testing methodology
4. Known issues and remediation
5. Contact information
6. Formal compliance language

---

## TEMPLATE

```markdown
# Accessibility Statement for {{ config('app.name') }}

**Last Updated**: {{ date('F j, Y') }}

---

## Our Commitment

{{ config('app.name') }} is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.

---

## Conformance Status

The Web Content Accessibility Guidelines (WCAG) defines requirements for designers and developers to improve accessibility for people with disabilities.

**Current Status**: {{ CONFORMANCE_LEVEL }}

- **WCAG 2.1 Level AA**: {{ STATUS }}
- **Target Date**: {{ TARGET_DATE }}
- **Last Audit**: {{ AUDIT_DATE }}

### Conformance Levels Explained

| Level | Description |
|-------|-------------|
| **Level A** | Essential accessibility features |
| **Level AA** | Addresses major barriers (our target) |
| **Level AAA** | Highest level of accessibility |

---

## Scope

This accessibility statement applies to the following:

### Included
{{ SCOPE_INCLUDED }}

### Not Included
{{ SCOPE_EXCLUDED }}

---

## Technical Specifications

This website relies on the following technologies for conformance:

- HTML5
- CSS3
- JavaScript
- WAI-ARIA 1.2
- SVG

The website is designed to be compatible with the following assistive technologies:

- Screen readers (NVDA, JAWS, VoiceOver)
- Screen magnification software
- Speech recognition software
- Keyboard-only navigation

---

## Known Limitations

While we strive for WCAG 2.1 Level AA conformance, some content may have accessibility limitations:

{{ KNOWN_LIMITATIONS_TABLE }}

We are actively working to address these issues.

---

## Testing Methodology

Our accessibility testing includes:

### Automated Testing
- **axe-core** - Automated accessibility scanner
- **WAVE** - Web accessibility evaluation tool
- **Lighthouse** - Performance and accessibility audits
- **Pa11y** - Automated accessibility testing

### Manual Testing
- Keyboard navigation testing
- Screen reader testing (NVDA, VoiceOver)
- Color contrast verification
- Focus management review
- Form validation accessibility

### User Testing
- Usability testing with people with disabilities
- Feedback collection and analysis

---

## Feedback and Contact

We welcome your feedback on the accessibility of {{ config('app.name') }}. Please let us know if you encounter accessibility barriers:

**Accessibility Contact**:
- Email: {{ ACCESSIBILITY_EMAIL }}
- Phone: {{ ACCESSIBILITY_PHONE }}
- Address: {{ ACCESSIBILITY_ADDRESS }}

**Response Time**: We aim to respond within {{ RESPONSE_TIME }} business days.

---

## Formal Complaints

If you are not satisfied with our response, you may escalate your complaint to:

{{ COMPLAINT_AUTHORITY }}

---

## Assessment and Audit History

| Date | Auditor | Scope | Result |
|------|---------|-------|--------|
{{ AUDIT_HISTORY }}

---

## Remediation Plan

We have an ongoing accessibility improvement plan:

{{ REMEDIATION_PLAN }}

---

## Additional Resources

- [Web Content Accessibility Guidelines (WCAG) 2.1](https://www.w3.org/TR/WCAG21/)
- [How to Meet WCAG (Quick Reference)](https://www.w3.org/WAI/WCAG21/quickref/)
- [Understanding WCAG 2.1](https://www.w3.org/WAI/WCAG21/Understanding/)

---

*This statement was created on {{ CREATION_DATE }} and was last reviewed on {{ LAST_REVIEW_DATE }}.*
```

---

## FIELD DEFINITIONS

### Conformance Status Options

```javascript
const CONFORMANCE_OPTIONS = {
  'fully': 'Fully conforms - all requirements met without exceptions',
  'partially': 'Partially conforms - most requirements met with known exceptions',
  'non-conformant': 'Non-conformant - significant requirements not met',
  'not-assessed': 'Not assessed - formal evaluation pending'
};
```

### Scope Templates

```javascript
// E-commerce
const ECOMMERCE_SCOPE = {
  included: [
    'Main website (www.example.com)',
    'Product catalog and search',
    'Shopping cart and checkout',
    'Account management',
    'Customer support pages'
  ],
  excluded: [
    'Third-party payment processors',
    'PDF product documentation (remediation in progress)',
    'Legacy archive content pre-2020'
  ]
};

// SaaS
const SAAS_SCOPE = {
  included: [
    'Application dashboard',
    'All user-facing features',
    'Documentation site',
    'Help center',
    'Account settings'
  ],
  excluded: [
    'API documentation (developer-focused)',
    'Admin-only internal tools',
    'Third-party integrations'
  ]
};
```

### Known Limitations Table Template

```markdown
| Content | Issue | Alternative | Timeline |
|---------|-------|-------------|----------|
| Video content | Some videos lack captions | Transcripts provided | Q2 2026 |
| PDF documents | Some not tagged | HTML versions available | Q3 2026 |
| Charts | Complex data visualizations | Data tables provided | Q2 2026 |
| Legacy forms | Some not keyboard accessible | New forms in progress | Q4 2026 |
```

---

## GENERATION PROMPTS

### AI Generation Command

When the user asks to generate an accessibility statement:

```
I'll generate a WCAG 2.1 Level AA accessibility statement for {{ app.name }}.

Please provide the following information:
1. Current conformance status (fully/partially conforming)
2. Last accessibility audit date
3. Known accessibility issues
4. Accessibility contact email
5. Any specific exclusions from scope

I'll create a complete statement following the W3C template format.
```

### Quick Generation (Defaults)

```javascript
function generateQuickStatement(appName) {
  return `
# Accessibility Statement for ${appName}

**Last Updated**: ${new Date().toLocaleDateString('en-US', { 
  year: 'numeric', 
  month: 'long', 
  day: 'numeric' 
})}

## Our Commitment
${appName} is committed to ensuring digital accessibility for people with disabilities. We follow WCAG 2.1 Level AA guidelines.

## Conformance Status
**Current Status**: Partially Conforms
We are actively working to achieve full conformance.

## Testing
We test using:
- Automated: axe-core, Lighthouse
- Manual: Keyboard navigation, screen reader testing

## Feedback
Please report accessibility issues to: accessibility@example.com

## Known Issues
We are addressing the following:
- [List any known issues]

*This statement was last reviewed on ${new Date().toLocaleDateString()}.*
  `;
}
```

---

## BLADE TEMPLATE (Laravel)

Create at `resources/views/legal/accessibility.blade.php`:

```blade
@extends('layouts.app')

@section('title', 'Accessibility Statement')

@section('content')
<article class="legal-page">
  <header class="legal-page__header">
    <h1>Accessibility Statement</h1>
    <p class="legal-page__updated">
      Last Updated: {{ now()->format('F j, Y') }}
    </p>
  </header>

  <section class="legal-page__section">
    <h2>Our Commitment</h2>
    <p>
      {{ config('app.name') }} is committed to ensuring digital accessibility 
      for people with disabilities. We are continually improving the user 
      experience for everyone and applying the relevant accessibility standards.
    </p>
  </section>

  <section class="legal-page__section">
    <h2>Conformance Status</h2>
    <p>
      <strong>Current Status</strong>: Partially Conforms to WCAG 2.1 Level AA
    </p>
    <p>
      We are actively working to achieve and maintain full conformance with 
      the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA.
    </p>
  </section>

  <section class="legal-page__section">
    <h2>Feedback</h2>
    <p>
      We welcome your feedback on the accessibility of {{ config('app.name') }}. 
      Please let us know if you encounter accessibility barriers:
    </p>
    <ul>
      <li>Email: <a href="mailto:{{ config('mail.accessibility') }}">{{ config('mail.accessibility') }}</a></li>
      <li>Phone: {{ config('app.phone') }}</li>
    </ul>
  </section>

  <section class="legal-page__section">
    <h2>Testing Methodology</h2>
    <p>Our accessibility testing includes:</p>
    <ul>
      <li>Automated testing with axe-core and Lighthouse</li>
      <li>Manual testing with keyboard navigation</li>
      <li>Screen reader testing with NVDA and VoiceOver</li>
      <li>Regular audits by accessibility specialists</li>
    </ul>
  </section>
</article>
@endsection
```

---

## REACT COMPONENT

```tsx
// pages/Accessibility.tsx
import { useConfig } from '@/hooks/useConfig';

export default function AccessibilityStatement() {
  const { appName, accessibilityEmail } = useConfig();
  const lastUpdated = new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });

  return (
    <article className="legal-page">
      <header className="legal-page__header">
        <h1>Accessibility Statement</h1>
        <p className="legal-page__updated">Last Updated: {lastUpdated}</p>
      </header>

      <section className="legal-page__section">
        <h2>Our Commitment</h2>
        <p>
          {appName} is committed to ensuring digital accessibility for people 
          with disabilities. We follow WCAG 2.1 Level AA guidelines.
        </p>
      </section>

      <section className="legal-page__section">
        <h2>Conformance Status</h2>
        <dl className="status-list">
          <dt>Current Status</dt>
          <dd>Partially Conforms to WCAG 2.1 Level AA</dd>
          <dt>Target</dt>
          <dd>Full Conformance by Q4 2026</dd>
        </dl>
      </section>

      <section className="legal-page__section">
        <h2>Feedback</h2>
        <p>
          Report accessibility issues to:{' '}
          <a href={`mailto:${accessibilityEmail}`}>{accessibilityEmail}</a>
        </p>
      </section>

      <section className="legal-page__section">
        <h2>Testing</h2>
        <ul>
          <li>Automated: axe-core, Lighthouse, Pa11y</li>
          <li>Manual: Keyboard, screen readers</li>
          <li>User: Testing with assistive technology users</li>
        </ul>
      </section>
    </article>
  );
}
```

---

## STYLES (Token-Aware)

```css
.legal-page {
  max-width: 800px;
  margin: 0 auto;
  padding: var(--space-8) var(--space-4);
}

.legal-page__header {
  margin-bottom: var(--space-8);
  padding-bottom: var(--space-6);
  border-bottom: 1px solid var(--border);
}

.legal-page__header h1 {
  font-size: var(--text-4xl);
  margin-bottom: var(--space-2);
}

.legal-page__updated {
  color: var(--muted);
  font-size: var(--text-sm);
}

.legal-page__section {
  margin-bottom: var(--space-8);
}

.legal-page__section h2 {
  font-size: var(--text-2xl);
  margin-bottom: var(--space-4);
  color: var(--text);
}

.legal-page__section p,
.legal-page__section ul,
.legal-page__section ol {
  color: var(--text-secondary);
  line-height: 1.7;
  margin-bottom: var(--space-4);
}

.legal-page__section ul,
.legal-page__section ol {
  padding-left: var(--space-6);
}

.legal-page__section li {
  margin-bottom: var(--space-2);
}

.status-list {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: var(--space-2) var(--space-4);
}

.status-list dt {
  font-weight: 600;
}

.status-list dd {
  color: var(--text-secondary);
}
```

---

## CHECKLIST

When generating an accessibility statement, ensure:

- [ ] App name from config (not hardcoded)
- [ ] Current date auto-generated
- [ ] Conformance level clearly stated
- [ ] Scope defined (included/excluded)
- [ ] Testing methodology listed
- [ ] Known limitations documented
- [ ] Contact information provided
- [ ] Feedback mechanism clear
- [ ] Remediation timeline included
- [ ] Statement is itself accessible
