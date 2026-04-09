---
description: Generate placeholder pages for Privacy Policy, Terms of Service, Cookie Policy, and Accessibility Statement. Uses app config for names. Checks existence before creating.
---

# UI/UX Legal Placeholder Pages

> Generate professional legal page templates.
> All content uses config variables. No duplicates.

---

## OVERVIEW

This skill generates placeholder legal pages:
1. Privacy Policy
2. Terms of Service
3. Cookie Policy
4. Accessibility Statement

All pages:
- Use `{{ config('app.name') }}` and other config values
- Include standard sections with placeholder text
- Share consistent styling with design tokens
- Are clearly marked as templates needing legal review

---

## BEFORE CREATING

**Check if page already exists:**

```bash
# Laravel
php artisan tinker
>>> File::exists(resource_path('views/legal/privacy.blade.php'))

# Or check routes
php artisan route:list --name=privacy
```

**Never duplicate pages** - update existing ones instead.

---

## PRIVACY POLICY

```blade
{{-- resources/views/legal/privacy.blade.php --}}
@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<article class="legal-page">
    <header class="legal-page__header">
        <h1>Privacy Policy</h1>
        <p class="legal-page__effective">
            Effective Date: {{ now()->format('F j, Y') }}
        </p>
    </header>

    <div class="legal-page__notice">
        <strong>⚠️ Template Notice:</strong> This is a placeholder privacy policy. 
        Please have this document reviewed by a legal professional before publishing.
    </div>

    <section class="legal-page__section">
        <h2>1. Introduction</h2>
        <p>
            Welcome to {{ config('app.name') }} ("we," "our," or "us"). We respect your 
            privacy and are committed to protecting your personal data. This privacy policy 
            explains how we collect, use, disclose, and safeguard your information when you 
            visit our website or use our services.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>2. Information We Collect</h2>
        
        <h3>2.1 Information You Provide</h3>
        <ul>
            <li><strong>Account Information:</strong> Name, email address, password</li>
            <li><strong>Profile Information:</strong> Avatar, bio, preferences</li>
            <li><strong>Payment Information:</strong> Billing address, payment method details (processed by our payment provider)</li>
            <li><strong>Communications:</strong> Messages you send us, support requests</li>
        </ul>

        <h3>2.2 Information Collected Automatically</h3>
        <ul>
            <li><strong>Usage Data:</strong> Pages visited, features used, actions taken</li>
            <li><strong>Device Information:</strong> Browser type, operating system, device identifiers</li>
            <li><strong>Location Data:</strong> IP address, general geographic location</li>
            <li><strong>Cookies:</strong> See our <a href="{{ route('cookies') }}">Cookie Policy</a></li>
        </ul>
    </section>

    <section class="legal-page__section">
        <h2>3. How We Use Your Information</h2>
        <p>We use your information to:</p>
        <ul>
            <li>Provide and maintain our services</li>
            <li>Process transactions and send related information</li>
            <li>Send administrative notifications and updates</li>
            <li>Respond to your comments, questions, and support requests</li>
            <li>Monitor and analyze usage and trends to improve our services</li>
            <li>Detect, investigate, and prevent fraudulent or unauthorized activities</li>
            <li>Personalize your experience</li>
        </ul>
    </section>

    <section class="legal-page__section">
        <h2>4. Sharing Your Information</h2>
        <p>We may share your information with:</p>
        <ul>
            <li><strong>Service Providers:</strong> Third parties that help us operate our business (hosting, analytics, payment processing)</li>
            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
            <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
        </ul>
        <p>We do not sell your personal information to third parties.</p>
    </section>

    <section class="legal-page__section">
        <h2>5. Data Security</h2>
        <p>
            We implement appropriate technical and organizational measures to protect your 
            personal data against unauthorized access, alteration, disclosure, or destruction. 
            However, no method of transmission over the Internet is 100% secure.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>6. Your Rights</h2>
        <p>Depending on your location, you may have the right to:</p>
        <ul>
            <li>Access the personal data we hold about you</li>
            <li>Correct inaccurate personal data</li>
            <li>Request deletion of your personal data</li>
            <li>Object to processing of your personal data</li>
            <li>Request portability of your personal data</li>
            <li>Withdraw consent at any time</li>
        </ul>
        <p>
            To exercise these rights, please contact us at 
            <a href="mailto:{{ config('app.support_email', 'privacy@example.com') }}">
                {{ config('app.support_email', 'privacy@example.com') }}
            </a>.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>7. Data Retention</h2>
        <p>
            We retain your personal data only for as long as necessary to fulfill the 
            purposes for which it was collected, including to satisfy any legal, accounting, 
            or reporting requirements.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>8. Children's Privacy</h2>
        <p>
            Our services are not intended for individuals under the age of 16. We do not 
            knowingly collect personal data from children. If you believe we have collected 
            data from a child, please contact us immediately.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>9. Changes to This Policy</h2>
        <p>
            We may update this privacy policy from time to time. We will notify you of any 
            changes by posting the new policy on this page and updating the "Effective Date" 
            at the top.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>10. Contact Us</h2>
        <p>
            If you have questions about this privacy policy, please contact us:
        </p>
        <ul>
            <li>Email: <a href="mailto:{{ config('app.support_email', 'privacy@example.com') }}">{{ config('app.support_email', 'privacy@example.com') }}</a></li>
            @if(config('app.legal.address'))
            <li>Address: {{ config('app.legal.address') }}</li>
            @endif
        </ul>
    </section>
</article>
@endsection
```

---

## TERMS OF SERVICE

```blade
{{-- resources/views/legal/terms.blade.php --}}
@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<article class="legal-page">
    <header class="legal-page__header">
        <h1>Terms of Service</h1>
        <p class="legal-page__effective">
            Effective Date: {{ now()->format('F j, Y') }}
        </p>
    </header>

    <div class="legal-page__notice">
        <strong>⚠️ Template Notice:</strong> This is a placeholder terms of service. 
        Please have this document reviewed by a legal professional before publishing.
    </div>

    <section class="legal-page__section">
        <h2>1. Agreement to Terms</h2>
        <p>
            By accessing or using {{ config('app.name') }} ("Service"), you agree to be 
            bound by these Terms of Service ("Terms"). If you disagree with any part of 
            the terms, you may not access the Service.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>2. Description of Service</h2>
        <p>
            {{ config('app.name') }} provides [DESCRIBE YOUR SERVICE HERE]. We reserve 
            the right to modify, suspend, or discontinue the Service at any time without 
            prior notice.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>3. User Accounts</h2>
        <ul>
            <li>You must be at least 16 years old to use this Service</li>
            <li>You are responsible for maintaining the security of your account</li>
            <li>You must provide accurate and complete information</li>
            <li>You are responsible for all activities under your account</li>
            <li>You must notify us immediately of any unauthorized use</li>
        </ul>
    </section>

    <section class="legal-page__section">
        <h2>4. Acceptable Use</h2>
        <p>You agree not to:</p>
        <ul>
            <li>Use the Service for any illegal purpose</li>
            <li>Violate any laws or regulations</li>
            <li>Infringe on intellectual property rights</li>
            <li>Transmit harmful code or interfere with the Service</li>
            <li>Attempt to gain unauthorized access</li>
            <li>Harass, abuse, or harm others</li>
            <li>Use the Service to send spam or unsolicited messages</li>
        </ul>
    </section>

    <section class="legal-page__section">
        <h2>5. Intellectual Property</h2>
        <p>
            The Service and its original content, features, and functionality are owned 
            by {{ config('app.legal.entity', config('app.name')) }} and are protected by 
            international copyright, trademark, and other intellectual property laws.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>6. User Content</h2>
        <p>
            You retain ownership of any content you submit to the Service. By submitting 
            content, you grant us a non-exclusive, worldwide, royalty-free license to use, 
            modify, and display such content in connection with the Service.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>7. Payment Terms</h2>
        <p>
            [IF APPLICABLE] Paid features are billed in advance. All fees are 
            non-refundable except as required by law. We may change pricing with 30 days' 
            notice.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>8. Termination</h2>
        <p>
            We may terminate or suspend your account immediately, without prior notice, 
            for conduct that we believe violates these Terms or is harmful to other users, 
            us, or third parties, or for any other reason.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>9. Limitation of Liability</h2>
        <p>
            To the maximum extent permitted by law, {{ config('app.legal.entity', config('app.name')) }} 
            shall not be liable for any indirect, incidental, special, consequential, or 
            punitive damages resulting from your use of the Service.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>10. Disclaimer</h2>
        <p>
            The Service is provided "as is" without warranties of any kind, whether express 
            or implied. We do not warrant that the Service will be uninterrupted, secure, 
            or error-free.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>11. Governing Law</h2>
        <p>
            These Terms shall be governed by the laws of [YOUR JURISDICTION], without 
            regard to conflict of law provisions.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>12. Changes to Terms</h2>
        <p>
            We reserve the right to modify these Terms at any time. We will provide notice 
            of significant changes. Your continued use of the Service constitutes acceptance 
            of the modified Terms.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>13. Contact</h2>
        <p>
            Questions about these Terms should be sent to 
            <a href="mailto:{{ config('app.support_email', 'legal@example.com') }}">
                {{ config('app.support_email', 'legal@example.com') }}
            </a>.
        </p>
    </section>
</article>
@endsection
```

---

## COOKIE POLICY

```blade
{{-- resources/views/legal/cookies.blade.php --}}
@extends('layouts.app')

@section('title', 'Cookie Policy')

@section('content')
<article class="legal-page">
    <header class="legal-page__header">
        <h1>Cookie Policy</h1>
        <p class="legal-page__effective">
            Effective Date: {{ now()->format('F j, Y') }}
        </p>
    </header>

    <div class="legal-page__notice">
        <strong>⚠️ Template Notice:</strong> This is a placeholder cookie policy. 
        Please have this document reviewed by a legal professional before publishing.
    </div>

    <section class="legal-page__section">
        <h2>1. What Are Cookies</h2>
        <p>
            Cookies are small text files stored on your device when you visit a website. 
            They help websites remember your preferences and improve your experience.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>2. How We Use Cookies</h2>
        <p>{{ config('app.name') }} uses cookies for:</p>
        
        <h3>2.1 Essential Cookies</h3>
        <p>
            Required for the website to function. They enable core features like security, 
            account access, and form submissions. Cannot be disabled.
        </p>
        <table class="legal-page__table">
            <thead>
                <tr>
                    <th>Cookie</th>
                    <th>Purpose</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>session</code></td>
                    <td>User authentication</td>
                    <td>Session</td>
                </tr>
                <tr>
                    <td><code>XSRF-TOKEN</code></td>
                    <td>Security (CSRF protection)</td>
                    <td>Session</td>
                </tr>
            </tbody>
        </table>

        <h3>2.2 Functional Cookies</h3>
        <p>
            Enable enhanced functionality and personalization, such as remembering your 
            preferences.
        </p>
        <table class="legal-page__table">
            <thead>
                <tr>
                    <th>Cookie</th>
                    <th>Purpose</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>theme</code></td>
                    <td>Remember dark/light mode preference</td>
                    <td>1 year</td>
                </tr>
                <tr>
                    <td><code>locale</code></td>
                    <td>Remember language preference</td>
                    <td>1 year</td>
                </tr>
            </tbody>
        </table>

        <h3>2.3 Analytics Cookies</h3>
        <p>
            Help us understand how visitors interact with our website by collecting 
            anonymous information.
        </p>
        <table class="legal-page__table">
            <thead>
                <tr>
                    <th>Cookie</th>
                    <th>Purpose</th>
                    <th>Duration</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>_ga</code></td>
                    <td>Google Analytics - distinguish users</td>
                    <td>2 years</td>
                </tr>
                <tr>
                    <td><code>_gid</code></td>
                    <td>Google Analytics - distinguish users</td>
                    <td>24 hours</td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="legal-page__section">
        <h2>3. Managing Cookies</h2>
        <p>You can control cookies through:</p>
        <ul>
            <li><strong>Cookie Banner:</strong> Use our cookie consent banner to manage preferences</li>
            <li><strong>Browser Settings:</strong> Most browsers allow you to block or delete cookies</li>
        </ul>
        <p>
            Note: Disabling certain cookies may affect website functionality.
        </p>
        
        <h3>Browser-Specific Instructions</h3>
        <ul>
            <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Chrome</a></li>
            <li><a href="https://support.mozilla.org/en-US/kb/cookies-information-websites-store-on-your-computer" target="_blank" rel="noopener">Firefox</a></li>
            <li><a href="https://support.apple.com/guide/safari/manage-cookies-sfri11471/mac" target="_blank" rel="noopener">Safari</a></li>
            <li><a href="https://support.microsoft.com/en-us/microsoft-edge/delete-cookies-in-microsoft-edge" target="_blank" rel="noopener">Edge</a></li>
        </ul>
    </section>

    <section class="legal-page__section">
        <h2>4. Third-Party Cookies</h2>
        <p>
            Some cookies are placed by third-party services that appear on our pages. 
            We do not control these cookies. Please refer to the respective privacy 
            policies of these third parties.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>5. Updates to This Policy</h2>
        <p>
            We may update this Cookie Policy from time to time. The updated version will 
            be indicated by an updated "Effective Date" at the top of this page.
        </p>
    </section>

    <section class="legal-page__section">
        <h2>6. Contact Us</h2>
        <p>
            If you have questions about our use of cookies, please contact us at 
            <a href="mailto:{{ config('app.support_email', 'privacy@example.com') }}">
                {{ config('app.support_email', 'privacy@example.com') }}
            </a>.
        </p>
    </section>
</article>
@endsection
```

---

## SHARED STYLES

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
  font-weight: 700;
  margin-bottom: var(--space-3);
}

.legal-page__effective {
  color: var(--muted);
  font-size: var(--text-sm);
}

.legal-page__notice {
  padding: var(--space-4);
  background: var(--color-warning-soft);
  border: 1px solid var(--color-warning);
  border-radius: var(--radius-md);
  margin-bottom: var(--space-8);
  font-size: var(--text-sm);
}

.legal-page__section {
  margin-bottom: var(--space-8);
}

.legal-page__section h2 {
  font-size: var(--text-2xl);
  font-weight: 600;
  margin-bottom: var(--space-4);
  color: var(--text);
}

.legal-page__section h3 {
  font-size: var(--text-lg);
  font-weight: 600;
  margin-top: var(--space-6);
  margin-bottom: var(--space-3);
}

.legal-page__section p {
  color: var(--text-secondary);
  line-height: 1.7;
  margin-bottom: var(--space-4);
}

.legal-page__section ul,
.legal-page__section ol {
  color: var(--text-secondary);
  padding-left: var(--space-6);
  margin-bottom: var(--space-4);
}

.legal-page__section li {
  margin-bottom: var(--space-2);
  line-height: 1.6;
}

.legal-page__section a {
  color: var(--accent);
  text-decoration: underline;
}

.legal-page__section a:hover {
  text-decoration: none;
}

.legal-page__table {
  width: 100%;
  border-collapse: collapse;
  margin: var(--space-4) 0;
  font-size: var(--text-sm);
}

.legal-page__table th,
.legal-page__table td {
  padding: var(--space-3);
  border: 1px solid var(--border);
  text-align: left;
}

.legal-page__table th {
  background: var(--surface);
  font-weight: 600;
}

.legal-page__table code {
  background: var(--surface);
  padding: var(--space-1) var(--space-2);
  border-radius: var(--radius-sm);
  font-size: var(--text-xs);
}
```

---

## ROUTES

```php
// routes/web.php
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/cookies', [PageController::class, 'cookies'])->name('cookies');
Route::get('/accessibility', [PageController::class, 'accessibility'])->name('accessibility');
```

---

## CHECKLIST

- [ ] Check if page exists before creating
- [ ] Uses `config('app.name')` throughout
- [ ] Uses `config('app.support_email')` for contact
- [ ] Uses `config('app.legal.entity')` for company name
- [ ] Template notice visible at top
- [ ] Effective date auto-generated
- [ ] All sections have proper headings
- [ ] Links to related policies (e.g., privacy → cookies)
- [ ] Accessible heading structure
- [ ] Shared styling from design system
