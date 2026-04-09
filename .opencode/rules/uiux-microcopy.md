---
description: Error messages that don't suck, empty states with personality, CTAs that convert, and microcopy that builds trust. Words matter.
---

# Microcopy — Words That Work

> The smallest words have the biggest impact. This file covers error messages,
> empty states, button labels, tooltips, and all the tiny text that makes
> the difference between "I hate this app" and "this app gets me."

---

## CRITICAL RULES

1. **Human first** — Write like a helpful person, not a robot.
2. **Blame the system, not the user** — "We couldn't find that" not "You typed it wrong."
3. **Always offer a way forward** — Don't just state the problem.
4. **Be specific** — "Password must be 8+ characters" not "Invalid password."
5. **Match the brand voice** — Consistent tone everywhere.

---

## THE MICROCOPY SPECTRUM

```
[Functional]                                              [Delightful]
   |                                                            |
"Error"                                              "Oops! That didn't work.
                                                      Let's try again."
   
"Submit"                                              "Send my message →"
   
"No results"                                          "Nothing here yet.
                                                       Time to create something!"
```

**Rule**: Professional apps lean left, consumer apps lean right. Never be cold.

---

## ERROR MESSAGES

### The Formula

```
[What happened] + [Why it might have happened] + [What to do now]
```

### Bad vs. Good Examples

| Bad ❌ | Good ✅ |
|--------|---------|
| `Error 500` | `Something went wrong on our end. We're looking into it. Try again in a few minutes.` |
| `Invalid input` | `Please enter a valid email address (e.g., name@example.com)` |
| `Authentication failed` | `That password doesn't match our records. Forgot your password?` |
| `Network error` | `We can't reach the server right now. Check your connection and try again.` |
| `File too large` | `This file is 25MB, but the limit is 10MB. Try compressing it or choosing a smaller file.` |
| `Permission denied` | `You don't have access to this page. Contact your admin to request access.` |

### Error Message Component

```tsx
// ErrorMessage.tsx
interface ErrorMessageProps {
  type: 'validation' | 'network' | 'permission' | 'server' | 'notFound';
  field?: string;
  detail?: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}

const errorCopy = {
  validation: {
    title: 'Please check your input',
    icon: '⚠️',
  },
  network: {
    title: 'Connection issue',
    description: 'We can\'t reach the server. Check your internet connection.',
    icon: '📡',
  },
  permission: {
    title: 'Access denied',
    description: 'You don\'t have permission to view this.',
    icon: '🔒',
  },
  server: {
    title: 'Something went wrong',
    description: 'Our servers are having trouble. We\'ve been notified.',
    icon: '🔧',
  },
  notFound: {
    title: 'Not found',
    description: 'We couldn\'t find what you\'re looking for.',
    icon: '🔍',
  },
};

export function ErrorMessage({ type, field, detail, action }: ErrorMessageProps) {
  const copy = errorCopy[type];

  return (
    <div className="error-message" role="alert">
      <span className="error-message__icon" aria-hidden="true">{copy.icon}</span>
      
      <div className="error-message__content">
        <strong className="error-message__title">
          {field ? `${field}: ${copy.title}` : copy.title}
        </strong>
        
        <p className="error-message__description">
          {detail || copy.description}
        </p>
        
        {action && (
          <button 
            className="error-message__action"
            onClick={action.onClick}
          >
            {action.label}
          </button>
        )}
      </div>
    </div>
  );
}
```

---

## EMPTY STATES

Empty states are opportunities, not dead ends.

### The Formula

```
[Acknowledge the emptiness] + [Explain value of content] + [Clear CTA to create]
```

### Empty State Examples

```tsx
// EmptyState.tsx
interface EmptyStateProps {
  type: 'search' | 'list' | 'inbox' | 'cart' | 'notifications' | 'custom';
  searchQuery?: string;
  customTitle?: string;
  customDescription?: string;
  customIcon?: string;
  action?: {
    label: string;
    onClick: () => void;
  };
}

const emptyStateCopy = {
  search: {
    icon: '🔍',
    title: (query?: string) => query ? `No results for "${query}"` : 'No results',
    description: 'Try different keywords or check your spelling',
    actionLabel: 'Clear search',
  },
  list: {
    icon: '📝',
    title: () => 'Nothing here yet',
    description: 'Create your first item to get started',
    actionLabel: 'Create new',
  },
  inbox: {
    icon: '📭',
    title: () => 'All caught up!',
    description: 'No new messages. Enjoy the peace and quiet.',
    actionLabel: null,
  },
  cart: {
    icon: '🛒',
    title: () => 'Your cart is empty',
    description: 'Looks like you haven\'t added anything yet',
    actionLabel: 'Start shopping',
  },
  notifications: {
    icon: '🔔',
    title: () => 'No notifications',
    description: 'We\'ll let you know when something happens',
    actionLabel: null,
  },
};

export function EmptyState({ 
  type, 
  searchQuery, 
  customTitle,
  customDescription,
  customIcon,
  action 
}: EmptyStateProps) {
  const copy = type === 'custom' ? null : emptyStateCopy[type];

  return (
    <div className="empty-state" role="status">
      <span className="empty-state__icon" aria-hidden="true">
        {customIcon || copy?.icon}
      </span>
      
      <h3 className="empty-state__title">
        {customTitle || copy?.title(searchQuery)}
      </h3>
      
      <p className="empty-state__description">
        {customDescription || copy?.description}
      </p>
      
      {action && (
        <button 
          className="btn btn--primary empty-state__action"
          onClick={action.onClick}
        >
          {action.label}
        </button>
      )}
    </div>
  );
}
```

### Empty State with Illustration

```tsx
// IllustratedEmptyState.tsx
function ProjectsEmptyState() {
  return (
    <div className="empty-state empty-state--illustrated">
      <img 
        src="/illustrations/empty-projects.svg" 
        alt=""
        className="empty-state__illustration"
        width={200}
        height={150}
      />
      
      <h3>Start your first project</h3>
      <p>
        Projects help you organize your work and collaborate with others.
        Create one to get started.
      </p>
      
      <div className="empty-state__actions">
        <button className="btn btn--primary">
          Create project
        </button>
        <button className="btn btn--secondary">
          Import from GitHub
        </button>
      </div>
      
      <a href="/help/projects" className="empty-state__help">
        Learn more about projects →
      </a>
    </div>
  );
}
```

---

## BUTTON LABELS

### Principles

1. **Use verbs** — "Create account" not "Account creation"
2. **Be specific** — "Save changes" not just "Save"
3. **Show outcome** — "Send message" not "Submit"
4. **Primary = action** — What happens when clicked
5. **Secondary = escape** — How to cancel/back

### Button Label Examples

| Context | Bad ❌ | Good ✅ |
|---------|--------|---------|
| Form submit | Submit | Save changes |
| Newsletter | Submit | Subscribe |
| Contact form | Send | Send message |
| Checkout | Continue | Pay $49.00 |
| Delete | Delete | Delete project |
| Destructive | OK | Yes, delete permanently |
| Cancel | No | Keep editing |
| Login | Login | Sign in |
| Register | Register | Create free account |
| Download | Download | Download PDF (2.4 MB) |
| Loading | Loading... | Saving... |

### Confirmation Dialogs

```tsx
// ConfirmDialog.tsx
interface ConfirmDialogProps {
  title: string;
  description: string;
  confirmLabel: string;
  cancelLabel: string;
  variant: 'danger' | 'warning' | 'info';
  onConfirm: () => void;
  onCancel: () => void;
}

// Good example
<ConfirmDialog
  title="Delete this project?"
  description="This will permanently delete 'My Project' and all its files. This action cannot be undone."
  confirmLabel="Yes, delete project"
  cancelLabel="Keep project"
  variant="danger"
  onConfirm={handleDelete}
  onCancel={handleCancel}
/>

// Bad example (what does "OK" mean?)
<ConfirmDialog
  title="Are you sure?"
  description="This action cannot be undone."
  confirmLabel="OK"
  cancelLabel="Cancel"
  variant="danger"
  onConfirm={handleDelete}
  onCancel={handleCancel}
/>
```

---

## FORM LABELS & HELPERS

### Label Best Practices

```html
<!-- Bad: Ambiguous -->
<label>Name</label>

<!-- Good: Specific -->
<label>Full name</label>

<!-- Better: With format hint -->
<label>Full name <span class="label-hint">(as it appears on your ID)</span></label>
```

### Helper Text

```tsx
// FormField.tsx
interface FormFieldProps {
  label: string;
  hint?: string;
  error?: string;
  example?: string;
  required?: boolean;
  children: React.ReactNode;
}

export function FormField({ 
  label, 
  hint, 
  error, 
  example,
  required,
  children 
}: FormFieldProps) {
  return (
    <div className={`form-field ${error ? 'form-field--error' : ''}`}>
      <label className="form-field__label">
        {label}
        {required && <span className="form-field__required" aria-label="required">*</span>}
      </label>
      
      {hint && (
        <p className="form-field__hint">{hint}</p>
      )}
      
      {children}
      
      {error ? (
        <p className="form-field__error" role="alert">{error}</p>
      ) : example ? (
        <p className="form-field__example">Example: {example}</p>
      ) : null}
    </div>
  );
}

// Usage
<FormField
  label="Phone number"
  hint="We'll only use this for account recovery"
  example="+1 (555) 123-4567"
  required
>
  <input type="tel" />
</FormField>
```

---

## TOOLTIPS & HINTS

### When to Use Tooltips

- ✅ Explaining icons
- ✅ Providing additional context
- ✅ Showing keyboard shortcuts
- ❌ Essential information (use inline text)
- ❌ Long content (use a popover)
- ❌ Mobile-only features (no hover)

### Good Tooltip Examples

```tsx
// Tooltip examples
<Tooltip content="Copy to clipboard (⌘C)">
  <button aria-label="Copy"><CopyIcon /></button>
</Tooltip>

<Tooltip content="Only you can see this draft">
  <span className="status-badge">Draft</span>
</Tooltip>

<Tooltip content="Last edited 2 hours ago by Alex">
  <time>2h ago</time>
</Tooltip>
```

---

## SUCCESS MESSAGES

Don't just confirm — celebrate and guide:

```tsx
// SuccessMessage.tsx
const successMessages = {
  saved: {
    title: 'Changes saved',
    description: 'Your updates are live.',
    icon: '✓',
  },
  sent: {
    title: 'Message sent!',
    description: 'We\'ll get back to you within 24 hours.',
    icon: '📧',
  },
  subscribed: {
    title: 'You\'re in!',
    description: 'Check your inbox for a confirmation email.',
    icon: '🎉',
  },
  published: {
    title: 'Post published',
    description: 'Your post is now live and visible to everyone.',
    icon: '🚀',
    action: { label: 'View post', href: '/post/123' },
  },
  purchased: {
    title: 'Order confirmed!',
    description: 'We\'ve sent a receipt to your email.',
    icon: '✨',
    action: { label: 'Track order', href: '/orders/123' },
  },
};

export function SuccessMessage({ 
  type, 
  customTitle,
  customDescription,
}: { 
  type: keyof typeof successMessages;
  customTitle?: string;
  customDescription?: string;
}) {
  const copy = successMessages[type];

  return (
    <div className="success-message" role="status">
      <span className="success-message__icon">{copy.icon}</span>
      <strong>{customTitle || copy.title}</strong>
      <p>{customDescription || copy.description}</p>
      {copy.action && (
        <a href={copy.action.href}>{copy.action.label} →</a>
      )}
    </div>
  );
}
```

---

## LOADING & PROCESSING STATES

Be specific about what's happening:

| Context | Bad ❌ | Good ✅ |
|---------|--------|---------|
| General | Loading... | Loading your dashboard... |
| Save | Processing... | Saving changes... |
| Upload | Please wait... | Uploading photo (3 of 5)... |
| Payment | Loading... | Processing payment... |
| Search | Searching... | Searching 10,000 products... |
| AI | Loading... | Generating response... |

---

## PLACEHOLDER TEXT

### Input Placeholders

Placeholders should be **examples**, not labels:

```html
<!-- Bad: Label as placeholder -->
<input placeholder="Email">

<!-- Good: Example as placeholder -->
<label>Email</label>
<input placeholder="name@company.com">

<!-- Better: No placeholder (cleaner) -->
<label>Email</label>
<input>
```

### Textarea Placeholders

```html
<!-- Bad: Too generic -->
<textarea placeholder="Enter your message"></textarea>

<!-- Good: Encouraging and specific -->
<textarea placeholder="Tell us about your project, goals, and timeline..."></textarea>
```

---

## CALL-TO-ACTION (CTA) WRITING

### Primary CTA Formula

```
[Action verb] + [What they get] + [Value hint]
```

### CTA Examples

| Page | Weak ❌ | Strong ✅ |
|------|--------|----------|
| Pricing | Buy | Start free trial |
| SaaS | Sign up | Get started free |
| Newsletter | Submit | Join 50,000 readers |
| Download | Download | Get the free guide |
| Booking | Continue | Book my consultation |
| E-commerce | Add to cart | Add to bag — $49 |

---

## MICROCOPY STYLE GUIDE

```markdown
## Voice & Tone

### We are:
- Helpful, not preachy
- Confident, not arrogant  
- Friendly, not casual
- Clear, not clever

### We use:
- Active voice ("We sent your email" not "Your email was sent")
- Second person ("your account" not "the account")
- Contractions ("don't", "we'll", "you're")
- Positive framing ("Save changes" not "Don't lose changes")

### We avoid:
- Jargon ("Invalid OAuth token" → "Login expired, please sign in again")
- Blame ("You entered wrong password" → "That password didn't work")
- Exclamation points (except celebrations!)
- ALL CAPS
- "Please" at the start of everything
```

---

## CHECKLIST

```
□ Error messages explain what went wrong
□ Error messages offer a way forward
□ Empty states have clear CTAs
□ Buttons use action verbs
□ Destructive actions have clear confirmation
□ Success messages confirm and guide
□ Loading states are specific
□ Placeholders are examples, not labels
□ Tooltips are concise (under 10 words)
□ Voice is consistent across the app
□ No jargon or technical terms
□ No blame language
```

---

## MICROCOPY AUDIT CHECKLIST

Review every piece of text:

1. **Is it human?** Would a helpful person say this?
2. **Is it clear?** Can a 12-year-old understand it?
3. **Is it useful?** Does it help the user?
4. **Is it necessary?** Can it be shorter?
5. **Is it kind?** Does it blame the user?
6. **Is it actionable?** Is there a clear next step?
