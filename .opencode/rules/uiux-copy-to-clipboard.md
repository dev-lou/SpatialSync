---
description: Copy to clipboard using Clipboard API with execCommand fallback. Visual feedback via tooltip or icon change. Token-aware styling with accessibility announcements.
---

# UI/UX Copy to Clipboard

> Copy text to clipboard with visual feedback.
> Uses Clipboard API with graceful fallback.

---

## OVERVIEW

This skill covers:
1. Clipboard API implementation
2. execCommand fallback for older browsers
3. Visual feedback (tooltip, icon change)
4. Token-aware button styling
5. Accessibility announcements

---

## HTML STRUCTURE

### Basic Copy Button

```html
<button 
  class="copy-btn"
  data-copy="https://example.com/share/abc123"
  aria-label="Copy link to clipboard"
>
  <i data-lucide="copy" class="copy-btn__icon"></i>
  <span class="copy-btn__text">Copy Link</span>
</button>

<!-- Screen reader announcement -->
<div id="copy-status" role="status" aria-live="polite" class="sr-only"></div>
```

### Copy with Input Field

```html
<div class="copy-field">
  <input 
    type="text" 
    class="copy-field__input" 
    value="https://example.com/share/abc123"
    readonly
    aria-label="Shareable link"
  >
  <button 
    class="copy-field__btn"
    aria-label="Copy to clipboard"
  >
    <i data-lucide="copy"></i>
  </button>
</div>
```

### Code Block with Copy

```html
<div class="code-block">
  <div class="code-block__header">
    <span class="code-block__lang">JavaScript</span>
    <button class="code-block__copy" aria-label="Copy code">
      <i data-lucide="copy"></i>
      <span>Copy</span>
    </button>
  </div>
  <pre class="code-block__content"><code>const greeting = "Hello, World!";</code></pre>
</div>
```

---

## CSS STYLES

```css
/* Basic copy button */
.copy-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  color: var(--text);
  font-size: var(--text-sm);
  cursor: pointer;
  transition: background var(--dur-micro), border-color var(--dur-micro);
}

.copy-btn:hover {
  background: var(--bg-hover);
  border-color: var(--border-hover);
}

.copy-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.copy-btn__icon {
  width: 16px;
  height: 16px;
  transition: transform var(--dur-micro);
}

/* Success state */
.copy-btn--success {
  background: var(--color-success-soft);
  border-color: var(--color-success);
  color: var(--color-success);
}

.copy-btn--success .copy-btn__icon {
  transform: scale(1.1);
}

/* Copy with input field */
.copy-field {
  display: flex;
  max-width: 400px;
}

.copy-field__input {
  flex: 1;
  padding: var(--space-3);
  border: 1px solid var(--border);
  border-right: none;
  border-radius: var(--radius-md) 0 0 var(--radius-md);
  background: var(--surface);
  color: var(--text);
  font-size: var(--text-sm);
  font-family: var(--font-mono);
}

.copy-field__input:focus {
  outline: none;
  border-color: var(--accent);
}

.copy-field__btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-3) var(--space-4);
  background: var(--accent);
  border: 1px solid var(--accent);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
  color: white;
  cursor: pointer;
  transition: background var(--dur-micro);
}

.copy-field__btn:hover {
  background: var(--accent-hover);
}

.copy-field__btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.copy-field__btn svg {
  width: 18px;
  height: 18px;
}

/* Success state for field button */
.copy-field__btn--success {
  background: var(--color-success);
  border-color: var(--color-success);
}

/* Code block */
.code-block {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.code-block__header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-2) var(--space-4);
  background: var(--bg);
  border-bottom: 1px solid var(--border);
}

.code-block__lang {
  font-size: var(--text-xs);
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.code-block__copy {
  display: flex;
  align-items: center;
  gap: var(--space-1);
  padding: var(--space-1) var(--space-2);
  background: transparent;
  border: none;
  border-radius: var(--radius-sm);
  color: var(--muted);
  font-size: var(--text-xs);
  cursor: pointer;
  transition: color var(--dur-micro), background var(--dur-micro);
}

.code-block__copy:hover {
  color: var(--text);
  background: var(--bg-hover);
}

.code-block__copy svg {
  width: 14px;
  height: 14px;
}

.code-block__copy--success {
  color: var(--color-success);
}

.code-block__content {
  padding: var(--space-4);
  margin: 0;
  overflow-x: auto;
}

.code-block__content code {
  font-family: var(--font-mono);
  font-size: var(--text-sm);
  line-height: 1.6;
}

/* Tooltip feedback */
.copy-btn[data-tooltip]::after {
  content: attr(data-tooltip);
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  padding: var(--space-1) var(--space-2);
  background: var(--text);
  color: var(--bg);
  font-size: var(--text-xs);
  white-space: nowrap;
  border-radius: var(--radius-sm);
  opacity: 0;
  pointer-events: none;
  transition: opacity var(--dur-micro);
}

.copy-btn[data-tooltip].show-tooltip::after {
  opacity: 1;
}
```

---

## JAVASCRIPT IMPLEMENTATION

### Basic Copy Function

```typescript
interface CopyOptions {
  onSuccess?: () => void;
  onError?: (error: Error) => void;
  feedbackDuration?: number;
}

async function copyToClipboard(
  text: string, 
  options: CopyOptions = {}
): Promise<boolean> {
  const { onSuccess, onError, feedbackDuration = 2000 } = options;
  
  try {
    // Modern Clipboard API
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      onSuccess?.();
      return true;
    }
    
    // Fallback for older browsers
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.cssText = 'position:fixed;left:-9999px;top:-9999px;';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    
    const success = document.execCommand('copy');
    document.body.removeChild(textarea);
    
    if (success) {
      onSuccess?.();
      return true;
    } else {
      throw new Error('execCommand copy failed');
    }
    
  } catch (error) {
    onError?.(error as Error);
    return false;
  }
}
```

### Copy Button Handler

```typescript
function initCopyButtons(): void {
  const statusEl = document.getElementById('copy-status');
  
  function announce(message: string): void {
    if (statusEl) {
      statusEl.textContent = message;
      setTimeout(() => {
        statusEl.textContent = '';
      }, 1000);
    }
  }
  
  // Data-copy buttons
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', async () => {
      const text = (btn as HTMLElement).dataset.copy;
      if (!text) return;
      
      const success = await copyToClipboard(text, {
        onSuccess: () => {
          showSuccessState(btn as HTMLElement);
          announce('Copied to clipboard');
        },
        onError: (error) => {
          console.error('Copy failed:', error);
          announce('Failed to copy');
        }
      });
    });
  });
  
  // Copy field buttons
  document.querySelectorAll('.copy-field').forEach(field => {
    const input = field.querySelector('.copy-field__input') as HTMLInputElement;
    const btn = field.querySelector('.copy-field__btn') as HTMLButtonElement;
    
    btn?.addEventListener('click', async () => {
      const text = input?.value;
      if (!text) return;
      
      const success = await copyToClipboard(text, {
        onSuccess: () => {
          showSuccessState(btn);
          announce('Copied to clipboard');
        }
      });
    });
  });
  
  // Code block copy buttons
  document.querySelectorAll('.code-block').forEach(block => {
    const btn = block.querySelector('.code-block__copy') as HTMLButtonElement;
    const code = block.querySelector('code');
    
    btn?.addEventListener('click', async () => {
      const text = code?.textContent;
      if (!text) return;
      
      const success = await copyToClipboard(text, {
        onSuccess: () => {
          showCodeCopySuccess(btn);
          announce('Code copied to clipboard');
        }
      });
    });
  });
}

function showSuccessState(element: HTMLElement): void {
  const iconEl = element.querySelector('i, svg');
  const textEl = element.querySelector('.copy-btn__text');
  
  // Add success class
  element.classList.add('copy-btn--success');
  
  // Change icon to checkmark
  if (iconEl) {
    iconEl.setAttribute('data-lucide', 'check');
    if (window.lucide) {
      window.lucide.createIcons();
    }
  }
  
  // Change text
  if (textEl) {
    const originalText = textEl.textContent;
    textEl.textContent = 'Copied!';
    
    setTimeout(() => {
      textEl.textContent = originalText;
    }, 2000);
  }
  
  // Reset after delay
  setTimeout(() => {
    element.classList.remove('copy-btn--success');
    if (iconEl) {
      iconEl.setAttribute('data-lucide', 'copy');
      if (window.lucide) {
        window.lucide.createIcons();
      }
    }
  }, 2000);
}

function showCodeCopySuccess(btn: HTMLButtonElement): void {
  const iconEl = btn.querySelector('i, svg');
  const textEl = btn.querySelector('span');
  
  btn.classList.add('code-block__copy--success');
  
  if (iconEl) {
    iconEl.setAttribute('data-lucide', 'check');
    if (window.lucide) {
      window.lucide.createIcons();
    }
  }
  
  if (textEl) {
    textEl.textContent = 'Copied!';
  }
  
  setTimeout(() => {
    btn.classList.remove('code-block__copy--success');
    if (iconEl) {
      iconEl.setAttribute('data-lucide', 'copy');
      if (window.lucide) {
        window.lucide.createIcons();
      }
    }
    if (textEl) {
      textEl.textContent = 'Copy';
    }
  }, 2000);
}

// Initialize
document.addEventListener('DOMContentLoaded', initCopyButtons);
```

---

## REACT COMPONENT

```tsx
import { useState, useCallback, useRef } from 'react';
import { Copy, Check } from 'lucide-react';

interface CopyButtonProps {
  text: string;
  className?: string;
  children?: React.ReactNode;
  onCopy?: () => void;
}

export function CopyButton({ 
  text, 
  className = '', 
  children,
  onCopy 
}: CopyButtonProps) {
  const [copied, setCopied] = useState(false);
  const statusRef = useRef<HTMLDivElement>(null);
  
  const handleCopy = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(text);
      setCopied(true);
      onCopy?.();
      
      // Announce to screen readers
      if (statusRef.current) {
        statusRef.current.textContent = 'Copied to clipboard';
      }
      
      setTimeout(() => {
        setCopied(false);
        if (statusRef.current) {
          statusRef.current.textContent = '';
        }
      }, 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  }, [text, onCopy]);
  
  return (
    <>
      <button
        className={`copy-btn ${copied ? 'copy-btn--success' : ''} ${className}`}
        onClick={handleCopy}
        aria-label={copied ? 'Copied!' : 'Copy to clipboard'}
      >
        {copied ? (
          <Check className="copy-btn__icon" size={16} />
        ) : (
          <Copy className="copy-btn__icon" size={16} />
        )}
        <span className="copy-btn__text">
          {children || (copied ? 'Copied!' : 'Copy')}
        </span>
      </button>
      
      <div ref={statusRef} role="status" aria-live="polite" className="sr-only" />
    </>
  );
}
```

### Copy Field Component

```tsx
interface CopyFieldProps {
  value: string;
  label?: string;
  onCopy?: () => void;
}

export function CopyField({ value, label, onCopy }: CopyFieldProps) {
  const [copied, setCopied] = useState(false);
  const inputRef = useRef<HTMLInputElement>(null);
  
  const handleCopy = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(value);
      setCopied(true);
      onCopy?.();
      
      // Select the input text
      inputRef.current?.select();
      
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  }, [value, onCopy]);
  
  return (
    <div className="copy-field">
      <input
        ref={inputRef}
        type="text"
        className="copy-field__input"
        value={value}
        readOnly
        aria-label={label || 'Copyable text'}
        onFocus={(e) => e.target.select()}
      />
      <button
        className={`copy-field__btn ${copied ? 'copy-field__btn--success' : ''}`}
        onClick={handleCopy}
        aria-label={copied ? 'Copied!' : 'Copy to clipboard'}
      >
        {copied ? <Check size={18} /> : <Copy size={18} />}
      </button>
    </div>
  );
}
```

### Code Block Component

```tsx
interface CodeBlockProps {
  code: string;
  language?: string;
}

export function CodeBlock({ code, language = 'plaintext' }: CodeBlockProps) {
  const [copied, setCopied] = useState(false);
  
  const handleCopy = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(code);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  }, [code]);
  
  return (
    <div className="code-block">
      <div className="code-block__header">
        <span className="code-block__lang">{language}</span>
        <button
          className={`code-block__copy ${copied ? 'code-block__copy--success' : ''}`}
          onClick={handleCopy}
          aria-label={copied ? 'Copied!' : 'Copy code'}
        >
          {copied ? <Check size={14} /> : <Copy size={14} />}
          <span>{copied ? 'Copied!' : 'Copy'}</span>
        </button>
      </div>
      <pre className="code-block__content">
        <code>{code}</code>
      </pre>
    </div>
  );
}
```

---

## USAGE EXAMPLES

```tsx
// Basic copy button
<CopyButton text="npm install my-package">
  Copy Command
</CopyButton>

// Copy field with shareable link
<CopyField
  value={`https://myapp.com/invite/${inviteCode}`}
  label="Invite link"
  onCopy={() => track('invite_link_copied')}
/>

// Code block
<CodeBlock
  language="bash"
  code={`npm install @my-org/package
npm run setup`}
/>
```

---

## ACCESSIBILITY

1. **ARIA Labels**: Clear labels describing the action
2. **Status Announcements**: `aria-live="polite"` for feedback
3. **Focus Indicators**: Visible focus styles
4. **Keyboard Accessible**: Works with Enter/Space

```html
<button aria-label="Copy to clipboard">
  <i data-lucide="copy" aria-hidden="true"></i>
</button>

<div role="status" aria-live="polite" class="sr-only">
  Copied to clipboard
</div>
```

---

## BROWSER SUPPORT

| Browser | Clipboard API | execCommand Fallback |
|---------|--------------|---------------------|
| Chrome 66+ | ✅ | ✅ |
| Firefox 63+ | ✅ | ✅ |
| Safari 13.1+ | ✅ | ✅ |
| Edge 79+ | ✅ | ✅ |
| IE 11 | ❌ | ✅ |

---

## CHECKLIST

- [ ] Clipboard API with fallback
- [ ] Visual feedback (icon change)
- [ ] Success state styling
- [ ] Screen reader announcement
- [ ] Focus indicators
- [ ] Token-aware colors
- [ ] Works in secure contexts
- [ ] Handles errors gracefully
- [ ] Reset after timeout
- [ ] Touch targets ≥44px
