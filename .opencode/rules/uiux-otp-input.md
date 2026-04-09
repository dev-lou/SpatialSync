---
description: 6-digit OTP/verification code input. Individual boxes, auto-focus, paste support, backspace navigation. Token-aware styling with countdown timer.
---

# UI/UX OTP Input Component

> 6-digit verification code input with auto-focus and paste support.
> Accessible, token-aware, with resend countdown timer.

---

## OVERVIEW

This skill covers:
1. Individual digit boxes
2. Auto-focus on next input
3. Paste support (full code)
4. Backspace navigation
5. Countdown resend timer
6. Accessibility (ARIA labels)

---

## HTML STRUCTURE

```html
<div class="otp-input" role="group" aria-labelledby="otp-label">
  <label id="otp-label" class="otp-input__label">
    Enter the 6-digit code sent to your email
  </label>
  
  <div class="otp-input__boxes">
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 1 of 6"
      autocomplete="one-time-code"
    >
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 2 of 6"
    >
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 3 of 6"
    >
    <span class="otp-input__separator">–</span>
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 4 of 6"
    >
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 5 of 6"
    >
    <input 
      type="text" 
      inputmode="numeric" 
      pattern="[0-9]" 
      maxlength="1"
      class="otp-input__box"
      aria-label="Digit 6 of 6"
    >
  </div>
  
  <div class="otp-input__footer">
    <p class="otp-input__help">
      Didn't receive the code?
    </p>
    <button 
      type="button" 
      class="otp-input__resend" 
      id="resend-btn"
      disabled
    >
      Resend code (<span id="countdown">60</span>s)
    </button>
  </div>
  
  <!-- Hidden input for form submission -->
  <input type="hidden" name="otp" id="otp-value">
  
  <!-- Screen reader announcements -->
  <div id="otp-status" role="status" aria-live="polite" class="sr-only"></div>
</div>
```

---

## CSS STYLES

```css
.otp-input {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-6);
}

.otp-input__label {
  font-size: var(--text-sm);
  color: var(--text-secondary);
  text-align: center;
}

.otp-input__boxes {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.otp-input__box {
  width: 48px;
  height: 56px;
  text-align: center;
  font-size: var(--text-2xl);
  font-weight: 600;
  font-family: var(--font-mono, monospace);
  color: var(--text);
  background: var(--surface);
  border: 2px solid var(--border);
  border-radius: var(--radius-md);
  transition: border-color var(--dur-micro), box-shadow var(--dur-micro);
  
  /* Prevent zoom on iOS */
  font-size: 16px;
}

@media (min-width: 640px) {
  .otp-input__box {
    width: 56px;
    height: 64px;
    font-size: var(--text-3xl);
  }
}

.otp-input__box:focus {
  outline: none;
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-soft);
}

.otp-input__box:focus-visible {
  border-color: var(--accent);
}

/* Filled state */
.otp-input__box--filled {
  border-color: var(--accent);
  background: var(--accent-soft);
}

/* Error state */
.otp-input--error .otp-input__box {
  border-color: var(--color-error);
  animation: shake 0.4s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-4px); }
  40%, 80% { transform: translateX(4px); }
}

@media (prefers-reduced-motion: reduce) {
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
  }
}

/* Success state */
.otp-input--success .otp-input__box {
  border-color: var(--color-success);
  background: var(--color-success-soft);
}

.otp-input__separator {
  color: var(--muted);
  font-size: var(--text-xl);
  padding: 0 var(--space-1);
}

.otp-input__footer {
  text-align: center;
}

.otp-input__help {
  font-size: var(--text-sm);
  color: var(--muted);
  margin-bottom: var(--space-2);
}

.otp-input__resend {
  background: none;
  border: none;
  color: var(--accent);
  font-size: var(--text-sm);
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
}

.otp-input__resend:disabled {
  color: var(--muted);
  cursor: not-allowed;
  text-decoration: none;
}

.otp-input__resend:not(:disabled):hover {
  text-decoration: none;
}
```

---

## JAVASCRIPT IMPLEMENTATION

```typescript
interface OTPInputOptions {
  length?: number;
  onComplete?: (code: string) => void;
  onResend?: () => void;
  resendCooldown?: number;
}

function initOTPInput(container: HTMLElement, options: OTPInputOptions = {}): void {
  const {
    length = 6,
    onComplete,
    onResend,
    resendCooldown = 60
  } = options;
  
  const inputs = container.querySelectorAll<HTMLInputElement>('.otp-input__box');
  const hiddenInput = container.querySelector<HTMLInputElement>('#otp-value');
  const resendBtn = container.querySelector<HTMLButtonElement>('#resend-btn');
  const countdownEl = container.querySelector<HTMLSpanElement>('#countdown');
  const statusEl = container.querySelector<HTMLElement>('#otp-status');
  
  // Focus first input on init
  inputs[0]?.focus();
  
  // Handle input
  inputs.forEach((input, index) => {
    // Input event
    input.addEventListener('input', (e) => {
      const target = e.target as HTMLInputElement;
      let value = target.value;
      
      // Only allow digits
      value = value.replace(/\D/g, '');
      target.value = value;
      
      if (value.length === 1) {
        target.classList.add('otp-input__box--filled');
        
        // Move to next input
        if (index < inputs.length - 1) {
          inputs[index + 1].focus();
        }
      }
      
      updateHiddenInput();
      checkComplete();
    });
    
    // Keydown event
    input.addEventListener('keydown', (e) => {
      const target = e.target as HTMLInputElement;
      
      // Backspace - move to previous
      if (e.key === 'Backspace') {
        if (target.value === '' && index > 0) {
          inputs[index - 1].focus();
          inputs[index - 1].value = '';
          inputs[index - 1].classList.remove('otp-input__box--filled');
        } else {
          target.classList.remove('otp-input__box--filled');
        }
        updateHiddenInput();
      }
      
      // Arrow keys
      if (e.key === 'ArrowLeft' && index > 0) {
        e.preventDefault();
        inputs[index - 1].focus();
      }
      
      if (e.key === 'ArrowRight' && index < inputs.length - 1) {
        e.preventDefault();
        inputs[index + 1].focus();
      }
    });
    
    // Paste event (on first input catches full paste)
    input.addEventListener('paste', (e) => {
      e.preventDefault();
      const pastedData = e.clipboardData?.getData('text') || '';
      const digits = pastedData.replace(/\D/g, '').slice(0, length);
      
      if (digits.length > 0) {
        // Distribute digits across inputs
        digits.split('').forEach((digit, i) => {
          if (inputs[i]) {
            inputs[i].value = digit;
            inputs[i].classList.add('otp-input__box--filled');
          }
        });
        
        // Focus appropriate input
        const nextIndex = Math.min(digits.length, inputs.length - 1);
        inputs[nextIndex].focus();
        
        updateHiddenInput();
        checkComplete();
        
        // Announce to screen reader
        announce(`Code pasted: ${digits.length} digits entered`);
      }
    });
    
    // Focus event - select content
    input.addEventListener('focus', () => {
      input.select();
    });
  });
  
  // Update hidden input value
  function updateHiddenInput() {
    if (!hiddenInput) return;
    const code = Array.from(inputs).map(i => i.value).join('');
    hiddenInput.value = code;
  }
  
  // Check if complete
  function checkComplete() {
    const code = Array.from(inputs).map(i => i.value).join('');
    
    if (code.length === length) {
      onComplete?.(code);
      announce('Verification code complete');
    }
  }
  
  // Announce to screen reader
  function announce(message: string) {
    if (statusEl) {
      statusEl.textContent = message;
      setTimeout(() => {
        statusEl.textContent = '';
      }, 1000);
    }
  }
  
  // Resend countdown
  let countdown = resendCooldown;
  let countdownInterval: NodeJS.Timeout | null = null;
  
  function startCountdown() {
    countdown = resendCooldown;
    if (resendBtn) resendBtn.disabled = true;
    if (countdownEl) countdownEl.textContent = String(countdown);
    
    countdownInterval = setInterval(() => {
      countdown--;
      if (countdownEl) countdownEl.textContent = String(countdown);
      
      if (countdown <= 0) {
        if (countdownInterval) clearInterval(countdownInterval);
        if (resendBtn) {
          resendBtn.disabled = false;
          resendBtn.textContent = 'Resend code';
        }
      }
    }, 1000);
  }
  
  // Start initial countdown
  startCountdown();
  
  // Resend button click
  resendBtn?.addEventListener('click', () => {
    onResend?.();
    startCountdown();
    
    // Reset inputs
    inputs.forEach(input => {
      input.value = '';
      input.classList.remove('otp-input__box--filled');
    });
    inputs[0].focus();
    
    announce('Code resent. Check your email.');
  });
  
  // Public methods
  return {
    reset: () => {
      inputs.forEach(input => {
        input.value = '';
        input.classList.remove('otp-input__box--filled');
      });
      container.classList.remove('otp-input--error', 'otp-input--success');
      inputs[0].focus();
    },
    setError: () => {
      container.classList.add('otp-input--error');
      container.classList.remove('otp-input--success');
      announce('Invalid code. Please try again.');
    },
    setSuccess: () => {
      container.classList.add('otp-input--success');
      container.classList.remove('otp-input--error');
      announce('Code verified successfully.');
    },
    getValue: () => {
      return Array.from(inputs).map(i => i.value).join('');
    }
  };
}

// Initialize
const otpContainer = document.querySelector('.otp-input');
if (otpContainer) {
  const otp = initOTPInput(otpContainer, {
    onComplete: (code) => {
      console.log('OTP entered:', code);
      // Submit to server
      verifyOTP(code);
    },
    onResend: () => {
      console.log('Resend requested');
      // Call resend API
    }
  });
}
```

---

## REACT COMPONENT

```tsx
import { useState, useRef, useEffect, useCallback, KeyboardEvent, ClipboardEvent } from 'react';

interface OTPInputProps {
  length?: number;
  onComplete?: (code: string) => void;
  onResend?: () => void;
  resendCooldown?: number;
  error?: boolean;
  autoFocus?: boolean;
}

export function OTPInput({
  length = 6,
  onComplete,
  onResend,
  resendCooldown = 60,
  error = false,
  autoFocus = true
}: OTPInputProps) {
  const [values, setValues] = useState<string[]>(Array(length).fill(''));
  const [countdown, setCountdown] = useState(resendCooldown);
  const [canResend, setCanResend] = useState(false);
  const inputRefs = useRef<(HTMLInputElement | null)[]>([]);
  const statusRef = useRef<HTMLDivElement>(null);
  
  // Countdown timer
  useEffect(() => {
    if (countdown > 0) {
      const timer = setTimeout(() => setCountdown(c => c - 1), 1000);
      return () => clearTimeout(timer);
    } else {
      setCanResend(true);
    }
  }, [countdown]);
  
  // Auto focus
  useEffect(() => {
    if (autoFocus) {
      inputRefs.current[0]?.focus();
    }
  }, [autoFocus]);
  
  // Check complete
  useEffect(() => {
    const code = values.join('');
    if (code.length === length && !values.includes('')) {
      onComplete?.(code);
      announce('Verification code complete');
    }
  }, [values, length, onComplete]);
  
  const announce = useCallback((message: string) => {
    if (statusRef.current) {
      statusRef.current.textContent = message;
      setTimeout(() => {
        if (statusRef.current) statusRef.current.textContent = '';
      }, 1000);
    }
  }, []);
  
  const handleChange = (index: number, value: string) => {
    // Only digits
    const digit = value.replace(/\D/g, '').slice(-1);
    
    const newValues = [...values];
    newValues[index] = digit;
    setValues(newValues);
    
    // Move to next
    if (digit && index < length - 1) {
      inputRefs.current[index + 1]?.focus();
    }
  };
  
  const handleKeyDown = (index: number, e: KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Backspace') {
      if (!values[index] && index > 0) {
        const newValues = [...values];
        newValues[index - 1] = '';
        setValues(newValues);
        inputRefs.current[index - 1]?.focus();
      }
    }
    
    if (e.key === 'ArrowLeft' && index > 0) {
      e.preventDefault();
      inputRefs.current[index - 1]?.focus();
    }
    
    if (e.key === 'ArrowRight' && index < length - 1) {
      e.preventDefault();
      inputRefs.current[index + 1]?.focus();
    }
  };
  
  const handlePaste = (e: ClipboardEvent<HTMLInputElement>) => {
    e.preventDefault();
    const pastedData = e.clipboardData.getData('text');
    const digits = pastedData.replace(/\D/g, '').slice(0, length);
    
    if (digits.length > 0) {
      const newValues = [...values];
      digits.split('').forEach((digit, i) => {
        newValues[i] = digit;
      });
      setValues(newValues);
      
      const nextIndex = Math.min(digits.length, length - 1);
      inputRefs.current[nextIndex]?.focus();
      
      announce(`Code pasted: ${digits.length} digits entered`);
    }
  };
  
  const handleResend = () => {
    onResend?.();
    setCountdown(resendCooldown);
    setCanResend(false);
    setValues(Array(length).fill(''));
    inputRefs.current[0]?.focus();
    announce('Code resent. Check your email.');
  };
  
  return (
    <div className={`otp-input ${error ? 'otp-input--error' : ''}`} role="group" aria-labelledby="otp-label">
      <label id="otp-label" className="otp-input__label">
        Enter the {length}-digit code sent to your email
      </label>
      
      <div className="otp-input__boxes">
        {values.map((value, index) => (
          <React.Fragment key={index}>
            {index === Math.floor(length / 2) && (
              <span className="otp-input__separator">–</span>
            )}
            <input
              ref={(el) => (inputRefs.current[index] = el)}
              type="text"
              inputMode="numeric"
              pattern="[0-9]"
              maxLength={1}
              value={value}
              onChange={(e) => handleChange(index, e.target.value)}
              onKeyDown={(e) => handleKeyDown(index, e)}
              onPaste={handlePaste}
              onFocus={(e) => e.target.select()}
              className={`otp-input__box ${value ? 'otp-input__box--filled' : ''}`}
              aria-label={`Digit ${index + 1} of ${length}`}
              autoComplete={index === 0 ? 'one-time-code' : 'off'}
            />
          </React.Fragment>
        ))}
      </div>
      
      <div className="otp-input__footer">
        <p className="otp-input__help">Didn't receive the code?</p>
        <button
          type="button"
          className="otp-input__resend"
          onClick={handleResend}
          disabled={!canResend}
        >
          {canResend ? 'Resend code' : `Resend code (${countdown}s)`}
        </button>
      </div>
      
      <div ref={statusRef} role="status" aria-live="polite" className="sr-only" />
    </div>
  );
}
```

### Usage

```tsx
function VerificationPage() {
  const [error, setError] = useState(false);
  
  const handleComplete = async (code: string) => {
    try {
      await verifyOTP(code);
      // Success - redirect
    } catch (err) {
      setError(true);
      setTimeout(() => setError(false), 1000);
    }
  };
  
  const handleResend = async () => {
    await resendOTP();
  };
  
  return (
    <div className="verification-page">
      <h1>Verify your email</h1>
      <OTPInput
        length={6}
        onComplete={handleComplete}
        onResend={handleResend}
        error={error}
        resendCooldown={60}
      />
    </div>
  );
}
```

---

## ACCESSIBILITY FEATURES

1. **ARIA Labels**: Each input has `aria-label="Digit X of 6"`
2. **Role Group**: Container has `role="group"`
3. **Live Region**: Announcements via `aria-live="polite"`
4. **Autocomplete**: First input has `autocomplete="one-time-code"` for SMS autofill
5. **Focus Management**: Clear focus indicators
6. **Error Announcement**: Announces errors to screen readers

---

## MOBILE CONSIDERATIONS

```css
/* Prevent zoom on iOS */
.otp-input__box {
  font-size: 16px; /* iOS zooms inputs smaller than 16px */
}

/* Better touch targets */
@media (pointer: coarse) {
  .otp-input__box {
    width: 52px;
    height: 60px;
  }
}
```

```html
<!-- Numeric keyboard on mobile -->
<input 
  type="text" 
  inputmode="numeric" 
  pattern="[0-9]"
>
```

---

## CHECKLIST

- [ ] Individual input boxes (not one long input)
- [ ] Auto-focus on next input
- [ ] Paste support for full code
- [ ] Backspace moves to previous
- [ ] Arrow key navigation
- [ ] ARIA labels on each input
- [ ] `autocomplete="one-time-code"` on first input
- [ ] Screen reader announcements
- [ ] Countdown resend timer
- [ ] Error shake animation
- [ ] Success visual feedback
- [ ] Mobile numeric keyboard
- [ ] Touch targets ≥44px
- [ ] Token-aware styling
