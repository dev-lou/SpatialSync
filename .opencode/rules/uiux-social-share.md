---
description: Social share buttons using Web Share API with fallback popups. Supports Twitter, LinkedIn, Facebook, WhatsApp, Email. Token-aware styling, no third-party libraries.
---

# UI/UX Social Share Buttons

> Share content using native Web Share API with popup fallbacks.
> No third-party libraries, token-aware styling.

---

## OVERVIEW

This skill covers:
1. Native Web Share API (mobile)
2. Platform-specific popup fallbacks
3. Share button component
4. Copy link option
5. Token-aware styling
6. Analytics tracking

---

## HTML STRUCTURE

```html
<div class="share-buttons">
  <span class="share-buttons__label">Share:</span>
  
  <!-- Native share (mobile) -->
  <button 
    class="share-btn share-btn--native" 
    id="native-share"
    aria-label="Share this page"
    hidden
  >
    <i data-lucide="share"></i>
    <span>Share</span>
  </button>
  
  <!-- Individual platforms (desktop fallback) -->
  <div class="share-buttons__platforms" id="share-platforms">
    <button 
      class="share-btn share-btn--twitter"
      data-platform="twitter"
      aria-label="Share on Twitter"
    >
      <i data-lucide="twitter"></i>
    </button>
    
    <button 
      class="share-btn share-btn--linkedin"
      data-platform="linkedin"
      aria-label="Share on LinkedIn"
    >
      <i data-lucide="linkedin"></i>
    </button>
    
    <button 
      class="share-btn share-btn--facebook"
      data-platform="facebook"
      aria-label="Share on Facebook"
    >
      <i data-lucide="facebook"></i>
    </button>
    
    <button 
      class="share-btn share-btn--whatsapp"
      data-platform="whatsapp"
      aria-label="Share on WhatsApp"
    >
      <i data-lucide="message-circle"></i>
    </button>
    
    <button 
      class="share-btn share-btn--email"
      data-platform="email"
      aria-label="Share via Email"
    >
      <i data-lucide="mail"></i>
    </button>
    
    <button 
      class="share-btn share-btn--copy"
      data-platform="copy"
      aria-label="Copy link"
    >
      <i data-lucide="link"></i>
    </button>
  </div>
</div>
```

---

## CSS STYLES

```css
.share-buttons {
  display: flex;
  align-items: center;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.share-buttons__label {
  font-size: var(--text-sm);
  color: var(--muted);
  font-weight: 500;
}

.share-buttons__platforms {
  display: flex;
  gap: var(--space-2);
}

.share-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: transform var(--dur-micro), background var(--dur-micro);
  color: white;
}

.share-btn:hover {
  transform: translateY(-2px);
}

.share-btn:active {
  transform: translateY(0);
}

.share-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Native share button */
.share-btn--native {
  width: auto;
  padding: var(--space-2) var(--space-4);
  gap: var(--space-2);
  background: var(--accent);
}

.share-btn--native:hover {
  background: var(--accent-hover);
}

/* Platform colors — using brand tokens from uiux-tokens.md (v10.1) */
.share-btn--twitter {
  background: var(--brand-twitter);
}

.share-btn--twitter:hover {
  background: var(--brand-twitter-hover);
}

.share-btn--linkedin {
  background: var(--brand-linkedin);
}

.share-btn--linkedin:hover {
  background: var(--brand-linkedin-hover);
}

.share-btn--facebook {
  background: var(--brand-facebook);
}

.share-btn--facebook:hover {
  background: var(--brand-facebook-hover);
}

.share-btn--whatsapp {
  background: var(--brand-whatsapp);
}

.share-btn--whatsapp:hover {
  background: var(--brand-whatsapp-hover);
}

.share-btn--email {
  background: var(--muted);
}

.share-btn--email:hover {
  background: var(--text);
}

.share-btn--copy {
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
}

.share-btn--copy:hover {
  background: var(--bg-hover);
}

.share-btn--copy.copied {
  background: var(--color-success);
  color: white;
  border-color: var(--color-success);
}

/* Icon sizing */
.share-btn svg,
.share-btn i {
  width: 18px;
  height: 18px;
}

/* Tooltip */
.share-btn[data-tooltip] {
  position: relative;
}

.share-btn[data-tooltip]::after {
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

.share-btn[data-tooltip]:hover::after {
  opacity: 1;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .share-btn:hover {
    transform: none;
  }
}
```

---

## JAVASCRIPT IMPLEMENTATION

```typescript
interface ShareData {
  title: string;
  text?: string;
  url: string;
}

interface ShareButtonsOptions {
  container: HTMLElement;
  shareData: ShareData;
  onShare?: (platform: string) => void;
}

function initShareButtons(options: ShareButtonsOptions): void {
  const { container, shareData, onShare } = options;
  
  const nativeShareBtn = container.querySelector('#native-share') as HTMLButtonElement;
  const platformsContainer = container.querySelector('#share-platforms') as HTMLElement;
  const platformBtns = container.querySelectorAll('[data-platform]');
  
  // Check for Web Share API support
  const canShare = 'share' in navigator && 'canShare' in navigator;
  
  if (canShare) {
    // Show native share button on mobile
    if (window.matchMedia('(max-width: 768px)').matches) {
      nativeShareBtn.hidden = false;
      platformsContainer.hidden = true;
    }
  }
  
  // Native share handler
  nativeShareBtn?.addEventListener('click', async () => {
    try {
      await navigator.share(shareData);
      onShare?.('native');
    } catch (err) {
      if ((err as Error).name !== 'AbortError') {
        console.error('Share failed:', err);
      }
    }
  });
  
  // Platform share handlers
  platformBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const platform = (btn as HTMLElement).dataset.platform;
      if (!platform) return;
      
      handlePlatformShare(platform, shareData);
      onShare?.(platform);
    });
  });
}

function handlePlatformShare(platform: string, data: ShareData): void {
  const { title, text, url } = data;
  const encodedUrl = encodeURIComponent(url);
  const encodedTitle = encodeURIComponent(title);
  const encodedText = encodeURIComponent(text || title);
  
  const shareUrls: Record<string, string> = {
    twitter: `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`,
    linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
    facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
    whatsapp: `https://wa.me/?text=${encodedText}%20${encodedUrl}`,
    email: `mailto:?subject=${encodedTitle}&body=${encodedText}%0A%0A${encodedUrl}`,
  };
  
  if (platform === 'copy') {
    copyToClipboard(url);
    return;
  }
  
  const shareUrl = shareUrls[platform];
  if (!shareUrl) return;
  
  if (platform === 'email') {
    window.location.href = shareUrl;
  } else {
    openSharePopup(shareUrl, platform);
  }
}

function openSharePopup(url: string, platform: string): void {
  const width = 600;
  const height = 400;
  const left = (window.innerWidth - width) / 2;
  const top = (window.innerHeight - height) / 2;
  
  window.open(
    url,
    `share-${platform}`,
    `width=${width},height=${height},left=${left},top=${top},toolbar=no,menubar=no`
  );
}

function copyToClipboard(text: string): void {
  const btn = document.querySelector('[data-platform="copy"]') as HTMLButtonElement;
  
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => {
      showCopiedFeedback(btn);
    });
  } else {
    // Fallback for older browsers
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showCopiedFeedback(btn);
  }
}

function showCopiedFeedback(btn: HTMLButtonElement): void {
  btn.classList.add('copied');
  btn.setAttribute('data-tooltip', 'Copied!');
  
  // Update icon
  const icon = btn.querySelector('i, svg');
  if (icon) {
    icon.setAttribute('data-lucide', 'check');
    if (window.lucide) {
      window.lucide.createIcons();
    }
  }
  
  setTimeout(() => {
    btn.classList.remove('copied');
    btn.removeAttribute('data-tooltip');
    if (icon) {
      icon.setAttribute('data-lucide', 'link');
      if (window.lucide) {
        window.lucide.createIcons();
      }
    }
  }, 2000);
}

// Initialize
const shareContainer = document.querySelector('.share-buttons');
if (shareContainer) {
  initShareButtons({
    container: shareContainer as HTMLElement,
    shareData: {
      title: document.title,
      text: document.querySelector('meta[name="description"]')?.getAttribute('content') || '',
      url: window.location.href
    },
    onShare: (platform) => {
      // Analytics tracking
      console.log(`Shared via ${platform}`);
      // gtag('event', 'share', { method: platform });
    }
  });
}
```

---

## REACT COMPONENT

```tsx
import { useState, useCallback } from 'react';
import { 
  Share, 
  Twitter, 
  Linkedin, 
  Facebook, 
  MessageCircle, 
  Mail, 
  Link, 
  Check 
} from 'lucide-react';

interface ShareButtonsProps {
  title: string;
  text?: string;
  url: string;
  onShare?: (platform: string) => void;
}

export function ShareButtons({ title, text, url, onShare }: ShareButtonsProps) {
  const [copied, setCopied] = useState(false);
  
  const canNativeShare = typeof navigator !== 'undefined' && 'share' in navigator;
  const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
  
  const handleNativeShare = useCallback(async () => {
    try {
      await navigator.share({ title, text, url });
      onShare?.('native');
    } catch (err) {
      if ((err as Error).name !== 'AbortError') {
        console.error('Share failed:', err);
      }
    }
  }, [title, text, url, onShare]);
  
  const handlePlatformShare = useCallback((platform: string) => {
    const encodedUrl = encodeURIComponent(url);
    const encodedTitle = encodeURIComponent(title);
    const encodedText = encodeURIComponent(text || title);
    
    const shareUrls: Record<string, string> = {
      twitter: `https://twitter.com/intent/tweet?text=${encodedText}&url=${encodedUrl}`,
      linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
      facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
      whatsapp: `https://wa.me/?text=${encodedText}%20${encodedUrl}`,
      email: `mailto:?subject=${encodedTitle}&body=${encodedText}%0A%0A${encodedUrl}`,
    };
    
    if (platform === 'email') {
      window.location.href = shareUrls[platform];
    } else {
      window.open(
        shareUrls[platform],
        `share-${platform}`,
        'width=600,height=400,toolbar=no,menubar=no'
      );
    }
    
    onShare?.(platform);
  }, [title, text, url, onShare]);
  
  const handleCopy = useCallback(async () => {
    try {
      await navigator.clipboard.writeText(url);
      setCopied(true);
      onShare?.('copy');
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Copy failed:', err);
    }
  }, [url, onShare]);
  
  return (
    <div className="share-buttons">
      <span className="share-buttons__label">Share:</span>
      
      {canNativeShare && isMobile ? (
        <button
          className="share-btn share-btn--native"
          onClick={handleNativeShare}
          aria-label="Share this page"
        >
          <Share size={18} />
          <span>Share</span>
        </button>
      ) : (
        <div className="share-buttons__platforms">
          <button
            className="share-btn share-btn--twitter"
            onClick={() => handlePlatformShare('twitter')}
            aria-label="Share on Twitter"
          >
            <Twitter size={18} />
          </button>
          
          <button
            className="share-btn share-btn--linkedin"
            onClick={() => handlePlatformShare('linkedin')}
            aria-label="Share on LinkedIn"
          >
            <Linkedin size={18} />
          </button>
          
          <button
            className="share-btn share-btn--facebook"
            onClick={() => handlePlatformShare('facebook')}
            aria-label="Share on Facebook"
          >
            <Facebook size={18} />
          </button>
          
          <button
            className="share-btn share-btn--whatsapp"
            onClick={() => handlePlatformShare('whatsapp')}
            aria-label="Share on WhatsApp"
          >
            <MessageCircle size={18} />
          </button>
          
          <button
            className="share-btn share-btn--email"
            onClick={() => handlePlatformShare('email')}
            aria-label="Share via Email"
          >
            <Mail size={18} />
          </button>
          
          <button
            className={`share-btn share-btn--copy ${copied ? 'copied' : ''}`}
            onClick={handleCopy}
            aria-label={copied ? 'Copied!' : 'Copy link'}
          >
            {copied ? <Check size={18} /> : <Link size={18} />}
          </button>
        </div>
      )}
    </div>
  );
}
```

### Usage

```tsx
function BlogPost({ post }) {
  return (
    <article>
      <h1>{post.title}</h1>
      <p>{post.excerpt}</p>
      
      <ShareButtons
        title={post.title}
        text={post.excerpt}
        url={`https://mysite.com/blog/${post.slug}`}
        onShare={(platform) => {
          // Track in analytics
          gtag('event', 'share', {
            method: platform,
            content_type: 'article',
            item_id: post.id
          });
        }}
      />
    </article>
  );
}
```

---

## SHARE URLS REFERENCE

| Platform | URL Pattern |
|----------|-------------|
| Twitter | `https://twitter.com/intent/tweet?text={text}&url={url}` |
| LinkedIn | `https://www.linkedin.com/sharing/share-offsite/?url={url}` |
| Facebook | `https://www.facebook.com/sharer/sharer.php?u={url}` |
| WhatsApp | `https://wa.me/?text={text}%20{url}` |
| Telegram | `https://t.me/share/url?url={url}&text={text}` |
| Reddit | `https://reddit.com/submit?url={url}&title={title}` |
| Pinterest | `https://pinterest.com/pin/create/button/?url={url}&description={text}` |
| Email | `mailto:?subject={title}&body={text}%0A%0A{url}` |

---

## ACCESSIBILITY

```html
<!-- Clear labels -->
<button aria-label="Share on Twitter">
  <i data-lucide="twitter" aria-hidden="true"></i>
</button>

<!-- Announce copy success -->
<div role="status" aria-live="polite" class="sr-only">
  Link copied to clipboard
</div>
```

---

## CHECKLIST

- [ ] Native Web Share API for mobile
- [ ] Popup fallbacks for desktop
- [ ] All major platforms covered
- [ ] Copy link option
- [ ] Visual feedback on copy
- [ ] Accessible labels
- [ ] Token-aware colors
- [ ] No third-party tracking scripts
- [ ] Analytics tracking (optional)
- [ ] Touch targets ≥44px
- [ ] Works without JavaScript (email fallback)
