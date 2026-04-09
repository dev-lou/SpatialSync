---
description: Complete profile settings page pattern. Includes avatar upload, name/email/password editing, notification preferences, theme toggle, and account deletion. Token-aware and accessible.
---

# UI/UX Profile Settings Page

> Complete profile settings pattern with all common features.
> Token-aware styling, fully accessible, works on mobile.

---

## OVERVIEW

This skill covers a complete profile settings implementation:
1. Avatar upload with preview
2. Profile information editing
3. Password change
4. Notification preferences
5. Theme toggle (light/dark)
6. Account deletion with confirmation

---

## PAGE STRUCTURE

```html
<main class="settings-page">
  <header class="settings-page__header">
    <h1>Account Settings</h1>
    <p>Manage your profile and preferences</p>
  </header>
  
  <nav class="settings-nav" aria-label="Settings sections">
    <a href="#profile" class="settings-nav__item settings-nav__item--active">
      <i data-lucide="user"></i>
      Profile
    </a>
    <a href="#security" class="settings-nav__item">
      <i data-lucide="lock"></i>
      Security
    </a>
    <a href="#notifications" class="settings-nav__item">
      <i data-lucide="bell"></i>
      Notifications
    </a>
    <a href="#appearance" class="settings-nav__item">
      <i data-lucide="palette"></i>
      Appearance
    </a>
    <a href="#danger" class="settings-nav__item settings-nav__item--danger">
      <i data-lucide="alert-triangle"></i>
      Danger Zone
    </a>
  </nav>
  
  <div class="settings-content">
    <!-- Sections go here -->
  </div>
</main>
```

---

## 1. AVATAR UPLOAD

### HTML

```html
<section id="profile" class="settings-section">
  <h2 class="settings-section__title">Profile Photo</h2>
  
  <div class="avatar-upload">
    <div class="avatar-upload__preview">
      <img 
        id="avatar-preview" 
        src="/placeholder-avatar.jpg" 
        alt="Your profile photo"
        class="avatar-upload__image"
      >
      <div class="avatar-upload__overlay">
        <i data-lucide="camera"></i>
      </div>
    </div>
    
    <div class="avatar-upload__actions">
      <label for="avatar-input" class="btn btn--secondary">
        <i data-lucide="upload"></i>
        Upload Photo
      </label>
      <input 
        type="file" 
        id="avatar-input" 
        accept="image/png, image/jpeg, image/webp"
        class="sr-only"
        aria-describedby="avatar-help"
      >
      <button type="button" class="btn btn--ghost btn--danger" id="remove-avatar">
        <i data-lucide="trash-2"></i>
        Remove
      </button>
    </div>
    
    <p id="avatar-help" class="settings-section__help">
      PNG, JPG, or WebP. Max 5MB. Recommended 400×400px.
    </p>
  </div>
</section>
```

### JavaScript

```typescript
interface AvatarUploadOptions {
  maxSizeMB?: number;
  minDimension?: number;
  onUpload: (file: File, preview: string) => Promise<void>;
  onRemove: () => Promise<void>;
}

function initAvatarUpload(options: AvatarUploadOptions) {
  const {
    maxSizeMB = 5,
    minDimension = 100,
    onUpload,
    onRemove
  } = options;
  
  const input = document.getElementById('avatar-input') as HTMLInputElement;
  const preview = document.getElementById('avatar-preview') as HTMLImageElement;
  const removeBtn = document.getElementById('remove-avatar') as HTMLButtonElement;
  
  input.addEventListener('change', async (e) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    
    // Validate file size
    if (file.size > maxSizeMB * 1024 * 1024) {
      showToast(`File must be smaller than ${maxSizeMB}MB`, 'error');
      return;
    }
    
    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
      showToast('Please upload a PNG, JPG, or WebP image', 'error');
      return;
    }
    
    // Validate dimensions
    const img = new Image();
    const objectUrl = URL.createObjectURL(file);
    
    img.onload = async () => {
      URL.revokeObjectURL(objectUrl);
      
      if (img.width < minDimension || img.height < minDimension) {
        showToast(`Image must be at least ${minDimension}×${minDimension}px`, 'error');
        return;
      }
      
      // Show preview
      preview.src = objectUrl;
      
      // Upload
      try {
        await onUpload(file, objectUrl);
        showToast('Profile photo updated', 'success');
      } catch (error) {
        showToast('Failed to upload photo', 'error');
      }
    };
    
    img.src = objectUrl;
  });
  
  removeBtn.addEventListener('click', async () => {
    const confirmed = await swal.confirm({
      title: 'Remove photo?',
      text: 'Your profile photo will be replaced with the default avatar.',
      confirmText: 'Remove',
      cancelText: 'Cancel'
    });
    
    if (confirmed) {
      try {
        await onRemove();
        preview.src = '/placeholder-avatar.jpg';
        showToast('Profile photo removed', 'success');
      } catch (error) {
        showToast('Failed to remove photo', 'error');
      }
    }
  });
}
```

### CSS

```css
.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--space-4);
}

.avatar-upload__preview {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  cursor: pointer;
}

.avatar-upload__image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-upload__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  opacity: 0;
  transition: opacity var(--dur-micro);
}

.avatar-upload__preview:hover .avatar-upload__overlay,
.avatar-upload__preview:focus-within .avatar-upload__overlay {
  opacity: 1;
}

.avatar-upload__actions {
  display: flex;
  gap: var(--space-3);
  flex-wrap: wrap;
}
```

---

## 2. PROFILE INFORMATION

### HTML

```html
<section id="profile-info" class="settings-section">
  <h2 class="settings-section__title">Profile Information</h2>
  
  <form id="profile-form" class="settings-form">
    <div class="field">
      <label for="full-name" class="field__label">Full Name</label>
      <input 
        type="text" 
        id="full-name" 
        name="name"
        value="John Doe"
        required
        autocomplete="name"
        class="field__input"
      >
    </div>
    
    <div class="field">
      <label for="email" class="field__label">Email Address</label>
      <input 
        type="email" 
        id="email" 
        name="email"
        value="john@example.com"
        required
        autocomplete="email"
        class="field__input"
      >
      <p class="field__help">
        We'll send a verification email if you change this.
      </p>
    </div>
    
    <div class="field">
      <label for="username" class="field__label">Username</label>
      <div class="field__input-group">
        <span class="field__prefix">@</span>
        <input 
          type="text" 
          id="username" 
          name="username"
          value="johndoe"
          required
          pattern="[a-z0-9_]+"
          autocomplete="username"
          class="field__input"
        >
      </div>
      <p class="field__help">
        Only lowercase letters, numbers, and underscores.
      </p>
    </div>
    
    <div class="field">
      <label for="bio" class="field__label">Bio</label>
      <textarea 
        id="bio" 
        name="bio"
        rows="3"
        maxlength="160"
        class="field__input"
        aria-describedby="bio-count"
      ></textarea>
      <p id="bio-count" class="field__help">
        <span class="char-count">0</span>/160 characters
      </p>
    </div>
    
    <div class="settings-form__actions">
      <button type="submit" class="btn btn--primary">
        Save Changes
      </button>
    </div>
  </form>
</section>
```

---

## 3. PASSWORD CHANGE

### HTML

```html
<section id="security" class="settings-section">
  <h2 class="settings-section__title">Change Password</h2>
  
  <form id="password-form" class="settings-form">
    <div class="field">
      <label for="current-password" class="field__label">Current Password</label>
      <div class="field__password-wrap">
        <input 
          type="password" 
          id="current-password" 
          name="current_password"
          required
          autocomplete="current-password"
          class="field__input"
        >
        <button 
          type="button" 
          class="field__password-toggle"
          aria-label="Show password"
          aria-pressed="false"
        >
          <i data-lucide="eye"></i>
        </button>
      </div>
    </div>
    
    <div class="field">
      <label for="new-password" class="field__label">New Password</label>
      <div class="field__password-wrap">
        <input 
          type="password" 
          id="new-password" 
          name="new_password"
          required
          minlength="8"
          autocomplete="new-password"
          class="field__input"
          aria-describedby="password-requirements"
        >
        <button 
          type="button" 
          class="field__password-toggle"
          aria-label="Show password"
          aria-pressed="false"
        >
          <i data-lucide="eye"></i>
        </button>
      </div>
      
      <!-- Password strength meter -->
      <div class="password-strength" aria-live="polite">
        <div class="password-strength__bar">
          <div class="password-strength__fill" data-strength="0"></div>
        </div>
        <span class="password-strength__label">Password strength: <strong>Weak</strong></span>
      </div>
      
      <ul id="password-requirements" class="password-requirements">
        <li data-requirement="length">At least 8 characters</li>
        <li data-requirement="uppercase">One uppercase letter</li>
        <li data-requirement="lowercase">One lowercase letter</li>
        <li data-requirement="number">One number</li>
        <li data-requirement="special">One special character</li>
      </ul>
    </div>
    
    <div class="field">
      <label for="confirm-password" class="field__label">Confirm New Password</label>
      <div class="field__password-wrap">
        <input 
          type="password" 
          id="confirm-password" 
          name="confirm_password"
          required
          autocomplete="new-password"
          class="field__input"
        >
        <button 
          type="button" 
          class="field__password-toggle"
          aria-label="Show password"
          aria-pressed="false"
        >
          <i data-lucide="eye"></i>
        </button>
      </div>
    </div>
    
    <div class="settings-form__actions">
      <button type="submit" class="btn btn--primary">
        Update Password
      </button>
    </div>
  </form>
</section>
```

---

## 4. NOTIFICATION PREFERENCES

### HTML

```html
<section id="notifications" class="settings-section">
  <h2 class="settings-section__title">Notification Preferences</h2>
  
  <form id="notifications-form" class="settings-form">
    <fieldset class="settings-fieldset">
      <legend class="settings-fieldset__legend">Email Notifications</legend>
      
      <div class="toggle-group">
        <label class="toggle">
          <input type="checkbox" name="email_marketing" checked>
          <span class="toggle__slider"></span>
          <span class="toggle__content">
            <span class="toggle__label">Marketing emails</span>
            <span class="toggle__description">
              Receive emails about new features and promotions
            </span>
          </span>
        </label>
        
        <label class="toggle">
          <input type="checkbox" name="email_updates" checked>
          <span class="toggle__slider"></span>
          <span class="toggle__content">
            <span class="toggle__label">Product updates</span>
            <span class="toggle__description">
              Get notified about important product changes
            </span>
          </span>
        </label>
        
        <label class="toggle">
          <input type="checkbox" name="email_security" checked disabled>
          <span class="toggle__slider"></span>
          <span class="toggle__content">
            <span class="toggle__label">Security alerts</span>
            <span class="toggle__description">
              Always receive security-related notifications
            </span>
            <span class="toggle__badge">Required</span>
          </span>
        </label>
      </div>
    </fieldset>
    
    <fieldset class="settings-fieldset">
      <legend class="settings-fieldset__legend">Push Notifications</legend>
      
      <div class="toggle-group">
        <label class="toggle">
          <input type="checkbox" name="push_messages">
          <span class="toggle__slider"></span>
          <span class="toggle__content">
            <span class="toggle__label">Direct messages</span>
            <span class="toggle__description">
              Get push notifications for new messages
            </span>
          </span>
        </label>
        
        <label class="toggle">
          <input type="checkbox" name="push_mentions">
          <span class="toggle__slider"></span>
          <span class="toggle__content">
            <span class="toggle__label">Mentions</span>
            <span class="toggle__description">
              Get notified when someone mentions you
            </span>
          </span>
        </label>
      </div>
    </fieldset>
    
    <div class="settings-form__actions">
      <button type="submit" class="btn btn--primary">
        Save Preferences
      </button>
    </div>
  </form>
</section>
```

---

## 5. THEME TOGGLE

### HTML

```html
<section id="appearance" class="settings-section">
  <h2 class="settings-section__title">Appearance</h2>
  
  <div class="theme-selector" role="radiogroup" aria-label="Color theme">
    <label class="theme-option">
      <input type="radio" name="theme" value="light" class="sr-only">
      <div class="theme-option__preview theme-option__preview--light">
        <div class="theme-option__preview-header"></div>
        <div class="theme-option__preview-content">
          <div class="theme-option__preview-line"></div>
          <div class="theme-option__preview-line theme-option__preview-line--short"></div>
        </div>
      </div>
      <span class="theme-option__label">
        <i data-lucide="sun"></i>
        Light
      </span>
    </label>
    
    <label class="theme-option">
      <input type="radio" name="theme" value="dark" class="sr-only">
      <div class="theme-option__preview theme-option__preview--dark">
        <div class="theme-option__preview-header"></div>
        <div class="theme-option__preview-content">
          <div class="theme-option__preview-line"></div>
          <div class="theme-option__preview-line theme-option__preview-line--short"></div>
        </div>
      </div>
      <span class="theme-option__label">
        <i data-lucide="moon"></i>
        Dark
      </span>
    </label>
    
    <label class="theme-option">
      <input type="radio" name="theme" value="system" class="sr-only" checked>
      <div class="theme-option__preview theme-option__preview--system">
        <div class="theme-option__preview-split">
          <div class="theme-option__preview-half theme-option__preview-half--light"></div>
          <div class="theme-option__preview-half theme-option__preview-half--dark"></div>
        </div>
      </div>
      <span class="theme-option__label">
        <i data-lucide="monitor"></i>
        System
      </span>
    </label>
  </div>
</section>
```

### CSS

```css
.theme-selector {
  display: flex;
  gap: var(--space-4);
  flex-wrap: wrap;
}

.theme-option {
  cursor: pointer;
}

.theme-option input:checked + .theme-option__preview {
  border-color: var(--accent);
  box-shadow: 0 0 0 2px var(--accent);
}

.theme-option input:focus-visible + .theme-option__preview {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.theme-option__preview {
  width: 120px;
  height: 80px;
  border: 2px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  transition: border-color var(--dur-micro), box-shadow var(--dur-micro);
}

.theme-option__preview--light {
  background: #ffffff;
}

.theme-option__preview--dark {
  background: #1a1a2e;
}

.theme-option__preview-header {
  height: 20%;
  background: currentColor;
  opacity: 0.1;
}

.theme-option__label {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2);
  margin-top: var(--space-2);
  font-size: var(--text-sm);
}
```

### JavaScript

```typescript
function initThemeSelector() {
  const radios = document.querySelectorAll('input[name="theme"]');
  
  radios.forEach(radio => {
    radio.addEventListener('change', (e) => {
      const theme = (e.target as HTMLInputElement).value;
      setTheme(theme);
    });
  });
  
  // Set initial value from localStorage or system
  const savedTheme = localStorage.getItem('theme') || 'system';
  const radio = document.querySelector(`input[name="theme"][value="${savedTheme}"]`) as HTMLInputElement;
  if (radio) radio.checked = true;
}

function setTheme(theme: 'light' | 'dark' | 'system') {
  localStorage.setItem('theme', theme);
  
  if (theme === 'system') {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
  } else {
    document.documentElement.setAttribute('data-theme', theme);
  }
}
```

---

## 6. DELETE ACCOUNT (DANGER ZONE)

### HTML

```html
<section id="danger" class="settings-section settings-section--danger">
  <h2 class="settings-section__title">Danger Zone</h2>
  
  <div class="danger-zone">
    <div class="danger-zone__item">
      <div class="danger-zone__content">
        <h3>Delete Account</h3>
        <p>
          Permanently delete your account and all associated data. 
          This action cannot be undone.
        </p>
      </div>
      <button 
        type="button" 
        class="btn btn--danger btn--outline"
        id="delete-account-btn"
      >
        Delete Account
      </button>
    </div>
  </div>
</section>

<!-- Delete Confirmation Dialog -->
<dialog id="delete-account-dialog" class="dialog dialog--danger">
  <form method="dialog" id="delete-account-form">
    <header class="dialog__header">
      <i data-lucide="alert-triangle" class="dialog__icon"></i>
      <h2>Delete Account</h2>
    </header>
    
    <div class="dialog__content">
      <p>
        This will permanently delete your account including:
      </p>
      <ul>
        <li>All your profile information</li>
        <li>Your saved preferences</li>
        <li>All associated data</li>
      </ul>
      <p class="dialog__warning">
        <strong>This action cannot be undone.</strong>
      </p>
      
      <div class="field">
        <label for="confirm-delete" class="field__label">
          Type <strong>DELETE</strong> to confirm
        </label>
        <input 
          type="text" 
          id="confirm-delete" 
          name="confirm"
          pattern="DELETE"
          required
          autocomplete="off"
          class="field__input"
        >
      </div>
      
      <div class="field">
        <label for="delete-password" class="field__label">
          Enter your password
        </label>
        <input 
          type="password" 
          id="delete-password" 
          name="password"
          required
          autocomplete="current-password"
          class="field__input"
        >
      </div>
    </div>
    
    <footer class="dialog__footer">
      <button type="button" value="cancel" class="btn btn--ghost">
        Cancel
      </button>
      <button type="submit" class="btn btn--danger" disabled>
        Delete My Account
      </button>
    </footer>
  </form>
</dialog>
```

### JavaScript

```typescript
function initDeleteAccount() {
  const btn = document.getElementById('delete-account-btn');
  const dialog = document.getElementById('delete-account-dialog') as HTMLDialogElement;
  const form = document.getElementById('delete-account-form') as HTMLFormElement;
  const confirmInput = document.getElementById('confirm-delete') as HTMLInputElement;
  const submitBtn = form.querySelector('button[type="submit"]') as HTMLButtonElement;
  
  btn?.addEventListener('click', () => {
    dialog.showModal();
  });
  
  // Enable submit only when "DELETE" is typed
  confirmInput?.addEventListener('input', () => {
    submitBtn.disabled = confirmInput.value !== 'DELETE';
  });
  
  // Handle cancel
  dialog.querySelector('[value="cancel"]')?.addEventListener('click', () => {
    dialog.close();
    form.reset();
    submitBtn.disabled = true;
  });
  
  // Handle form submission
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(form);
    
    try {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i data-lucide="loader-2" class="spin"></i> Deleting...';
      
      const response = await fetch('/api/account', {
        method: 'DELETE',
        body: formData
      });
      
      if (response.ok) {
        window.location.href = '/goodbye';
      } else {
        const error = await response.json();
        showToast(error.message || 'Failed to delete account', 'error');
      }
    } catch (error) {
      showToast('An error occurred. Please try again.', 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Delete My Account';
    }
  });
}
```

---

## COMPLETE CSS

```css
/* Settings page layout */
.settings-page {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--space-6);
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: var(--space-8);
}

@media (max-width: 768px) {
  .settings-page {
    grid-template-columns: 1fr;
    padding: var(--space-4);
  }
}

.settings-page__header {
  grid-column: 1 / -1;
  margin-bottom: var(--space-4);
}

.settings-page__header h1 {
  font-size: var(--text-3xl);
  margin-bottom: var(--space-2);
}

/* Settings navigation */
.settings-nav {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  position: sticky;
  top: var(--space-6);
  height: fit-content;
}

@media (max-width: 768px) {
  .settings-nav {
    flex-direction: row;
    overflow-x: auto;
    position: static;
    padding-bottom: var(--space-4);
    border-bottom: 1px solid var(--border);
  }
}

.settings-nav__item {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding: var(--space-3) var(--space-4);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  text-decoration: none;
  transition: background var(--dur-micro), color var(--dur-micro);
}

.settings-nav__item:hover {
  background: var(--bg-hover);
  color: var(--text);
}

.settings-nav__item--active {
  background: var(--accent-soft);
  color: var(--accent);
}

.settings-nav__item--danger {
  color: var(--color-error);
}

/* Settings sections */
.settings-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: var(--space-6);
  margin-bottom: var(--space-6);
}

.settings-section--danger {
  border-color: var(--color-error);
}

.settings-section__title {
  font-size: var(--text-xl);
  font-weight: 600;
  margin-bottom: var(--space-6);
  padding-bottom: var(--space-4);
  border-bottom: 1px solid var(--border);
}

.settings-section__help {
  color: var(--muted);
  font-size: var(--text-sm);
  margin-top: var(--space-2);
}

/* Settings form */
.settings-form {
  display: flex;
  flex-direction: column;
  gap: var(--space-6);
}

.settings-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--space-3);
  padding-top: var(--space-4);
  border-top: 1px solid var(--border);
}

/* Toggle group */
.toggle-group {
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.toggle {
  display: flex;
  align-items: flex-start;
  gap: var(--space-4);
  cursor: pointer;
}

.toggle__slider {
  position: relative;
  width: 44px;
  height: 24px;
  background: var(--border);
  border-radius: var(--radius-full);
  flex-shrink: 0;
  transition: background var(--dur-micro);
}

.toggle__slider::after {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: transform var(--dur-micro);
}

.toggle input:checked + .toggle__slider {
  background: var(--accent);
}

.toggle input:checked + .toggle__slider::after {
  transform: translateX(20px);
}

.toggle input:focus-visible + .toggle__slider {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.toggle__content {
  flex: 1;
}

.toggle__label {
  font-weight: 500;
  display: block;
}

.toggle__description {
  color: var(--muted);
  font-size: var(--text-sm);
  margin-top: var(--space-1);
}

.toggle__badge {
  display: inline-block;
  padding: var(--space-1) var(--space-2);
  background: var(--muted);
  color: white;
  font-size: var(--text-xs);
  border-radius: var(--radius-sm);
  margin-top: var(--space-2);
}

/* Danger zone */
.danger-zone__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding: var(--space-4);
  background: var(--bg);
  border-radius: var(--radius-md);
}

.danger-zone__content h3 {
  font-size: var(--text-base);
  font-weight: 600;
  margin-bottom: var(--space-1);
}

.danger-zone__content p {
  color: var(--muted);
  font-size: var(--text-sm);
}
```

---

## CHECKLIST

- [ ] Avatar upload with validation
- [ ] Profile form with autocomplete
- [ ] Password change with strength meter
- [ ] Notification toggles
- [ ] Theme selector (light/dark/system)
- [ ] Delete account with double confirmation
- [ ] All forms have CSRF protection
- [ ] Accessible labels and ARIA
- [ ] Mobile-responsive layout
- [ ] Token-aware styling throughout
