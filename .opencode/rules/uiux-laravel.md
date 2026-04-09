---
description: Dedicated skill for integrating OpenCode UI/UX 2026 design system with Laravel, Blade, and Vite.
---

# OpenCode UI/UX 2026 — Laravel Integration Skill

This skill provides guidelines and best practices for integrating the OpenCode UI/UX 2026 design system within Laravel applications, specifically focusing on Blade templates and Vite for asset bundling. Adhering to these patterns ensures a consistent, secure, and performant user experience.

## 1. CSRF Token Protection (`@csrf` Directive)

Cross-Site Request Forgery (CSRF) protection is a critical security measure in web applications. Laravel makes it easy to protect your forms using the `@csrf` Blade directive, which generates a hidden input field containing a CSRF token. This token is then verified by Laravel upon form submission.

### Step-by-step Instructions

1.  **Include in all forms**: For any HTML form that submits data via `POST`, `PUT`, or `DELETE` methods, ensure the `@csrf` directive is placed inside the `<form>` tags.

### Code Example

```blade
<form method="POST" action="/profile">
    @csrf
    <!-- Other form fields -->
    <input type="text" name="name" value="{{ old("name") }}">
    <button type="submit">Update Profile</button>
</form>
```

## 2. Vite Asset Bundling (`@vite` Directive)

Vite is the recommended build tool for modern Laravel applications, offering a fast development experience and optimized production builds. Laravel provides a convenient `@vite` Blade directive to include your compiled assets.

### Step-by-step Instructions

1.  **Configure `vite.config.js`**: Ensure your Vite configuration correctly points to your entry points (e.g., `resources/js/app.js`, `resources/css/app.css`).
2.  **Include in Blade Layout**: Place the `@vite` directive in your main Blade layout file, typically within the `<head>` section for CSS and before the closing `</body>` tag for JavaScript.

### Code Example

```blade
<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Laravel App</title>
    @vite(["resources/css/app.css", "resources/js/app.js"])
</head>
<body>
    <div id="app">
        @yield("content")
    </div>
</body>
</html>
```

## 3. Session Flashing (Success/Error Messages)

Session flashing allows you to store data in the session for only the next HTTP request. This is commonly used for displaying one-time success or error messages after an action (e.g., form submission, user creation).

### Step-by-step Instructions

1.  **Flash data in Controller**: Use `session()->flash()` or `with()` to store messages in your controller.
2.  **Display in Blade**: Check for flashed messages in your Blade templates and display them using design tokens for styling.

### Code Example

```php
// In your Controller (e.g., after saving data)
return redirect()->back()->with("success", "Profile updated successfully!");

// In your Blade template
@if (session("success"))
    <div class="alert-success" style="background-color: var(--accent); color: var(--bg); padding: var(--space-2); border-radius: var(--radius-sm);">
        {{ session("success") }}
    </div>
@endif

@if (session("error"))
    <div class="alert-error" style="background-color: var(--error); color: var(--bg); padding: var(--space-2); border-radius: var(--radius-sm);">
        {{ session("error") }}
    </div>
@endif
```

## 4. Use of Design Tokens in Blade Templates

Leveraging CSS Custom Properties (design tokens) directly in your Blade templates ensures that your Laravel application adheres to the OpenCode UI/UX 2026 design system. This allows for dynamic theming and easy maintenance of styles.

### Step-by-step Instructions

1.  **Global CSS Import**: Ensure your `app.css` (or equivalent) imports the global token definitions (e.g., `tokens.css` as described in `uiux-tokens.md`).
2.  **Apply in Inline Styles or Classes**: Use `var(--token-name)` directly in inline styles or within CSS classes that are applied to your Blade elements.

### Code Example

```blade
<button class="btn-primary" style="background-color: var(--accent); color: var(--text-inverse); padding: var(--space-3) var(--space-4); border-radius: var(--radius-md);">
    Submit
</button>

<div class="card" style="background-color: var(--surface); border: 1px solid var(--border); padding: var(--space-6); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
    <h3>Welcome</h3>
    <p style="color: var(--text-2); margin-top: var(--space-2);">This is a card component using design tokens.</p>
</div>
```

## 5. Optional: Livewire and Inertia Patterns

For more dynamic and single-page application (SPA) like experiences within Laravel, Livewire and Inertia.js are popular choices. While specific integration patterns can be extensive, here are general considerations for using design tokens.

### Livewire

Livewire components render server-side and re-render parts of the page via AJAX. Design tokens are applied as usual through your global CSS or inline styles within the Livewire component's Blade view.

```blade
<!-- my-livewire-component.blade.php -->
<div class="p-4" style="background-color: var(--surface-up); border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
    <h4 style="color: var(--text);">{{ $title }}</h4>
    <button wire:click="increment" style="background-color: var(--accent); color: var(--bg); padding: var(--space-2); border-radius: var(--radius-sm); margin-top: var(--space-2);">
        Increment
    </button>
</div>
```

### Inertia.js

Inertia.js allows you to build SPAs using classic server-side routing and controllers, but with client-side rendering using frameworks like React, Vue, or Svelte. Design tokens would primarily be consumed within your JavaScript framework components, typically by importing your CSS variables or using a CSS-in-JS solution that references them.

```javascript
// Example React component with TailwindCSS and design tokens
import React from 'react';

export default function MyComponent({ user }) {
  return (
    <div className="bg-surface p-6 rounded-lg shadow-md"
         style={{ backgroundColor: 'var(--surface)', borderRadius: 'var(--radius-lg)', boxShadow: 'var(--shadow-md)' }}>
      <h2 className="text-text-primary" style={{ color: 'var(--text)' }}>Welcome, {user.name}</h2>
      <p className="text-text-secondary" style={{ color: 'var(--text-2)' }}>This is an Inertia component.</p>
    </div>
  );
}
```
