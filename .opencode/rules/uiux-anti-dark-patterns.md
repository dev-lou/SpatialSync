---
description: Ethical UI rules, no manipulative design, transparent pricing, and respecting user autonomy. Design that doesn't deceive.
---

# Anti-Dark Patterns

> Dark patterns manipulate users into actions they didn't intend.
> This file defines what to avoid and how to design ethically.
> Because trust is the most valuable conversion metric.

---

## CRITICAL RULES

1. **Respect user intent** — Make it easy to do what users came to do.
2. **No deception** — Never mislead about costs, commitments, or consequences.
3. **Equal treatment** — Unsubscribe should be as easy as subscribe.
4. **Clear consequences** — Users should understand what will happen.
5. **No manipulation** — Avoid exploiting cognitive biases.

---

## THE DARK PATTERNS CATALOG

These are **banned** in ethical UI design:

### 1. Confirmshaming

Guilt-tripping users into an action.

```
❌ "No thanks, I don't want to save money"
❌ "I'll stay uninformed"
❌ "No, I hate discounts"

✅ "No thanks"
✅ "Maybe later"
✅ "Skip"
```

```tsx
// Bad
<button>Yes, sign me up!</button>
<button className="text-muted text-sm">
  No thanks, I prefer to miss out on exclusive deals and stay uninformed
</button>

// Good
<button>Subscribe to newsletter</button>
<button>No thanks</button>
```

---

### 2. Trick Questions

Confusing double negatives or misleading wording.

```
❌ "Uncheck this box if you prefer not to not receive emails"
❌ "Check to opt out of promotional communications"

✅ "Send me promotional emails" (unchecked by default)
✅ "I'd like to receive updates" (clear, simple)
```

---

### 3. Hidden Costs

Revealing fees late in checkout.

```
❌ Showing "$29.99" then adding "$4.99 handling" + "$2.99 service fee" at checkout

✅ Show total price upfront
✅ Break down costs clearly from the start
✅ "Price includes all fees"
```

```tsx
// Transparent pricing component
function PriceBreakdown({ subtotal, shipping, tax, total }: PriceBreakdownProps) {
  return (
    <div className="price-breakdown">
      <div className="price-breakdown__row">
        <span>Subtotal</span>
        <span>${subtotal.toFixed(2)}</span>
      </div>
      <div className="price-breakdown__row">
        <span>Shipping</span>
        <span>{shipping === 0 ? 'Free' : `$${shipping.toFixed(2)}`}</span>
      </div>
      <div className="price-breakdown__row">
        <span>Tax</span>
        <span>${tax.toFixed(2)}</span>
      </div>
      <div className="price-breakdown__row price-breakdown__row--total">
        <span>Total</span>
        <span>${total.toFixed(2)}</span>
      </div>
      <p className="price-breakdown__note">
        No hidden fees. This is your final price.
      </p>
    </div>
  );
}
```

---

### 4. Roach Motel

Easy to get in, hard to get out.

```
❌ 1-click subscribe, 47-step unsubscribe
❌ "Call us to cancel" (no online option)
❌ Account deletion hidden in settings → privacy → data → advanced → really advanced

✅ Cancel button in account settings
✅ Same number of steps to join and leave
✅ Clear confirmation, not retention dark patterns
```

```tsx
// Account settings with clear cancel option
function AccountSettings() {
  return (
    <div className="account-settings">
      <section>
        <h2>Subscription</h2>
        <p>You're on the Pro plan ($29/month)</p>
        <div className="account-settings__actions">
          <button className="btn btn--secondary">Change plan</button>
          <button className="btn btn--secondary">Cancel subscription</button>
        </div>
      </section>
      
      <section>
        <h2>Account</h2>
        <button className="btn btn--danger-outline">Delete account</button>
        <p className="text-muted text-sm">
          This will permanently delete your account and all data.
        </p>
      </section>
    </div>
  );
}
```

---

### 5. Privacy Zuckering

Tricking users into sharing more data than intended.

```
❌ Pre-checked "share my data" boxes
❌ "Recommended" sharing options that benefit the company
❌ Making private mode hard to find

✅ All sharing options off by default
✅ Clear explanations of what each setting does
✅ Privacy-first defaults
```

```tsx
// Privacy settings with sensible defaults
const defaultPrivacySettings = {
  profileVisibility: 'private',      // Not "public"
  shareUsageData: false,             // Not true
  personalizedAds: false,            // Not true
  thirdPartySharing: false,          // Not true
  emailNotifications: 'important',   // Not "all"
};

function PrivacySettings() {
  const [settings, setSettings] = useState(defaultPrivacySettings);

  return (
    <div className="privacy-settings">
      <h2>Privacy Settings</h2>
      <p className="privacy-settings__intro">
        Your privacy matters. We've set secure defaults — 
        change only what you're comfortable with.
      </p>
      
      <Toggle
        label="Share anonymous usage data"
        description="Help us improve by sharing anonymous analytics"
        checked={settings.shareUsageData}
        onChange={(v) => setSettings(s => ({ ...s, shareUsageData: v }))}
      />
      
      <Toggle
        label="Personalized ads"
        description="Show ads based on your activity"
        checked={settings.personalizedAds}
        onChange={(v) => setSettings(s => ({ ...s, personalizedAds: v }))}
      />
      
      {/* ... */}
    </div>
  );
}
```

---

### 6. Forced Continuity

Auto-renewing subscriptions without clear notice.

```
❌ Free trial → auto-charge with no warning
❌ Renewal email buried in spam-like formatting
❌ "Renews automatically" in 6pt font

✅ Email reminder 7 days before renewal
✅ Clear renewal date in account dashboard
✅ Easy one-click cancel
✅ Explicit opt-in for auto-renewal
```

```tsx
// Subscription status with clear renewal info
function SubscriptionStatus({ subscription }: SubscriptionProps) {
  const renewalDate = new Date(subscription.renewsAt);
  const daysUntilRenewal = Math.ceil(
    (renewalDate.getTime() - Date.now()) / (1000 * 60 * 60 * 24)
  );

  return (
    <div className="subscription-status">
      <h3>Your Subscription</h3>
      
      <div className="subscription-status__plan">
        <span>{subscription.planName}</span>
        <span>${subscription.price}/month</span>
      </div>
      
      <div className="subscription-status__renewal">
        <span>
          {subscription.autoRenew ? '🔄 Renews' : '⏹️ Ends'} on{' '}
          {renewalDate.toLocaleDateString()}
        </span>
        {daysUntilRenewal <= 7 && (
          <span className="subscription-status__warning">
            ({daysUntilRenewal} days)
          </span>
        )}
      </div>
      
      <div className="subscription-status__actions">
        <Toggle
          label="Auto-renew"
          checked={subscription.autoRenew}
          onChange={handleAutoRenewChange}
        />
        <button className="btn btn--secondary">Cancel subscription</button>
      </div>
    </div>
  );
}
```

---

### 7. Misdirection

Drawing attention away from important information.

```
❌ Giant "Accept All" button, tiny "Manage Preferences" link
❌ Highlighting the expensive plan, graying out the free option
❌ "Recommended for you" on the most profitable option

✅ Equal visual weight for all options
✅ Highlight based on user's actual needs
✅ Clear comparison without bias
```

```tsx
// Fair cookie consent
function CookieConsent() {
  return (
    <div className="cookie-consent">
      <p>
        We use cookies to improve your experience. 
        You can customize your preferences below.
      </p>
      
      <div className="cookie-consent__actions">
        {/* Equal visual weight */}
        <button className="btn btn--secondary">Customize</button>
        <button className="btn btn--secondary">Reject all</button>
        <button className="btn btn--primary">Accept all</button>
      </div>
    </div>
  );
}
```

---

### 8. Bait and Switch

Advertising one thing, delivering another.

```
❌ "Free forever" (with features locked behind paywall)
❌ "No credit card required" (then asking for it to access basic features)
❌ Changing terms after user commits

✅ Clear feature comparison between free/paid
✅ No surprises after signup
✅ Notify users before any changes
```

---

### 9. Disguised Ads

Ads that look like content or UI elements.

```
❌ "Download" buttons that are ads
❌ Fake chat widgets that lead to sales
❌ Sponsored content without clear labels

✅ Clear "Ad" or "Sponsored" labels
✅ Distinct visual styling for ads
✅ Separate ads from core UI
```

---

### 10. Urgency & Scarcity Lies

Fake countdown timers and "only X left" claims.

```
❌ Countdown that resets when page refreshes
❌ "Only 2 left!" (that never changes)
❌ "Sale ends today!" (every day)

✅ Real deadlines only
✅ Honest inventory counts
✅ No false urgency
```

```tsx
// Only show urgency if it's real
function ProductAvailability({ stock, isRealTimeInventory }: Props) {
  // Don't show "low stock" unless it's real data
  if (!isRealTimeInventory) {
    return <span className="availability">In stock</span>;
  }

  if (stock === 0) {
    return <span className="availability availability--out">Out of stock</span>;
  }

  if (stock <= 5) {
    return (
      <span className="availability availability--low">
        Only {stock} left in stock
      </span>
    );
  }

  return <span className="availability">In stock</span>;
}
```

---

## ETHICAL ALTERNATIVES

### Instead of Confirmshaming

```tsx
// Ethical newsletter signup
function NewsletterSignup() {
  return (
    <div className="newsletter-signup">
      <h3>Stay updated</h3>
      <p>Get weekly tips on productivity and design.</p>
      
      <form>
        <input type="email" placeholder="your@email.com" />
        <button type="submit">Subscribe</button>
      </form>
      
      {/* Respectful decline */}
      <button className="newsletter-signup__skip">
        Maybe later
      </button>
    </div>
  );
}
```

### Instead of Forced Continuity

```tsx
// Clear trial flow
function TrialSignup() {
  return (
    <div className="trial-signup">
      <h2>Start your 14-day free trial</h2>
      
      <ul className="trial-signup__benefits">
        <li>✓ Full access to all features</li>
        <li>✓ No credit card required</li>
        <li>✓ Cancel anytime</li>
      </ul>
      
      <div className="trial-signup__reminder">
        <InfoIcon />
        <p>
          We'll email you 3 days before your trial ends. 
          No automatic charges — you decide if you want to continue.
        </p>
      </div>
      
      <button className="btn btn--primary">Start free trial</button>
    </div>
  );
}
```

### Instead of Misdirection

```tsx
// Fair pricing comparison
function PricingTable({ plans }: { plans: Plan[] }) {
  return (
    <div className="pricing-table">
      {plans.map(plan => (
        <div 
          key={plan.id}
          className="pricing-card"
          // No "popular" or "recommended" unless based on user's actual usage
        >
          <h3>{plan.name}</h3>
          <p className="pricing-card__price">
            ${plan.price}<span>/month</span>
          </p>
          
          <ul className="pricing-card__features">
            {plan.features.map(f => (
              <li key={f}>{f}</li>
            ))}
          </ul>
          
          {/* Equal button styling for all plans */}
          <button className="btn btn--primary">
            Choose {plan.name}
          </button>
        </div>
      ))}
    </div>
  );
}
```

---

## ETHICAL DESIGN CHECKLIST

Before shipping, verify:

```
□ Can users easily cancel/unsubscribe?
□ Are all prices shown upfront (no hidden fees)?
□ Are sharing/data options off by default?
□ Is the "no" option judgment-free?
□ Are countdown timers based on real deadlines?
□ Is "low stock" based on real inventory?
□ Are ads clearly labeled?
□ Is the trial terms clearly explained?
□ Do users get renewal reminders?
□ Are all options given equal visual weight?
□ Can users delete their account easily?
□ Are error messages helpful, not punishing?
```

---

## REGULATORY COMPLIANCE

These dark patterns may violate laws:

| Pattern | Regulation |
|---------|------------|
| Hidden costs | FTC Act, Consumer Rights Directive |
| Forced continuity | ROSCA (US), Consumer Contracts Regulations (UK) |
| Privacy Zuckering | GDPR, CCPA |
| Misdirection in consent | GDPR consent requirements |
| Bait and switch | FTC Act, Advertising Standards |
| Disguised ads | FTC Endorsement Guidelines |

---

## RESOURCES

- [Dark Patterns Tip Line](https://darkpatternstipline.org) — Report dark patterns
- [Deceptive Design](https://deceptive.design) — Hall of shame
- [GDPR consent requirements](https://gdpr.eu/consent/) — Legal requirements
- [FTC guidelines](https://www.ftc.gov/business-guidance) — US regulations

---

## REMEMBER

> "The user's goal is to complete a task, not to interact with your business model."

Design for trust. Trust converts better than tricks.
