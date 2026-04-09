---
description: Security headers configuration for CSP, HSTS, X-Frame-Options, and more. Covers Next.js, Laravel, and static sites.
---

# Security Headers Skill

> Configure essential security headers to protect your application.

---

## REQUIRED SECURITY HEADERS

| Header | Purpose | Priority |
|--------|---------|----------|
| Content-Security-Policy | Prevent XSS, injection attacks | Critical |
| Strict-Transport-Security | Force HTTPS | Critical |
| X-Content-Type-Options | Prevent MIME sniffing | High |
| X-Frame-Options | Prevent clickjacking | High |
| Referrer-Policy | Control referrer info | Medium |
| Permissions-Policy | Restrict browser features | Medium |

---

## CONTENT-SECURITY-POLICY (CSP)

### Start with Report-Only

```
Content-Security-Policy-Report-Only: 
  default-src 'self';
  script-src 'self' 'unsafe-inline' 'unsafe-eval';
  style-src 'self' 'unsafe-inline';
  img-src 'self' data: https:;
  font-src 'self';
  connect-src 'self';
  report-uri /api/csp-report;
```

### Production CSP (Strict)

```
Content-Security-Policy:
  default-src 'self';
  script-src 'self' https://cdn.jsdelivr.net https://unpkg.com;
  style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
  img-src 'self' data: https://images.unsplash.com https://picsum.photos;
  font-src 'self' https://fonts.gstatic.com;
  connect-src 'self' https://api.example.com wss://api.example.com;
  frame-ancestors 'none';
  form-action 'self';
  base-uri 'self';
  upgrade-insecure-requests;
```

### CSP with Nonces (for inline scripts)

```html
<!-- Server generates unique nonce per request -->
<script nonce="abc123">
  // Inline script allowed because nonce matches
</script>
```

```
Content-Security-Policy: script-src 'self' 'nonce-abc123';
```

---

## NEXT.JS CONFIGURATION

### `next.config.js`

```javascript
// next.config.js
const securityHeaders = [
  {
    key: 'Content-Security-Policy',
    value: [
      "default-src 'self'",
      "script-src 'self' 'unsafe-eval' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com",
      "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
      "img-src 'self' data: blob: https://images.unsplash.com https://picsum.photos",
      "font-src 'self' https://fonts.gstatic.com",
      "connect-src 'self' https://api.example.com wss:",
      "frame-ancestors 'none'",
      "form-action 'self'",
      "base-uri 'self'",
    ].join('; '),
  },
  {
    key: 'Strict-Transport-Security',
    value: 'max-age=31536000; includeSubDomains; preload',
  },
  {
    key: 'X-Content-Type-Options',
    value: 'nosniff',
  },
  {
    key: 'X-Frame-Options',
    value: 'DENY',
  },
  {
    key: 'X-XSS-Protection',
    value: '1; mode=block',
  },
  {
    key: 'Referrer-Policy',
    value: 'strict-origin-when-cross-origin',
  },
  {
    key: 'Permissions-Policy',
    value: [
      'camera=()',
      'microphone=()',
      'geolocation=()',
      'interest-cohort=()',
    ].join(', '),
  },
];

/** @type {import('next').NextConfig} */
const nextConfig = {
  async headers() {
    return [
      {
        // Apply to all routes
        source: '/:path*',
        headers: securityHeaders,
      },
    ];
  },
};

module.exports = nextConfig;
```

### Middleware for Dynamic CSP Nonce

```typescript
// middleware.ts
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

export function middleware(request: NextRequest) {
  const nonce = Buffer.from(crypto.randomUUID()).toString('base64');
  
  const cspHeader = `
    default-src 'self';
    script-src 'self' 'nonce-${nonce}' 'strict-dynamic';
    style-src 'self' 'nonce-${nonce}';
    img-src 'self' data: blob:;
    font-src 'self';
    connect-src 'self';
    frame-ancestors 'none';
  `.replace(/\s{2,}/g, ' ').trim();

  const response = NextResponse.next();
  
  response.headers.set('Content-Security-Policy', cspHeader);
  response.headers.set('x-nonce', nonce);
  
  return response;
}
```

---

## LARAVEL CONFIGURATION

### Middleware

```php
// app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Generate nonce for inline scripts
        $nonce = base64_encode(random_bytes(16));
        view()->share('cspNonce', $nonce);

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data: https://images.unsplash.com https://picsum.photos",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
```

### Register Middleware

```php
// app/Http/Kernel.php
protected $middleware = [
    // ...
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### Blade Usage with Nonce

```blade
{{-- resources/views/layouts/app.blade.php --}}
<script nonce="{{ $cspNonce }}">
    // Inline script with nonce
</script>

<style nonce="{{ $cspNonce }}">
    /* Inline style with nonce */
</style>
```

### Apache `.htaccess`

```apache
# .htaccess
<IfModule mod_headers.c>
    # HSTS
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    
    # Prevent MIME sniffing
    Header always set X-Content-Type-Options "nosniff"
    
    # Clickjacking protection
    Header always set X-Frame-Options "DENY"
    
    # XSS protection (legacy browsers)
    Header always set X-XSS-Protection "1; mode=block"
    
    # Referrer policy
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # CSP (customize as needed)
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;"
</IfModule>
```

### Nginx Configuration

```nginx
# nginx.conf or site config
server {
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    
    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Permissions-Policy "camera=(), microphone=(), geolocation=()" always;
    
    # CSP
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" always;
}
```

---

## STATIC SITES (VERCEL/NETLIFY)

### Vercel `vercel.json`

```json
{
  "headers": [
    {
      "source": "/(.*)",
      "headers": [
        {
          "key": "Content-Security-Policy",
          "value": "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self';"
        },
        {
          "key": "Strict-Transport-Security",
          "value": "max-age=31536000; includeSubDomains; preload"
        },
        {
          "key": "X-Content-Type-Options",
          "value": "nosniff"
        },
        {
          "key": "X-Frame-Options",
          "value": "DENY"
        },
        {
          "key": "Referrer-Policy",
          "value": "strict-origin-when-cross-origin"
        },
        {
          "key": "Permissions-Policy",
          "value": "camera=(), microphone=(), geolocation=()"
        }
      ]
    }
  ]
}
```

### Netlify `netlify.toml`

```toml
# netlify.toml
[[headers]]
  for = "/*"
  [headers.values]
    Content-Security-Policy = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self';"
    Strict-Transport-Security = "max-age=31536000; includeSubDomains; preload"
    X-Content-Type-Options = "nosniff"
    X-Frame-Options = "DENY"
    Referrer-Policy = "strict-origin-when-cross-origin"
    Permissions-Policy = "camera=(), microphone=(), geolocation=()"
```

### Netlify `_headers` File

```
/*
  Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;
  Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
  X-Content-Type-Options: nosniff
  X-Frame-Options: DENY
  Referrer-Policy: strict-origin-when-cross-origin
  Permissions-Policy: camera=(), microphone=(), geolocation=()
```

---

## CSP REPORT ENDPOINT

```typescript
// app/api/csp-report/route.ts (Next.js)
import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  const report = await request.json();
  
  // Log to console (or send to monitoring service)
  console.log('CSP Violation:', JSON.stringify(report, null, 2));
  
  // Send to Sentry, LogRocket, etc.
  // await fetch('https://sentry.io/api/csp-report/', { ... });
  
  return NextResponse.json({ received: true });
}
```

```php
// Laravel route
Route::post('/api/csp-report', function (Request $request) {
    Log::warning('CSP Violation', $request->all());
    return response()->json(['received' => true]);
});
```

---

## TESTING HEADERS

### Using curl

```bash
curl -I https://yoursite.com
```

### Online Tools

- https://securityheaders.com
- https://observatory.mozilla.org
- https://csp-evaluator.withgoogle.com

### Automated Test

```javascript
// tests/security-headers.test.js
describe('Security Headers', () => {
  let headers;
  
  beforeAll(async () => {
    const response = await fetch('http://localhost:3000');
    headers = response.headers;
  });

  test('has Content-Security-Policy', () => {
    expect(headers.get('content-security-policy')).toBeTruthy();
  });

  test('has Strict-Transport-Security', () => {
    expect(headers.get('strict-transport-security')).toContain('max-age=');
  });

  test('has X-Content-Type-Options', () => {
    expect(headers.get('x-content-type-options')).toBe('nosniff');
  });

  test('has X-Frame-Options', () => {
    expect(headers.get('x-frame-options')).toBe('DENY');
  });

  test('has Referrer-Policy', () => {
    expect(headers.get('referrer-policy')).toBeTruthy();
  });
});
```

---

## CHECKLIST

- [ ] CSP configured (start with report-only)
- [ ] HSTS enabled with preload
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: DENY (or SAMEORIGIN)
- [ ] Referrer-Policy configured
- [ ] Permissions-Policy restricts unused features
- [ ] CSP report endpoint set up
- [ ] Headers tested with securityheaders.com
- [ ] No console errors from CSP violations
