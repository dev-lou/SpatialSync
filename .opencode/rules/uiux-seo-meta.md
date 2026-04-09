---
description: Dynamic meta tags per route including title, description, canonical URL, Open Graph, and Twitter Card. Covers Next.js, Vue, and Laravel.
---

# SEO Meta Tags Skill

> Generate comprehensive meta tags for every route to maximize SEO and social sharing.

---

## REQUIRED META TAGS

Every page must have:

| Tag | Purpose | Max Length |
|-----|---------|------------|
| `<title>` | Browser tab, search results | 60 chars |
| `meta[name="description"]` | Search result snippet | 155 chars |
| `link[rel="canonical"]` | Avoid duplicate content | Full URL |
| `meta[property="og:*"]` | Facebook/LinkedIn sharing | Various |
| `meta[name="twitter:*"]` | Twitter/X sharing | Various |

---

## META TAG TEMPLATE

```html
<head>
  <!-- Primary Meta -->
  <title>{{ pageTitle }} | {{ siteName }}</title>
  <meta name="description" content="{{ pageDescription }}">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ canonicalUrl }}">

  <!-- Open Graph (Facebook, LinkedIn) -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="{{ siteName }}">
  <meta property="og:title" content="{{ ogTitle }}">
  <meta property="og:description" content="{{ ogDescription }}">
  <meta property="og:url" content="{{ canonicalUrl }}">
  <meta property="og:image" content="{{ ogImage }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:locale" content="{{ locale }}">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@{{ twitterHandle }}">
  <meta name="twitter:title" content="{{ twitterTitle }}">
  <meta name="twitter:description" content="{{ twitterDescription }}">
  <meta name="twitter:image" content="{{ twitterImage }}">

  <!-- Additional -->
  <meta name="author" content="{{ author }}">
  <meta name="theme-color" content="{{ themeColor }}">
</head>
```

---

## NEXT.JS (APP ROUTER)

### Static Metadata

```tsx
// app/about/page.tsx
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'About Us',
  description: 'Learn about our company mission, values, and team.',
  openGraph: {
    title: 'About Us | MyBrand',
    description: 'Learn about our company mission, values, and team.',
    url: 'https://mybrand.com/about',
    images: [
      {
        url: 'https://mybrand.com/og/about.jpg',
        width: 1200,
        height: 630,
        alt: 'About MyBrand',
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: 'About Us | MyBrand',
    description: 'Learn about our company mission, values, and team.',
    images: ['https://mybrand.com/og/about.jpg'],
  },
};

export default function AboutPage() {
  return <main>...</main>;
}
```

### Dynamic Metadata

```tsx
// app/blog/[slug]/page.tsx
import type { Metadata } from 'next';
import { getPost } from '@/lib/posts';

type Props = {
  params: { slug: string };
};

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const post = await getPost(params.slug);
  
  if (!post) {
    return {
      title: 'Post Not Found',
    };
  }

  const ogImage = post.coverImage || `https://mybrand.com/api/og?title=${encodeURIComponent(post.title)}`;

  return {
    title: post.title,
    description: post.excerpt,
    authors: [{ name: post.author.name }],
    openGraph: {
      title: post.title,
      description: post.excerpt,
      type: 'article',
      publishedTime: post.publishedAt,
      authors: [post.author.name],
      images: [{ url: ogImage, width: 1200, height: 630 }],
    },
    twitter: {
      card: 'summary_large_image',
      title: post.title,
      description: post.excerpt,
      images: [ogImage],
    },
  };
}
```

### Root Layout Defaults

```tsx
// app/layout.tsx
import type { Metadata } from 'next';

const siteName = process.env.NEXT_PUBLIC_SITE_NAME || 'MyBrand';
const siteUrl = process.env.NEXT_PUBLIC_SITE_URL || 'https://mybrand.com';

export const metadata: Metadata = {
  metadataBase: new URL(siteUrl),
  title: {
    default: siteName,
    template: `%s | ${siteName}`,
  },
  description: 'Your default site description for SEO.',
  openGraph: {
    type: 'website',
    siteName,
    locale: 'en_US',
  },
  twitter: {
    card: 'summary_large_image',
    site: '@mybrand',
  },
  robots: {
    index: true,
    follow: true,
  },
  alternates: {
    canonical: './',
  },
};
```

---

## VUE (VUE-META / UNHEAD)

### Setup with Unhead

```typescript
// main.ts
import { createApp } from 'vue';
import { createHead } from '@unhead/vue';

const app = createApp(App);
const head = createHead();

app.use(head);
```

### Page Component

```vue
<!-- views/About.vue -->
<script setup lang="ts">
import { useHead, useSeoMeta } from '@unhead/vue';

useSeoMeta({
  title: 'About Us',
  description: 'Learn about our company mission, values, and team.',
  ogTitle: 'About Us | MyBrand',
  ogDescription: 'Learn about our company mission, values, and team.',
  ogImage: 'https://mybrand.com/og/about.jpg',
  ogUrl: 'https://mybrand.com/about',
  twitterCard: 'summary_large_image',
  twitterTitle: 'About Us | MyBrand',
  twitterDescription: 'Learn about our company mission, values, and team.',
  twitterImage: 'https://mybrand.com/og/about.jpg',
});

// Or with useHead for more control
useHead({
  title: 'About Us',
  meta: [
    { name: 'description', content: 'Learn about...' },
  ],
  link: [
    { rel: 'canonical', href: 'https://mybrand.com/about' },
  ],
});
</script>
```

### Composable for Dynamic Pages

```typescript
// composables/useSeo.ts
import { useSeoMeta } from '@unhead/vue';

interface SeoOptions {
  title: string;
  description: string;
  image?: string;
  url?: string;
  type?: 'website' | 'article';
  publishedTime?: string;
  author?: string;
}

const siteConfig = {
  name: import.meta.env.VITE_SITE_NAME || 'MyBrand',
  url: import.meta.env.VITE_SITE_URL || 'https://mybrand.com',
  twitter: '@mybrand',
  defaultImage: '/og-default.jpg',
};

export function useSeo(options: SeoOptions) {
  const fullTitle = `${options.title} | ${siteConfig.name}`;
  const imageUrl = options.image || `${siteConfig.url}${siteConfig.defaultImage}`;
  const pageUrl = options.url || window.location.href;

  useSeoMeta({
    title: options.title,
    description: options.description,
    
    // Open Graph
    ogType: options.type || 'website',
    ogSiteName: siteConfig.name,
    ogTitle: fullTitle,
    ogDescription: options.description,
    ogImage: imageUrl,
    ogUrl: pageUrl,
    
    // Article-specific
    ...(options.type === 'article' && {
      articlePublishedTime: options.publishedTime,
      articleAuthor: options.author,
    }),
    
    // Twitter
    twitterCard: 'summary_large_image',
    twitterSite: siteConfig.twitter,
    twitterTitle: fullTitle,
    twitterDescription: options.description,
    twitterImage: imageUrl,
  });
}

// Usage in component:
// useSeo({ title: 'About Us', description: '...' });
```

---

## LARAVEL

### SEO Config

```php
// config/seo.php
return [
    'site_name' => env('APP_NAME', 'MyBrand'),
    'site_url' => env('APP_URL', 'https://mybrand.com'),
    'twitter_handle' => env('TWITTER_HANDLE', '@mybrand'),
    'default_image' => '/images/og-default.jpg',
    'default_description' => 'Your default site description.',
    'theme_color' => '#3B82F6',
];
```

### SEO Service

```php
// app/Services/SeoService.php
namespace App\Services;

class SeoService
{
    protected array $meta = [];

    public function __construct()
    {
        $this->meta = [
            'title' => config('seo.site_name'),
            'description' => config('seo.default_description'),
            'image' => config('seo.site_url') . config('seo.default_image'),
            'url' => url()->current(),
            'type' => 'website',
        ];
    }

    public function title(string $title): self
    {
        $this->meta['title'] = $title;
        return $this;
    }

    public function description(string $description): self
    {
        $this->meta['description'] = $description;
        return $this;
    }

    public function image(string $image): self
    {
        $this->meta['image'] = str_starts_with($image, 'http') 
            ? $image 
            : config('seo.site_url') . $image;
        return $this;
    }

    public function article(string $publishedAt, ?string $author = null): self
    {
        $this->meta['type'] = 'article';
        $this->meta['published_at'] = $publishedAt;
        $this->meta['author'] = $author;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'full_title' => $this->meta['title'] . ' | ' . config('seo.site_name'),
            ...$this->meta,
            'site_name' => config('seo.site_name'),
            'twitter_handle' => config('seo.twitter_handle'),
            'theme_color' => config('seo.theme_color'),
        ];
    }
}
```

### View Service Provider

```php
// app/Providers/AppServiceProvider.php
use App\Services\SeoService;

public function boot()
{
    view()->composer('*', function ($view) {
        $view->with('seo', app(SeoService::class)->toArray());
    });
}
```

### Controller Usage

```php
// app/Http/Controllers/PostController.php
use App\Services\SeoService;

public function show(Post $post, SeoService $seo)
{
    $seo->title($post->title)
        ->description($post->excerpt)
        ->image($post->cover_image)
        ->article($post->published_at, $post->author->name);

    return view('posts.show', compact('post'));
}
```

### Blade Partial

```blade
{{-- resources/views/partials/seo.blade.php --}}
<title>{{ $seo['full_title'] }}</title>
<meta name="description" content="{{ Str::limit($seo['description'], 155) }}">
<link rel="canonical" href="{{ $seo['url'] }}">
<meta name="theme-color" content="{{ $seo['theme_color'] }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo['type'] }}">
<meta property="og:site_name" content="{{ $seo['site_name'] }}">
<meta property="og:title" content="{{ $seo['full_title'] }}">
<meta property="og:description" content="{{ Str::limit($seo['description'], 200) }}">
<meta property="og:url" content="{{ $seo['url'] }}">
<meta property="og:image" content="{{ $seo['image'] }}">

@if($seo['type'] === 'article')
<meta property="article:published_time" content="{{ $seo['published_at'] }}">
@if(isset($seo['author']))
<meta property="article:author" content="{{ $seo['author'] }}">
@endif
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="{{ $seo['twitter_handle'] }}">
<meta name="twitter:title" content="{{ $seo['full_title'] }}">
<meta name="twitter:description" content="{{ Str::limit($seo['description'], 200) }}">
<meta name="twitter:image" content="{{ $seo['image'] }}">
```

### Layout Include

```blade
{{-- resources/views/layouts/app.blade.php --}}
<head>
    @include('partials.seo')
    <!-- Other head content -->
</head>
```

---

## DYNAMIC OG IMAGE GENERATION

### Next.js Edge Function

```tsx
// app/api/og/route.tsx
import { ImageResponse } from 'next/og';

export const runtime = 'edge';

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const title = searchParams.get('title') || 'Default Title';

  return new ImageResponse(
    (
      <div
        style={{
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          width: '100%',
          height: '100%',
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '48px',
        }}
      >
        <h1
          style={{
            fontSize: '64px',
            fontWeight: 'bold',
            color: 'white',
            textAlign: 'center',
            lineHeight: 1.2,
          }}
        >
          {title}
        </h1>
        <p style={{ fontSize: '24px', color: 'rgba(255,255,255,0.8)' }}>
          mybrand.com
        </p>
      </div>
    ),
    { width: 1200, height: 630 }
  );
}
```

---

## CHECKLIST

- [ ] Every page has unique `<title>` (60 chars max)
- [ ] Every page has unique `meta description` (155 chars max)
- [ ] Canonical URL set correctly
- [ ] Open Graph tags present (og:title, og:description, og:image, og:url)
- [ ] Twitter Card tags present
- [ ] OG image is 1200x630px
- [ ] Dynamic pages generate metadata from data
- [ ] Site name appended to titles via template
- [ ] No duplicate meta tags
- [ ] robots meta allows indexing (or blocks as needed)
