---
description: Advanced SEO patterns including structured data (JSON-LD), meta tags, sitemap.xml, and robots.txt guidance.
---

# UI/UX Search Engine Optimization (SEO) 2026

> Structured data (JSON-LD), meta tags, sitemap.xml, 
> and robots.txt guidance for static and SSR projects.

---

## 1. STRUCTURED DATA (JSON-LD)

**Rule**: Use JSON-LD for structured data to help search engines understand your content.

```html
<!-- Organization Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "OpenCode",
  "url": "https://opencode.com",
  "logo": "https://opencode.com/logo.png",
  "sameAs": [
    "https://twitter.com/opencode",
    "https://github.com/opencode"
  ]
}
</script>

<!-- Article Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "UI/UX Design Trends 2026",
  "image": ["https://opencode.com/images/trends-2026.jpg"],
  "datePublished": "2026-04-06T08:00:00+08:00",
  "author": {
    "@type": "Person",
    "name": "Jane Doe"
  }
}
</script>
```

---

## 2. META TAGS (Title, Description, OG, Twitter)

```html
<!-- Primary Meta Tags -->
<title>OpenCode — Premium UI/UX Design System 2026</title>
<meta name="title" content="OpenCode — Premium UI/UX Design System 2026">
<meta name="description" content="The definitive UI/UX design system for 2026. Built for performance, accessibility, and luxury.">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://opencode.com/">
<meta property="og:title" content="OpenCode — Premium UI/UX Design System 2026">
<meta property="og:description" content="The definitive UI/UX design system for 2026. Built for performance, accessibility, and luxury.">
<meta property="og:image" content="https://opencode.com/og-image.jpg">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://opencode.com/">
<meta property="twitter:title" content="OpenCode — Premium UI/UX Design System 2026">
<meta property="twitter:description" content="The definitive UI/UX design system for 2026. Built for performance, accessibility, and luxury.">
<meta property="twitter:image" content="https://opencode.com/og-image.jpg">
```

---

## 3. SITEMAP.XML (Static/SSR Guidance)

```xml
<!-- sitemap.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://opencode.com/</loc>
    <lastmod>2026-04-06</lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>https://opencode.com/about</loc>
    <lastmod>2026-04-06</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
</urlset>
```

---

## 4. ROBOTS.TXT

```text
# robots.txt
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/

Sitemap: https://opencode.com/sitemap.xml
```

---

## 5. CANONICAL TAGS

**Rule**: Always include a canonical tag to prevent duplicate content issues.

```html
<link rel="canonical" href="https://opencode.com/current-page">
```

---

## 6. IMAGE SEO

**Rule**: Use descriptive filenames and alt text for all images.

```html
<img src="premium-ui-kit-2026.webp" alt="Premium UI Kit 2026 Dashboard Preview" loading="lazy">
```
