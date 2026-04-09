# UI/UX Forms 2026

> Multi-step forms, autocomplete, password strength, phone + country picker,
> optimistic vs blocking submission, and form validation patterns.
> Forms are where users abandon products. Polish here = trust.

---

## CRITICAL RULE: NO NATIVE BROWSER UI

> **All native browser UI elements (date picker, time picker, color picker, select dropdown, alerts) are prohibited.**  
> Use custom token‑styled components as described in this file and `uiux-interactive.md`.  
> For date/time pickers, use Flatpickr (already in CDN stack) with token-based CSS overrides.  
> For alerts/confirmations, use `swal` helpers from `uiux-components.md`.

---

## MULTI-STEP FORM

### HTML Structure

```html
<form class="multistep-form" id="msf" novalidate aria-label="Registration form">
  <!-- Progress bar -->
  <div class="msf-progress" role="progressbar"
    aria-valuenow="1" aria-valuemin="1" aria-valuemax="3"
    aria-label="Step 1 of 3">
    <div class="msf-progress__bar" style="width: 33.33%"></div>
  </div>

  <!-- Step indicator -->
  <nav class="msf-steps" aria-label="Form steps">
    <button type="button" class="msf-step active" data-step="1"
      aria-current="step" aria-label="Step 1: Your info">
      <span class="msf-step__num">1</span>
      <span class="msf-step__label">Your info</span>
    </button>
    <span class="msf-step-divider" aria-hidden="true"></span>
    <button type="button" class="msf-step" data-step="2"
      aria-label="Step 2: Account">
      <span class="msf-step__num">2</span>
      <span class="msf-step__label">Account</span>
    </button>
    <span class="msf-step-divider" aria-hidden="true"></span>
    <button type="button" class="msf-step" data-step="3"
      aria-label="Step 3: Confirm">
      <span class="msf-step__num">3</span>
      <span class="msf-step__label">Confirm</span>
    </button>
  </nav>

  <!-- Panels -->
  <div class="msf-panel active" data-panel="1" role="group" aria-label="Step 1: Your info">
    <div class="field">
      <input class="field__input" type="text" id="first-name" name="firstName"
        placeholder=" " autocomplete="given-name" required minlength="2">
      <label class="field__label" for="first-name">First name</label>
      <span class="field__error" role="alert"></span>
    </div>
    <div class="field">
      <input class="field__input" type="text" id="last-name" name="lastName"
        placeholder=" " autocomplete="family-name" required minlength="2">
      <label class="field__label" for="last-name">Last name</label>
      <span class="field__error" role="alert"></span>
    </div>
  </div>

  <div class="msf-panel" data-panel="2" role="group" aria-label="Step 2: Account" hidden>
    <div class="field">
      <input class="field__input" type="email" id="email" name="email"
        placeholder=" " autocomplete="email" required>
      <label class="field__label" for="email">Email address</label>
      <span class="field__error" role="alert"></span>
    </div>
    <!-- Password with strength meter (see below) -->
  </div>

  <div class="msf-panel" data-panel="3" role="group" aria-label="Step 3: Confirm" hidden>
    <!-- Summary / review -->
    <div class="msf-summary" id="msf-summary"></div>
  </div>

  <!-- Navigation -->
  <div class="msf-nav">
    <button type="button" class="btn btn--ghost msf-btn-back" id="msf-back" hidden>
      ← Back
    </button>
    <button type="button" class="btn btn--primary msf-btn-next" id="msf-next">
      Continue →
    </button>
    <button type="submit" class="btn btn--primary msf-btn-submit" id="msf-submit" hidden>
      Create account
    </button>
  </div>
</form>
```

```css
/* Progress bar */
.msf-progress {
  height: 3px; background: var(--surface-up); border-radius: var(--radius-full);
  margin-bottom: var(--space-8); overflow: hidden;
}
.msf-progress__bar {
  height: 100%; background: var(--accent);
  border-radius: var(--radius-full);
  transition: width 0.4s var(--ease-out);
}

/* Step indicators */
.msf-steps {
  display: flex; align-items: center; gap: 0;
  margin-bottom: var(--space-8);
}
.msf-step {
  display: flex; align-items: center; gap: var(--space-2);
  background: none; border: none; cursor: default;
  font-size: var(--text-sm); color: var(--muted);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  transition: color var(--transition-micro);
}
.msf-step.active   { color: var(--accent); cursor: default; }
.msf-step.complete { color: var(--color-success); cursor: pointer; }
.msf-step.complete:hover { background: var(--surface-up); }
.msf-step__num {
  width: 24px; height: 24px; border-radius: 50%;
  background: var(--surface-up); border: 1.5px solid var(--border);
  display: grid; place-items: center;
  font-size: var(--text-xs); font-weight: 700;
  transition: background var(--transition-micro), border-color var(--transition-micro);
}
.msf-step.active   .msf-step__num { background: var(--accent); border-color: var(--accent); color: white; }
.msf-step.complete .msf-step__num { background: var(--color-success); border-color: var(--color-success); color: white; }
.msf-step-divider { flex: 1; height: 1px; background: var(--border); min-width: 16px; }

/* Panels */
.msf-panel { animation: panel-in 0.3s var(--ease-out); }
@keyframes panel-in {
  from { opacity: 0; transform: translateX(12px); }
  to   { opacity: 1; transform: translateX(0); }
}

/* Nav row */
.msf-nav {
  display: flex; justify-content: space-between; align-items: center;
  margin-top: var(--space-8); padding-top: var(--space-6);
  border-top: 1px solid var(--border);
}
```

```javascript
class MultiStepForm {
  constructor(formEl) {
    this.form    = formEl;
    this.panels  = formEl.querySelectorAll('.msf-panel');
    this.steps   = formEl.querySelectorAll('.msf-step');
    this.bar     = formEl.querySelector('.msf-progress__bar');
    this.progress= formEl.querySelector('.msf-progress');
    this.current = 1;
    this.total   = this.panels.length;

    formEl.querySelector('#msf-next').addEventListener('click', () => this.next());
    formEl.querySelector('#msf-back').addEventListener('click', () => this.prev());
    formEl.addEventListener('submit', e => this.submit(e));
    this.steps.forEach(s => s.addEventListener('click', () => {
      const target = parseInt(s.dataset.step);
      if (target < this.current) this.goTo(target);
    }));
  }

  validate(panel) {
    const inputs = panel.querySelectorAll('[required]');
    let valid = true;
    inputs.forEach(input => {
      const field = input.closest('.field');
      const errorEl = field?.querySelector('.field__error');
      if (!input.checkValidity()) {
        valid = false;
        if (field) field.dataset.state = 'error';
        if (errorEl) errorEl.textContent = input.validationMessage;
      } else {
        if (field) field.dataset.state = 'success';
        if (errorEl) errorEl.textContent = '';
      }
    });
    if (!valid) inputs[0]?.focus();
    return valid;
  }

  goTo(n) {
    const panel = this.form.querySelector(`[data-panel="${this.current}"]`);
    if (n > this.current && !this.validate(panel)) return;

    this.form.querySelector(`[data-panel="${this.current}"]`).hidden = true;
    this.steps[this.current - 1].classList.remove('active');
    if (n > this.current) this.steps[this.current - 1].classList.add('complete');

    this.current = n;
    const next = this.form.querySelector(`[data-panel="${this.current}"]`);
    next.hidden = false;
    this.steps[this.current - 1].classList.add('active');
    this.steps[this.current - 1].classList.remove('complete');

    // Progress bar
    this.bar.style.width = `${(this.current / this.total) * 100}%`;
    this.progress.setAttribute('aria-valuenow', this.current);

    // Button visibility
    this.form.querySelector('#msf-back').hidden   = this.current === 1;
    this.form.querySelector('#msf-next').hidden   = this.current === this.total;
    this.form.querySelector('#msf-submit').hidden = this.current !== this.total;
    next.querySelector('input, button, select')?.focus();
  }

  next() { this.goTo(this.current + 1); }
  prev() { this.goTo(this.current - 1); }

  async submit(e) {
    e.preventDefault();
    const panel = this.form.querySelector(`[data-panel="${this.current}"]`);
    if (!this.validate(panel)) return;
    const btn = this.form.querySelector('#msf-submit');
    btn.classList.add('btn--loading');
    try {
      const data = Object.fromEntries(new FormData(this.form));
      await fetch('/api/register', { method: 'POST', body: JSON.stringify(data),
        headers: { 'Content-Type': 'application/json' } });
      swal.success('Account created!', 'Welcome aboard.');
    } catch {
      swal.error('Something went wrong', 'Please try again.');
    } finally {
      btn.classList.remove('btn--loading');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('msf');
  if (el) new MultiStepForm(el);
});
```

---

## AUTOCOMPLETE BEST PRACTICES

```html
<!-- Use standard autocomplete token values — browsers autofill these -->

<!-- Name fields -->
<input autocomplete="given-name"     name="firstName">
<input autocomplete="family-name"    name="lastName">
<input autocomplete="name"           name="fullName">

<!-- Address — critical for checkout (browsers autofill entire address) -->
<input autocomplete="address-line1"   name="address1">
<input autocomplete="address-line2"   name="address2">
<input autocomplete="address-level2"  name="city">       <!-- city -->
<input autocomplete="address-level1"  name="state">      <!-- state/province -->
<input autocomplete="postal-code"     name="zip">
<input autocomplete="country-name"    name="country">

<!-- Contact -->
<input autocomplete="email"           name="email"  type="email">
<input autocomplete="tel"             name="phone"  type="tel">

<!-- Auth -->
<input autocomplete="username"        name="username">
<input autocomplete="current-password" name="password"  type="password">
<input autocomplete="new-password"    name="newPassword" type="password">
<input autocomplete="one-time-code"   name="otp"         inputmode="numeric">

<!-- Credit card — must be inside a <form> for browser autofill -->
<input autocomplete="cc-name"         name="ccName">
<input autocomplete="cc-number"       name="ccNumber"    inputmode="numeric">
<input autocomplete="cc-exp-month"    name="ccMonth">
<input autocomplete="cc-exp-year"     name="ccYear">
<input autocomplete="cc-csc"          name="ccCsc"       inputmode="numeric">
```

**Critical rules:**
- Never use `autocomplete="off"` on checkout/payment forms — it breaks browser autofill
- Always include `name` attribute — autocomplete requires it
- Use `inputmode` for number-only fields — shows correct mobile keyboard

---

## PASSWORD STRENGTH METER

```html
<div class="field field-password" id="field-password">
  <input class="field__input" type="password" id="password" name="password"
    placeholder=" " autocomplete="new-password" required
    aria-describedby="pw-strength-desc">
  <label class="field__label" for="password">Password</label>
  <button type="button" class="pw-toggle" id="pw-toggle"
    aria-label="Show password" aria-controls="password">
    <i data-lucide="eye" id="pw-icon"></i>
  </button>

  <!-- Strength meter -->
  <div class="pw-strength" id="pw-strength" aria-hidden="true">
    <div class="pw-strength__bar">
      <div class="pw-strength__fill" id="pw-fill"></div>
    </div>
    <span class="pw-strength__label" id="pw-label"></span>
  </div>
  <span class="sr-only" id="pw-strength-desc" aria-live="polite"></span>

  <!-- Requirements checklist -->
  <ul class="pw-requirements" aria-label="Password requirements">
    <li class="pw-req" data-req="length"><i data-lucide="circle"></i> At least 8 characters</li>
    <li class="pw-req" data-req="upper" ><i data-lucide="circle"></i> One uppercase letter</li>
    <li class="pw-req" data-req="number"><i data-lucide="circle"></i> One number</li>
    <li class="pw-req" data-req="special"><i data-lucide="circle"></i> One special character</li>
  </ul>
  <span class="field__error" role="alert"></span>
</div>

<!-- Confirm password -->
<div class="field" id="field-confirm">
  <input class="field__input" type="password" id="confirm-password"
    name="confirmPassword" placeholder=" " autocomplete="new-password" required
    aria-describedby="confirm-desc">
  <label class="field__label" for="confirm-password">Confirm password</label>
  <span class="field__error" role="alert" id="confirm-desc"></span>
</div>
```

```css
/* Toggle button inside input */
.field-password { position: relative; }
.pw-toggle {
  position: absolute; right: 12px; top: 50%;
  transform: translateY(-50%);
  width: 36px; height: 36px; display: grid; place-items: center;
  color: var(--muted); border-radius: var(--radius-sm);
  transition: color var(--transition-micro);
  min-height: unset; /* override base touch target for icon buttons */
}
.pw-toggle:hover { color: var(--text); }
.pw-toggle svg { width: 16px; height: 16px; }

/* Strength bar */
.pw-strength {
  display: flex; align-items: center; gap: var(--space-3);
  margin-top: var(--space-2);
}
.pw-strength__bar {
  flex: 1; height: 4px; background: var(--surface-up);
  border-radius: var(--radius-full); overflow: hidden;
}
.pw-strength__fill {
  height: 100%; border-radius: var(--radius-full);
  transition: width 0.3s var(--ease-out), background 0.3s;
  width: 0;
}
.pw-strength__fill.weak    { width: 25%; background: var(--color-error); }
.pw-strength__fill.fair    { width: 50%; background: var(--color-warning); }
.pw-strength__fill.good    { width: 75%; background: #22C55E; }
.pw-strength__fill.strong  { width: 100%; background: var(--color-success); }
.pw-strength__label { font-size: var(--text-xs); font-weight: 600;
  min-width: 44px; }
.pw-strength__fill.weak   + .pw-strength__label,
.pw-strength__label.weak   { color: var(--color-error); }
.pw-strength__label.fair   { color: var(--color-warning); }
.pw-strength__label.good   { color: #16A34A; }
.pw-strength__label.strong { color: var(--color-success); }

/* Requirements list */
.pw-requirements {
  list-style: none; display: flex; flex-direction: column; gap: var(--space-1);
  margin-top: var(--space-3); padding-left: var(--space-1);
}
.pw-req {
  display: flex; align-items: center; gap: var(--space-2);
  font-size: var(--text-xs); color: var(--muted);
  transition: color var(--transition-micro);
}
.pw-req svg { width: 12px; height: 12px; flex-shrink: 0; }
.pw-req.met { color: var(--color-success); }
.pw-req.met svg { color: var(--color-success); }
```

```javascript
const pwInput   = document.getElementById('password');
const pwFill    = document.getElementById('pw-fill');
const pwLabel   = document.getElementById('pw-label');
const pwDesc    = document.getElementById('pw-strength-desc');
const confirmEl = document.getElementById('confirm-password');

// Show/hide toggle
document.getElementById('pw-toggle').addEventListener('click', function() {
  const isText = pwInput.type === 'text';
  pwInput.type = isText ? 'password' : 'text';
  document.getElementById('pw-icon').setAttribute('data-lucide', isText ? 'eye' : 'eye-off');
  lucide.createIcons();
  this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
});

// Strength evaluation
const rules = {
  length:  v => v.length >= 8,
  upper:   v => /[A-Z]/.test(v),
  number:  v => /\d/.test(v),
  special: v => /[!@#$%^&*(),.?":{}|<>]/.test(v),
};

function getStrength(v) {
  const score = Object.values(rules).filter(fn => fn(v)).length;
  if (score <= 1) return { level: 'weak',   label: 'Weak' };
  if (score === 2) return { level: 'fair',  label: 'Fair' };
  if (score === 3) return { level: 'good',  label: 'Good' };
  return { level: 'strong', label: 'Strong' };
}

pwInput.addEventListener('input', () => {
  const val = pwInput.value;

  // Update requirement checks
  Object.entries(rules).forEach(([key, fn]) => {
    document.querySelector(`[data-req="${key}"]`)?.classList.toggle('met', fn(val));
  });

  // Update strength bar
  if (!val) { pwFill.style.width = '0'; pwFill.className = 'pw-strength__fill';
    pwLabel.textContent = ''; return; }
  const { level, label } = getStrength(val);
  pwFill.className = `pw-strength__fill ${level}`;
  pwLabel.className = `pw-strength__label ${level}`;
  pwLabel.textContent = label;
  pwDesc.textContent = `Password strength: ${label}`;
});

// Confirm match
confirmEl?.addEventListener('input', () => {
  const field = document.getElementById('field-confirm');
  const errorEl = document.getElementById('confirm-desc');
  const match = confirmEl.value === pwInput.value;
  field.dataset.state = confirmEl.value ? (match ? 'success' : 'error') : 'default';
  errorEl.textContent = match ? '' : 'Passwords do not match';
});
```

---

## PHONE NUMBER INPUT WITH COUNTRY CODE PICKER

```html
<div class="field field-phone">
  <label class="field__label-static" for="phone-number">Phone number</label>
  <div class="phone-input-wrap">
    <!-- Country code droptop (from uiux-interactive.md) -->
    <div class="custom-select phone-country" id="phone-country"
      role="combobox" aria-expanded="false" aria-label="Country code">
      <button class="custom-select__trigger phone-country__trigger"
        aria-controls="country-list">
        <span class="phone-country__flag" id="flag">🇺🇸</span>
        <span class="custom-select__value" id="dial-code">+1</span>
        <i data-lucide="chevron-down" class="custom-select__icon"
          style="width:12px;height:12px"></i>
      </button>
      <ul class="custom-select__list phone-country__list"
        id="country-list" role="listbox">
        <!-- Generated by JS below -->
      </ul>
    </div>

    <input class="field__input phone-number__input" type="tel"
      id="phone-number" name="phoneNumber"
      placeholder="(555) 000-0000"
      autocomplete="tel-national"
      inputmode="tel"
      aria-label="Phone number (without country code)">

    <input type="hidden" id="phone-full" name="phone">
  </div>
  <span class="field__error" role="alert"></span>
</div>
```

```css
.phone-input-wrap {
  display: flex; align-items: stretch;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-md);
  overflow: hidden;
  transition: border-color var(--transition-micro), box-shadow var(--transition-micro);
}
.phone-input-wrap:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent);
}
.phone-country__trigger {
  border: none; border-right: 1.5px solid var(--border);
  border-radius: 0; padding: 10px 12px;
  background: var(--surface-up); gap: var(--space-1);
  font-size: var(--text-sm); min-width: 80px;
}
.phone-number__input {
  flex: 1; border: none; padding: 10px 14px;
  background: var(--surface); font-size: var(--text-base);
}
.phone-number__input:focus { outline: none; }
.phone-country__list { min-width: 260px; max-height: 240px; overflow-y: auto; }
```

```javascript
const COUNTRIES = [
  { code: 'US', flag: '🇺🇸', dial: '+1',   name: 'United States' },
  { code: 'GB', flag: '🇬🇧', dial: '+44',  name: 'United Kingdom' },
  { code: 'AU', flag: '🇦🇺', dial: '+61',  name: 'Australia' },
  { code: 'CA', flag: '🇨🇦', dial: '+1',   name: 'Canada' },
  { code: 'DE', flag: '🇩🇪', dial: '+49',  name: 'Germany' },
  { code: 'FR', flag: '🇫🇷', dial: '+33',  name: 'France' },
  { code: 'JP', flag: '🇯🇵', dial: '+81',  name: 'Japan' },
  { code: 'CN', flag: '🇨🇳', dial: '+86',  name: 'China' },
  { code: 'IN', flag: '🇮🇳', dial: '+91',  name: 'India' },
  { code: 'BR', flag: '🇧🇷', dial: '+55',  name: 'Brazil' },
  { code: 'MX', flag: '🇲🇽', dial: '+52',  name: 'Mexico' },
  { code: 'PH', flag: '🇵🇭', dial: '+63',  name: 'Philippines' },
  { code: 'SG', flag: '🇸🇬', dial: '+65',  name: 'Singapore' },
  { code: 'AE', flag: '🇦🇪', dial: '+971', name: 'UAE' },
  // add more as needed
];

const list = document.getElementById('country-list');
COUNTRIES.forEach(c => {
  const li = document.createElement('li');
  li.className = 'custom-select__option'; li.role = 'option';
  li.dataset.value = c.dial; li.dataset.flag = c.flag;
  li.innerHTML = `<span style="margin-right:8px">${c.flag}</span>${c.name} <span style="color:var(--muted);margin-left:auto">${c.dial}</span>`;
  li.style.display = 'flex'; li.style.justifyContent = 'space-between';
  li.addEventListener('click', () => {
    document.getElementById('flag').textContent = c.flag;
    document.getElementById('dial-code').textContent = c.dial;
    document.getElementById('phone-country').setAttribute('aria-expanded', 'false');
    syncPhone();
  });
  list.appendChild(li);
});

function syncPhone() {
  const dial = document.getElementById('dial-code').textContent;
  const num  = document.getElementById('phone-number').value.replace(/\D/g, '');
  document.getElementById('phone-full').value = `${dial}${num}`;
}
document.getElementById('phone-number').addEventListener('input', syncPhone);
```

---

## FORM SUBMISSION — OPTIMISTIC VS BLOCKING

```javascript
// BLOCKING (default — safe, simple)
// Shows loading spinner, waits for server response before any UI change
async function submitBlocking(form) {
  const btn = form.querySelector('[type="submit"]');
  btn.classList.add('btn--loading');
  btn.disabled = true;
  try {
    const res = await fetch('/api/submit', {
      method: 'POST',
      body: new FormData(form),
    });
    if (!res.ok) throw new Error(await res.text());
    swal.success('Submitted!');
    form.reset();
  } catch (err) {
    swal.error('Submission failed', err.message);
  } finally {
    btn.classList.remove('btn--loading');
    btn.disabled = false;
  }
}

// OPTIMISTIC (advanced — instant feedback, rollback on failure)
// Use for: toggling a like, archiving an item, toggling settings
// Do NOT use for: payments, account creation, destructive actions
async function optimisticToggle(itemId, currentState) {
  // 1. Update UI immediately (assume success)
  const newState = !currentState;
  updateItemUI(itemId, newState);
  swal.toast(newState ? 'Saved' : 'Removed', 'success');

  try {
    // 2. Fire API request
    await fetch(`/api/items/${itemId}/toggle`, {
      method: 'PATCH',
      body: JSON.stringify({ active: newState }),
      headers: { 'Content-Type': 'application/json' },
    });
  } catch {
    // 3. On failure: roll back the UI
    updateItemUI(itemId, currentState);
    swal.error('Failed to save', 'Your change was reverted.');
  }
}

// WHEN TO USE EACH:
// Blocking:    payments, account creation, deleting data, anything with compliance
// Optimistic:  likes, follows, toggles, sort/reorder, non-critical settings
```

### Form Submission Decision Guide

| Action | Pattern | Reason |
|--------|---------|--------|
| Payment / checkout | Blocking | Cannot roll back money |
| Account registration | Blocking | Needs server validation |
| Delete with confirmation | Blocking + confirm dialog | Destructive |
| Toggle like/save | Optimistic | Fast, low stakes |
| Reorder items (drag) | Optimistic | UX requires instant feedback |
| Change notification preference | Optimistic | Fast, reversible |
| Upload file | Blocking + progress bar | Need to track progress |
| Send message / email | Blocking | User expects delivery confirmation |

---

## FORM QUICK REFERENCE

| Pattern | Use when | Don't use when |
|---------|---------|---------------|
| Multi-step | > 5 fields, different categories | Simple 2-3 field forms |
| Floating label | Space is limited, polished look | Dense technical forms |
| Password meter | Registration, password change | Login (where meter is useless) |
| Phone + country | International audience | Domestic-only apps |
| Optimistic submit | Toggle, sort, preference | Payments, deletion |
| Autocomplete attrs | Any form — always | Never skip these |

---

## ADDRESS AUTOCOMPLETE (Google Places / Mapbox)

**Rule**: Address autocomplete must use a custom-styled dropdown. Never use a native browser UI or the default Google/Mapbox white box.

### HTML Structure

```html
<div class="field address-field" id="address-field">
  <label class="field__label" for="address-input">Street Address</label>
  <div class="field__wrap">
    <input 
      class="field__input" 
      type="text" 
      id="address-input" 
      name="address"
      placeholder=" " 
      autocomplete="street-address"
      aria-autocomplete="list"
      aria-expanded="false"
      aria-controls="address-suggestions"
      role="combobox"
    >
    <i data-lucide="map-pin" class="field__icon-right" style="color: var(--muted)"></i>
  </div>
  
  <!-- Custom-styled suggestions dropdown -->
  <ul 
    class="address-suggestions" 
    id="address-suggestions" 
    role="listbox"
    aria-label="Address suggestions"
    hidden
  ></ul>
  
  <span class="field__helper">Start typing to search addresses</span>
  <span class="field__error" role="alert"></span>
</div>

<!-- Hidden fields for structured address data -->
<input type="hidden" id="address-street" name="street">
<input type="hidden" id="address-city" name="city">
<input type="hidden" id="address-state" name="state">
<input type="hidden" id="address-zip" name="zip">
<input type="hidden" id="address-country" name="country">
<input type="hidden" id="address-lat" name="lat">
<input type="hidden" id="address-lng" name="lng">
```

### Custom Dropdown Styles (Token-Based)

```css
/* Suggestions dropdown — custom styled, no native white box */
.address-suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  margin-top: var(--space-1);
  max-height: 280px;
  overflow-y: auto;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  z-index: 100;
  list-style: none;
  padding: var(--space-2);
}

.address-suggestions[hidden] {
  display: none;
}

.address-suggestion {
  display: flex;
  align-items: flex-start;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: background var(--transition-micro);
}

.address-suggestion:hover,
.address-suggestion:focus,
.address-suggestion[aria-selected="true"] {
  background: var(--surface-up);
}

.address-suggestion__icon {
  flex-shrink: 0;
  width: var(--space-5);
  height: var(--space-5);
  color: var(--muted);
}

.address-suggestion__text {
  flex: 1;
  min-width: 0;
}

.address-suggestion__main {
  font-size: var(--text-sm);
  font-weight: 500;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.address-suggestion__secondary {
  font-size: var(--text-xs);
  color: var(--muted);
  margin-top: 2px;
}

/* Match highlight in search results */
.address-suggestion__main mark {
  background: color-mix(in srgb, var(--accent) 20%, transparent);
  color: var(--accent);
  font-weight: 600;
  border-radius: 2px;
  padding: 0 2px;
}

/* Loading state */
.address-suggestions--loading::after {
  content: '';
  display: block;
  padding: var(--space-4);
  text-align: center;
  color: var(--muted);
}

/* Empty state */
.address-suggestions--empty {
  padding: var(--space-6);
  text-align: center;
  color: var(--muted);
  font-size: var(--text-sm);
}
```

### JavaScript Implementation (Mapbox Example)

```javascript
// Address Autocomplete with Custom Dropdown
// Uses Mapbox Geocoding API — replace with Google Places if preferred

class AddressAutocomplete {
  constructor(inputId, options = {}) {
    this.input = document.getElementById(inputId);
    this.field = this.input.closest('.address-field');
    this.suggestions = this.field.querySelector('.address-suggestions');
    this.debounceMs = options.debounce || 300;
    this.minChars = options.minChars || 3;
    this.accessToken = options.accessToken; // Mapbox API token
    this.country = options.country || ''; // e.g., 'us' to limit results
    this.selectedIndex = -1;
    
    this.init();
  }

  init() {
    this.input.addEventListener('input', this.debounce(this.onInput.bind(this), this.debounceMs));
    this.input.addEventListener('keydown', this.onKeydown.bind(this));
    this.input.addEventListener('blur', () => setTimeout(() => this.hideSuggestions(), 150));
    this.input.addEventListener('focus', () => {
      if (this.suggestions.children.length > 0) {
        this.showSuggestions();
      }
    });
  }

  async onInput(e) {
    const query = e.target.value.trim();
    
    if (query.length < this.minChars) {
      this.hideSuggestions();
      return;
    }

    try {
      const results = await this.fetchSuggestions(query);
      this.renderSuggestions(results, query);
    } catch (err) {
      console.error('Address lookup failed:', err);
      this.showError();
    }
  }

  async fetchSuggestions(query) {
    // Mapbox Geocoding API
    const params = new URLSearchParams({
      access_token: this.accessToken,
      autocomplete: true,
      types: 'address',
      limit: 5,
      ...(this.country && { country: this.country }),
    });

    const res = await fetch(
      `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?${params}`
    );

    if (!res.ok) throw new Error('Geocoding failed');
    const data = await res.json();
    return data.features || [];
  }

  renderSuggestions(results, query) {
    this.suggestions.innerHTML = '';
    this.selectedIndex = -1;

    if (results.length === 0) {
      this.suggestions.innerHTML = `<li class="address-suggestions--empty">No addresses found</li>`;
      this.showSuggestions();
      return;
    }

    results.forEach((result, index) => {
      const li = document.createElement('li');
      li.className = 'address-suggestion';
      li.setAttribute('role', 'option');
      li.setAttribute('data-index', index);
      li.setAttribute('aria-selected', 'false');

      // Highlight matching text
      const mainText = this.highlightMatch(result.text || result.place_name.split(',')[0], query);
      const secondaryText = result.place_name.replace(result.text + ', ', '');

      li.innerHTML = `
        <i data-lucide="map-pin" class="address-suggestion__icon"></i>
        <div class="address-suggestion__text">
          <div class="address-suggestion__main">${mainText}</div>
          <div class="address-suggestion__secondary">${secondaryText}</div>
        </div>
      `;

      li.addEventListener('click', () => this.selectSuggestion(result));
      this.suggestions.appendChild(li);
    });

    // Reinitialize Lucide icons in the new elements
    if (window.lucide) lucide.createIcons();
    this.showSuggestions();
  }

  highlightMatch(text, query) {
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark>$1</mark>');
  }

  selectSuggestion(result) {
    // Fill the visible input
    this.input.value = result.place_name;

    // Parse and fill hidden fields
    const context = result.context || [];
    const getContext = (type) => context.find(c => c.id.startsWith(type))?.text || '';

    document.getElementById('address-street').value = result.address 
      ? `${result.address} ${result.text}` 
      : result.text;
    document.getElementById('address-city').value = getContext('place');
    document.getElementById('address-state').value = getContext('region');
    document.getElementById('address-zip').value = getContext('postcode');
    document.getElementById('address-country').value = getContext('country');
    document.getElementById('address-lat').value = result.center?.[1] || '';
    document.getElementById('address-lng').value = result.center?.[0] || '';

    this.hideSuggestions();
    
    // Dispatch event for form validation
    this.input.dispatchEvent(new Event('change', { bubbles: true }));
  }

  onKeydown(e) {
    const items = this.suggestions.querySelectorAll('.address-suggestion');
    if (items.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
      this.updateSelection(items);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
      this.updateSelection(items);
    } else if (e.key === 'Enter' && this.selectedIndex >= 0) {
      e.preventDefault();
      items[this.selectedIndex].click();
    } else if (e.key === 'Escape') {
      this.hideSuggestions();
    }
  }

  updateSelection(items) {
    items.forEach((item, i) => {
      item.setAttribute('aria-selected', i === this.selectedIndex ? 'true' : 'false');
    });
    items[this.selectedIndex]?.scrollIntoView({ block: 'nearest' });
  }

  showSuggestions() {
    this.suggestions.hidden = false;
    this.input.setAttribute('aria-expanded', 'true');
  }

  hideSuggestions() {
    this.suggestions.hidden = true;
    this.input.setAttribute('aria-expanded', 'false');
  }

  showError() {
    this.suggestions.innerHTML = `<li class="address-suggestions--empty">Unable to fetch addresses. Please try again.</li>`;
    this.showSuggestions();
  }

  debounce(fn, delay) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), delay);
    };
  }
}

// Initialize
// Note: Get your Mapbox token from https://account.mapbox.com/
new AddressAutocomplete('address-input', {
  accessToken: 'YOUR_MAPBOX_ACCESS_TOKEN', // Use env variable in production
  country: 'us', // Limit to US addresses, or remove for global
});
```

### Google Places Alternative

```javascript
// For Google Places API, replace fetchSuggestions with:
async fetchSuggestions(query) {
  // Requires Google Maps JavaScript API with Places library
  const service = new google.maps.places.AutocompleteService();
  
  return new Promise((resolve, reject) => {
    service.getPlacePredictions(
      { input: query, types: ['address'], componentRestrictions: { country: this.country } },
      (predictions, status) => {
        if (status === google.maps.places.PlacesServiceStatus.OK) {
          resolve(predictions.map(p => ({
            text: p.structured_formatting.main_text,
            place_name: p.description,
            place_id: p.place_id,
          })));
        } else {
          reject(new Error(status));
        }
      }
    );
  });
}
```

---

## PLACEHOLDER STYLING & INPUT ANIMATIONS

**Rule**: Placeholders should be subtle and transition smoothly. Input feedback must use GPU-accelerated properties only.

### Placeholder Transitions

```css
/* Placeholder styling with smooth transitions */
.field__input::placeholder {
  color: var(--muted);
  opacity: 0.7;
  transition: opacity var(--duration-fast) ease, transform var(--duration-fast) ease;
}

/* Fade placeholder on focus */
.field__input:focus::placeholder {
  opacity: 0.4;
}

/* Slide placeholder left on focus (subtle) */
.field__input:focus::placeholder {
  opacity: 0.5;
  transform: translateX(4px);
}

/* For floating label inputs, fully hide placeholder on focus */
.field--floating .field__input:focus::placeholder {
  opacity: 0;
}
```

### Input Success/Failure Shake Animation

```css
/* Shake animation — GPU-only using transform: translateX */
@keyframes shake-error {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
  20%, 40%, 60%, 80% { transform: translateX(4px); }
}

@keyframes shake-success {
  0%, 100% { transform: translateX(0) scale(1); }
  50% { transform: translateX(0) scale(1.02); }
}

/* Error shake */
.field__input--shake-error {
  animation: shake-error 0.4s var(--ease-out);
}

/* Subtle success pulse */
.field__input--shake-success {
  animation: shake-success 0.3s var(--ease-out);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
  .field__input--shake-error,
  .field__input--shake-success {
    animation: none;
  }
}
```

### JavaScript Shake Trigger

```javascript
// Trigger shake animation on validation
function shakeInput(input, type = 'error') {
  const className = type === 'success' ? 'field__input--shake-success' : 'field__input--shake-error';
  
  // Remove any existing animation
  input.classList.remove('field__input--shake-error', 'field__input--shake-success');
  
  // Force reflow to restart animation
  void input.offsetWidth;
  
  // Add animation class
  input.classList.add(className);
  
  // Remove after animation completes
  input.addEventListener('animationend', () => {
    input.classList.remove(className);
  }, { once: true });
}

// Usage in validation
function validateField(input) {
  const isValid = input.checkValidity();
  
  if (!isValid) {
    shakeInput(input, 'error');
    // Also update visual state
    input.closest('.field')?.setAttribute('data-state', 'error');
  } else {
    shakeInput(input, 'success');
    input.closest('.field')?.setAttribute('data-state', 'success');
  }
  
  return isValid;
}
```

### Input Focus Ring Animation

```css
/* Animated focus ring that expands */
.field__input {
  position: relative;
  transition: 
    border-color var(--transition-micro),
    box-shadow var(--transition-micro);
}

.field__input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 15%, transparent);
}

/* Enhanced focus with animated ring */
.field__input:focus-visible {
  outline: none;
  box-shadow: 
    0 0 0 3px color-mix(in srgb, var(--accent) 20%, transparent),
    inset 0 1px 2px rgba(0, 0, 0, 0.05);
}
```

---

## PASSWORD VISIBILITY TOGGLE

**Rule**: Password fields must have a visibility toggle. Use proper ARIA attributes and token-based positioning.

### HTML Structure

```html
<div class="field field--password">
  <label class="field__label" for="password">Password</label>
  <div class="field__wrap">
    <input 
      class="field__input" 
      type="password" 
      id="password" 
      name="password"
      placeholder=" "
      autocomplete="current-password"
      minlength="8"
      required
    >
    <button 
      type="button" 
      class="field__toggle-password" 
      aria-label="Show password"
      aria-pressed="false"
      tabindex="-1"
    >
      <i data-lucide="eye" class="icon-show"></i>
      <i data-lucide="eye-off" class="icon-hide" hidden></i>
    </button>
  </div>
  <span class="field__helper">Minimum 8 characters</span>
  <span class="field__error" role="alert"></span>
</div>
```

### CSS Styling (Token-Based)

```css
/* Password field wrapper adjustments */
.field--password .field__wrap {
  position: relative;
}

/* Extra padding for toggle button */
.field--password .field__input {
  padding-right: calc(var(--space-3) + var(--space-8) + var(--space-3));
}

/* Toggle button - absolutely positioned */
.field__toggle-password {
  position: absolute;
  right: var(--space-3);
  top: 50%;
  transform: translateY(-50%);
  
  display: flex;
  align-items: center;
  justify-content: center;
  
  width: var(--space-8);
  height: var(--space-8);
  padding: 0;
  
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  
  color: var(--muted);
  cursor: pointer;
  
  transition: color var(--transition-micro), background var(--transition-micro);
}

.field__toggle-password:hover {
  color: var(--text);
  background: var(--surface-up);
}

.field__toggle-password:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Icon sizing */
.field__toggle-password svg {
  width: var(--space-5);
  height: var(--space-5);
}

/* Icon visibility states */
.field__toggle-password[aria-pressed="true"] .icon-show {
  display: none;
}

.field__toggle-password[aria-pressed="true"] .icon-hide {
  display: block !important;
}

.field__toggle-password[aria-pressed="false"] .icon-hide {
  display: none;
}
```

### JavaScript Toggle Logic

```javascript
// Password visibility toggle
class PasswordToggle {
  constructor(fieldSelector = '.field--password') {
    document.querySelectorAll(fieldSelector).forEach(field => {
      this.initField(field);
    });
  }

  initField(field) {
    const input = field.querySelector('.field__input');
    const toggle = field.querySelector('.field__toggle-password');
    
    if (!input || !toggle) return;

    toggle.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      
      // Toggle input type
      input.type = isPassword ? 'text' : 'password';
      
      // Update button state
      toggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
      toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      
      // Keep focus on input for usability
      input.focus();
    });
  }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  new PasswordToggle();
});
```

### Password Toggle with Strength Meter Integration

```html
<!-- Full password field with toggle + strength meter -->
<div class="field field--password">
  <label class="field__label" for="new-password">Create Password</label>
  <div class="field__wrap">
    <input 
      class="field__input" 
      type="password" 
      id="new-password" 
      name="password"
      placeholder=" "
      autocomplete="new-password"
      minlength="8"
      required
    >
    <button 
      type="button" 
      class="field__toggle-password" 
      aria-label="Show password"
      aria-pressed="false"
    >
      <i data-lucide="eye" class="icon-show"></i>
      <i data-lucide="eye-off" class="icon-hide" hidden></i>
    </button>
  </div>
  
  <!-- Strength meter (from existing password meter section) -->
  <div class="password-meter" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="4">
    <div class="password-meter__bar" data-strength="0"></div>
    <span class="password-meter__label" aria-live="polite"></span>
  </div>
  
  <span class="field__helper">Use 8+ characters with letters, numbers, and symbols</span>
  <span class="field__error" role="alert"></span>
</div>
```
