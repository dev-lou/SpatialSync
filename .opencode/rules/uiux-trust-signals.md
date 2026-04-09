---
description: Security badges, verification UI, privacy indicators, and trust signals. Building confidence through transparent design.
---

# Trust Signals

> Users don't trust by default — they verify. This file covers security badges,
> verification states, privacy indicators, and visual cues that build confidence.
> Trust is earned through transparency.

---

## CRITICAL RULES

1. **Never fake trust** — Only display real certifications and badges.
2. **Explain, don't just display** — Users should understand what badges mean.
3. **Consistent placement** — Trust signals belong in predictable locations.
4. **Contextual relevance** — Show payment security at checkout, not homepage.
5. **Accessible** — Trust signals need alt text and semantic markup.

---

## TRUST SIGNAL CATEGORIES

| Category | Purpose | When to Show |
|----------|---------|--------------|
| Security | "Your data is safe" | Login, checkout, forms |
| Verification | "This is legitimate" | User profiles, businesses |
| Privacy | "We respect your data" | Data collection points |
| Social proof | "Others trust us" | Landing pages, products |
| Support | "We're here to help" | Throughout, especially checkout |

---

## SECURITY BADGES

### Payment Security

```tsx
// PaymentSecurity.tsx
function PaymentSecurityBadges() {
  return (
    <div className="payment-security" aria-label="Payment security information">
      <div className="payment-security__lock">
        <LockIcon aria-hidden="true" />
        <span>Secure checkout</span>
      </div>
      
      <div className="payment-security__badges">
        {/* Only show badges you actually have */}
        <img 
          src="/badges/pci-dss.svg" 
          alt="PCI DSS Compliant" 
          title="Payment Card Industry Data Security Standard certified"
          width={60}
          height={30}
        />
        <img 
          src="/badges/ssl-secure.svg" 
          alt="SSL Encrypted" 
          title="256-bit SSL encryption protects your data"
          width={60}
          height={30}
        />
      </div>
      
      <p className="payment-security__text">
        Your payment information is encrypted and never stored on our servers.
      </p>
    </div>
  );
}
```

### SSL Indicator

```tsx
// SSLIndicator.tsx
function SSLIndicator() {
  const isSecure = window.location.protocol === 'https:';
  
  if (!isSecure) return null; // Don't show false indicator
  
  return (
    <div className="ssl-indicator" title="This connection is secure">
      <LockIcon className="ssl-indicator__icon" aria-hidden="true" />
      <span className="ssl-indicator__text">Secure</span>
    </div>
  );
}
```

### Data Encryption Notice

```tsx
// EncryptionNotice.tsx
function EncryptionNotice({ type }: { type: 'form' | 'upload' | 'message' }) {
  const messages = {
    form: 'Your information is encrypted and transmitted securely.',
    upload: 'Files are encrypted during upload and storage.',
    message: 'Messages are end-to-end encrypted.',
  };

  return (
    <div className="encryption-notice">
      <ShieldIcon aria-hidden="true" />
      <span>{messages[type]}</span>
    </div>
  );
}
```

---

## VERIFICATION BADGES

### Verified User/Business

```tsx
// VerifiedBadge.tsx
interface VerifiedBadgeProps {
  type: 'identity' | 'business' | 'creator' | 'official';
  showTooltip?: boolean;
}

const verificationInfo = {
  identity: {
    icon: '✓',
    label: 'Verified identity',
    description: 'This user has verified their identity with a government ID.',
  },
  business: {
    icon: '✓',
    label: 'Verified business',
    description: 'This business has been verified through official documentation.',
  },
  creator: {
    icon: '★',
    label: 'Verified creator',
    description: 'This creator has been verified by our team.',
  },
  official: {
    icon: '✓',
    label: 'Official account',
    description: 'This is an official account operated by the organization.',
  },
};

export function VerifiedBadge({ type, showTooltip = true }: VerifiedBadgeProps) {
  const info = verificationInfo[type];

  const badge = (
    <span 
      className={`verified-badge verified-badge--${type}`}
      aria-label={info.label}
    >
      {info.icon}
    </span>
  );

  if (!showTooltip) return badge;

  return (
    <Tooltip content={info.description}>
      {badge}
    </Tooltip>
  );
}

// Usage
function UserProfile({ user }: { user: User }) {
  return (
    <div className="user-profile">
      <Avatar src={user.avatar} alt="" />
      <span className="user-profile__name">
        {user.name}
        {user.verified && <VerifiedBadge type={user.verificationType} />}
      </span>
    </div>
  );
}
```

### Verification Levels

```tsx
// VerificationLevel.tsx
function VerificationLevel({ level }: { level: 0 | 1 | 2 | 3 }) {
  const levels = [
    { label: 'Unverified', color: 'gray', description: 'No verification' },
    { label: 'Email verified', color: 'blue', description: 'Email address confirmed' },
    { label: 'ID verified', color: 'green', description: 'Government ID verified' },
    { label: 'Fully verified', color: 'gold', description: 'ID, address, and phone verified' },
  ];

  const current = levels[level];

  return (
    <div className={`verification-level verification-level--${current.color}`}>
      <div className="verification-level__progress">
        {levels.map((_, i) => (
          <div 
            key={i}
            className={`verification-level__step ${i <= level ? 'active' : ''}`}
          />
        ))}
      </div>
      <span className="verification-level__label">{current.label}</span>
      <span className="verification-level__description">{current.description}</span>
    </div>
  );
}
```

---

## PRIVACY INDICATORS

### Data Collection Notice

```tsx
// DataCollectionNotice.tsx
interface DataNoticeProps {
  dataTypes: ('email' | 'phone' | 'location' | 'usage' | 'payment')[];
  purpose: string;
  retention?: string;
}

const dataInfo = {
  email: { icon: '📧', label: 'Email address' },
  phone: { icon: '📱', label: 'Phone number' },
  location: { icon: '📍', label: 'Location' },
  usage: { icon: '📊', label: 'Usage data' },
  payment: { icon: '💳', label: 'Payment info' },
};

export function DataCollectionNotice({ dataTypes, purpose, retention }: DataNoticeProps) {
  return (
    <div className="data-notice">
      <h4>Data we collect</h4>
      
      <ul className="data-notice__list">
        {dataTypes.map(type => (
          <li key={type}>
            <span aria-hidden="true">{dataInfo[type].icon}</span>
            {dataInfo[type].label}
          </li>
        ))}
      </ul>
      
      <p className="data-notice__purpose">
        <strong>Why:</strong> {purpose}
      </p>
      
      {retention && (
        <p className="data-notice__retention">
          <strong>Retention:</strong> {retention}
        </p>
      )}
      
      <a href="/privacy" className="data-notice__link">
        View full privacy policy →
      </a>
    </div>
  );
}
```

### Privacy Mode Indicator

```tsx
// PrivacyIndicator.tsx
function PrivacyIndicator({ isPrivate }: { isPrivate: boolean }) {
  return (
    <div className={`privacy-indicator ${isPrivate ? 'privacy-indicator--private' : ''}`}>
      {isPrivate ? (
        <>
          <EyeOffIcon aria-hidden="true" />
          <span>Private mode</span>
        </>
      ) : (
        <>
          <EyeIcon aria-hidden="true" />
          <span>Public</span>
        </>
      )}
    </div>
  );
}
```

### GDPR Compliance Badge

```tsx
// GDPRBadge.tsx
function GDPRComplianceBadge() {
  return (
    <div className="compliance-badge">
      <img 
        src="/badges/gdpr.svg" 
        alt="GDPR Compliant"
        width={40}
        height={40}
      />
      <div className="compliance-badge__info">
        <strong>GDPR Compliant</strong>
        <p>We follow EU data protection regulations.</p>
        <a href="/privacy/gdpr">Learn more</a>
      </div>
    </div>
  );
}
```

---

## SOCIAL PROOF

### Customer Testimonials

```tsx
// Testimonial.tsx
interface TestimonialProps {
  quote: string;
  author: {
    name: string;
    title: string;
    company: string;
    avatar: string;
    verified?: boolean;
  };
}

export function Testimonial({ quote, author }: TestimonialProps) {
  return (
    <blockquote className="testimonial">
      <p className="testimonial__quote">"{quote}"</p>
      
      <footer className="testimonial__author">
        <img 
          src={author.avatar} 
          alt=""
          className="testimonial__avatar"
          width={48}
          height={48}
        />
        <div className="testimonial__info">
          <cite className="testimonial__name">
            {author.name}
            {author.verified && <VerifiedBadge type="identity" />}
          </cite>
          <span className="testimonial__title">
            {author.title}, {author.company}
          </span>
        </div>
      </footer>
    </blockquote>
  );
}
```

### Trust Statistics

```tsx
// TrustStats.tsx
function TrustStats() {
  return (
    <div className="trust-stats" aria-label="Company statistics">
      <div className="trust-stats__item">
        <span className="trust-stats__number">500K+</span>
        <span className="trust-stats__label">Happy customers</span>
      </div>
      <div className="trust-stats__item">
        <span className="trust-stats__number">4.9/5</span>
        <span className="trust-stats__label">Average rating</span>
      </div>
      <div className="trust-stats__item">
        <span className="trust-stats__number">99.9%</span>
        <span className="trust-stats__label">Uptime</span>
      </div>
    </div>
  );
}
```

### Review Aggregate

```tsx
// ReviewAggregate.tsx
function ReviewAggregate({ 
  average, 
  count, 
  distribution 
}: { 
  average: number;
  count: number;
  distribution: number[];  // [5star, 4star, 3star, 2star, 1star]
}) {
  return (
    <div className="review-aggregate">
      <div className="review-aggregate__summary">
        <span className="review-aggregate__score">{average.toFixed(1)}</span>
        <StarRating value={average} readonly />
        <span className="review-aggregate__count">{count.toLocaleString()} reviews</span>
      </div>
      
      <div className="review-aggregate__distribution">
        {[5, 4, 3, 2, 1].map((stars, i) => (
          <div key={stars} className="review-aggregate__bar">
            <span>{stars} star</span>
            <div className="review-aggregate__track">
              <div 
                className="review-aggregate__fill"
                style={{ width: `${(distribution[i] / count) * 100}%` }}
              />
            </div>
            <span>{distribution[i]}</span>
          </div>
        ))}
      </div>
    </div>
  );
}
```

---

## SUPPORT INDICATORS

### Live Support Status

```tsx
// SupportStatus.tsx
function SupportStatus() {
  const { isOnline, responseTime } = useSupportStatus();

  return (
    <div className="support-status">
      <span className={`support-status__indicator ${isOnline ? 'online' : 'offline'}`} />
      
      {isOnline ? (
        <>
          <span>Live support available</span>
          <span className="support-status__time">
            ~{responseTime} min response
          </span>
        </>
      ) : (
        <>
          <span>Support offline</span>
          <span className="support-status__time">
            Back at 9am EST
          </span>
        </>
      )}
    </div>
  );
}
```

### Money-Back Guarantee

```tsx
// Guarantee.tsx
function MoneyBackGuarantee({ days }: { days: number }) {
  return (
    <div className="guarantee">
      <ShieldCheckIcon aria-hidden="true" />
      <div className="guarantee__content">
        <strong>{days}-day money-back guarantee</strong>
        <p>Not satisfied? Get a full refund within {days} days, no questions asked.</p>
      </div>
    </div>
  );
}
```

---

## CHECKOUT TRUST STRIP

```tsx
// CheckoutTrustStrip.tsx
function CheckoutTrustStrip() {
  return (
    <div className="checkout-trust-strip">
      <div className="checkout-trust-strip__item">
        <LockIcon aria-hidden="true" />
        <span>Secure checkout</span>
      </div>
      
      <div className="checkout-trust-strip__item">
        <RefreshIcon aria-hidden="true" />
        <span>30-day returns</span>
      </div>
      
      <div className="checkout-trust-strip__item">
        <TruckIcon aria-hidden="true" />
        <span>Free shipping</span>
      </div>
      
      <div className="checkout-trust-strip__item">
        <HeadphonesIcon aria-hidden="true" />
        <span>24/7 support</span>
      </div>
    </div>
  );
}
```

---

## CSS FOR TRUST SIGNALS

```css
/* Verified badge */
.verified-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  font-size: 12px;
  margin-left: var(--space-1);
}

.verified-badge--identity,
.verified-badge--official {
  background: var(--accent);
  color: var(--accent-contrast);
}

.verified-badge--business {
  background: var(--success);
  color: var(--success-contrast);
}

.verified-badge--creator {
  background: linear-gradient(135deg, #FFD700, #FFA500);
  color: #000;
}

/* Payment security */
.payment-security {
  display: flex;
  flex-direction: column;
  gap: var(--space-3);
  padding: var(--space-4);
  background: var(--surface-2);
  border-radius: var(--radius-md);
  border: 1px solid var(--border);
}

.payment-security__lock {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-weight: 600;
  color: var(--success);
}

.payment-security__badges {
  display: flex;
  gap: var(--space-3);
  align-items: center;
}

.payment-security__text {
  font-size: var(--text-sm);
  color: var(--text-muted);
}

/* Trust stats */
.trust-stats {
  display: flex;
  justify-content: center;
  gap: var(--space-8);
  padding: var(--space-6) 0;
}

.trust-stats__item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.trust-stats__number {
  font-size: var(--text-3xl);
  font-weight: 700;
  color: var(--text-1);
}

.trust-stats__label {
  font-size: var(--text-sm);
  color: var(--text-muted);
}

/* Guarantee */
.guarantee {
  display: flex;
  gap: var(--space-3);
  padding: var(--space-4);
  background: var(--success-bg);
  border-radius: var(--radius-md);
  border: 1px solid var(--success-border);
}

.guarantee svg {
  color: var(--success);
  flex-shrink: 0;
}

/* Trust strip */
.checkout-trust-strip {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: var(--space-6);
  padding: var(--space-4);
  background: var(--surface-2);
  border-top: 1px solid var(--border);
}

.checkout-trust-strip__item {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-sm);
  color: var(--text-muted);
}

.checkout-trust-strip__item svg {
  width: 20px;
  height: 20px;
}
```

---

## PLACEMENT GUIDELINES

| Signal | Where to Place |
|--------|---------------|
| SSL/Security | Header (subtle), checkout (prominent) |
| Verified badges | Next to user/business names |
| Privacy notices | Data collection forms |
| Testimonials | Landing pages, pricing |
| Review scores | Product pages, search results |
| Guarantees | Checkout, pricing pages |
| Support status | Footer, help section, checkout |
| Certifications | Footer, about page, checkout |

---

## CHECKLIST

```
□ Only display real certifications
□ Badges have alt text and tooltips
□ Verified badges explain what they mean
□ Privacy notices appear at data collection points
□ Security badges prominent at checkout
□ Testimonials include real names and photos
□ Statistics are accurate and current
□ Support availability is clearly indicated
□ Guarantees are specific (not vague promises)
□ Trust signals don't clutter the UI
□ Mobile-friendly sizing for badges
```
