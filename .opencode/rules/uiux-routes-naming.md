---
description: Professional route naming conventions. Kebab-case URLs, RESTful conventions, named routes in all frameworks.
---

# UI/UX Routes Naming Conventions

> Professional URL and route naming patterns.
> Consistent, RESTful, and maintainable.

---

## OVERVIEW

This skill covers:
1. URL naming conventions (kebab-case)
2. RESTful route patterns
3. Named routes (no hardcoded paths)
4. Route grouping and prefixes
5. Framework-specific examples

---

## URL CONVENTIONS

### Use Kebab-Case

```
✅ Good:
/user-profile
/blog-posts
/privacy-policy
/account-settings

❌ Bad:
/userProfile       (camelCase)
/user_profile      (snake_case)
/UserProfile       (PascalCase)
/user.profile      (dot notation)
```

### Use Nouns (Not Verbs)

```
✅ Good:
GET    /posts          (list posts)
POST   /posts          (create post)
GET    /posts/123      (view post)
PUT    /posts/123      (update post)
DELETE /posts/123      (delete post)

❌ Bad:
GET    /get-posts
POST   /create-post
GET    /show-post/123
POST   /update-post/123
POST   /delete-post/123
```

### Use Plurals for Collections

```
✅ Good:
/posts
/users
/categories
/products

❌ Bad:
/post
/user
/category
/product
```

---

## RESTFUL CONVENTIONS

### Standard CRUD Routes

| Method | URL | Action | Name |
|--------|-----|--------|------|
| GET | `/posts` | index | posts.index |
| GET | `/posts/create` | create form | posts.create |
| POST | `/posts` | store | posts.store |
| GET | `/posts/{id}` | show | posts.show |
| GET | `/posts/{id}/edit` | edit form | posts.edit |
| PUT/PATCH | `/posts/{id}` | update | posts.update |
| DELETE | `/posts/{id}` | destroy | posts.destroy |

### Nested Resources

```
# Comments on a post
GET    /posts/123/comments         comments.index
POST   /posts/123/comments         comments.store
GET    /posts/123/comments/456     comments.show
PUT    /posts/123/comments/456     comments.update
DELETE /posts/123/comments/456     comments.destroy
```

### Shallow Nesting (Recommended)

```
# Only nest where parent context is needed
GET    /posts/123/comments         (need post context)
POST   /posts/123/comments         (need post context)

# Don't nest when ID is unique
GET    /comments/456               (comment ID is unique)
PUT    /comments/456
DELETE /comments/456
```

---

## LARAVEL ROUTES

### Named Routes

```php
// routes/web.php

// Static pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Resource routes (auto-generates RESTful names)
Route::resource('posts', PostController::class);
// Creates: posts.index, posts.create, posts.store, posts.show, posts.edit, posts.update, posts.destroy

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
    });
});

// API routes
Route::prefix('api/v1')->name('api.v1.')->group(function () {
    Route::apiResource('posts', Api\PostController::class);
    // Creates: api.v1.posts.index, api.v1.posts.store, api.v1.posts.show, api.v1.posts.update, api.v1.posts.destroy
});
```

### Using Named Routes (Blade)

```blade
{{-- ✅ Always use named routes --}}
<a href="{{ route('home') }}">Home</a>
<a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
<a href="{{ route('posts.edit', ['post' => $post->id]) }}">Edit</a>

<form action="{{ route('posts.update', $post) }}" method="POST">
    @csrf
    @method('PUT')
    ...
</form>

{{-- ❌ Never hardcode paths --}}
<a href="/posts/{{ $post->id }}">Bad</a>
```

### Route Model Binding

```php
// Implicit binding (uses {post} parameter name)
Route::get('/posts/{post}', [PostController::class, 'show']);

// In controller
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}

// Custom key
Route::get('/posts/{post:slug}', [PostController::class, 'show']);
```

---

## NEXT.JS / REACT ROUTER

### Next.js App Router

```
app/
├── page.tsx                    # /
├── about/page.tsx              # /about
├── blog/
│   ├── page.tsx                # /blog
│   └── [slug]/page.tsx         # /blog/my-post
├── dashboard/
│   ├── page.tsx                # /dashboard
│   └── settings/
│       ├── page.tsx            # /dashboard/settings
│       ├── profile/page.tsx    # /dashboard/settings/profile
│       └── security/page.tsx   # /dashboard/settings/security
└── (auth)/
    ├── login/page.tsx          # /login
    └── register/page.tsx       # /register
```

### React Router Named Routes

```tsx
// routes.tsx
import { createBrowserRouter } from 'react-router-dom';

export const routes = {
  home: '/',
  about: '/about',
  login: '/login',
  register: '/register',
  dashboard: '/dashboard',
  settings: {
    index: '/settings',
    profile: '/settings/profile',
    security: '/settings/security',
  },
  posts: {
    index: '/posts',
    show: (slug: string) => `/posts/${slug}`,
    create: '/posts/new',
    edit: (slug: string) => `/posts/${slug}/edit`,
  },
};

// Usage
import { routes } from '@/routes';

<Link to={routes.home}>Home</Link>
<Link to={routes.posts.show(post.slug)}>{post.title}</Link>
<Link to={routes.settings.profile}>Profile Settings</Link>
```

### Route Helper Function

```typescript
// lib/routes.ts
type RouteParams = Record<string, string | number>;

function route(path: string, params?: RouteParams): string {
  if (!params) return path;
  
  let result = path;
  Object.entries(params).forEach(([key, value]) => {
    result = result.replace(`:${key}`, String(value));
  });
  return result;
}

// Define routes
export const ROUTES = {
  HOME: '/',
  ABOUT: '/about',
  POST: '/posts/:slug',
  POST_EDIT: '/posts/:slug/edit',
  USER: '/users/:id',
} as const;

// Usage
route(ROUTES.POST, { slug: 'my-post' })  // '/posts/my-post'
route(ROUTES.USER, { id: 123 })          // '/users/123'
```

---

## VUE ROUTER

```typescript
// router/index.ts
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
  },
  {
    path: '/about',
    name: 'about',
    component: () => import('@/views/About.vue'),
  },
  {
    path: '/posts',
    name: 'posts.index',
    component: () => import('@/views/posts/Index.vue'),
  },
  {
    path: '/posts/:slug',
    name: 'posts.show',
    component: () => import('@/views/posts/Show.vue'),
    props: true,
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'settings',
        name: 'settings.index',
        component: () => import('@/views/settings/Index.vue'),
      },
      {
        path: 'settings/profile',
        name: 'settings.profile',
        component: () => import('@/views/settings/Profile.vue'),
      },
    ],
  },
];

// Usage in templates
<router-link :to="{ name: 'home' }">Home</router-link>
<router-link :to="{ name: 'posts.show', params: { slug: post.slug } }">
  {{ post.title }}
</router-link>

// Usage in script
router.push({ name: 'dashboard' });
router.push({ name: 'posts.show', params: { slug: 'my-post' } });
```

---

## ROUTE NAMING PATTERNS

### Consistent Naming Scheme

```
resource.action

Examples:
- posts.index
- posts.show
- posts.create
- posts.store
- posts.edit
- posts.update
- posts.destroy

- users.profile
- users.settings

- auth.login
- auth.logout
- auth.register

- settings.profile
- settings.security
- settings.notifications

- api.v1.posts.index
- api.v1.posts.show
```

### Grouping by Feature

```php
// Laravel example with route groups
Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::get('/billing', [AccountController::class, 'billing'])->name('billing');
    Route::get('/team', [AccountController::class, 'team'])->name('team');
});

// Generates:
// account.index    -> /account
// account.profile  -> /account/profile
// account.billing  -> /account/billing
// account.team     -> /account/team
```

---

## SPECIAL CASES

### Authentication Routes

```
GET  /login              auth.login
POST /login              auth.login.submit
GET  /register           auth.register
POST /register           auth.register.submit
POST /logout             auth.logout
GET  /forgot-password    auth.password.request
POST /forgot-password    auth.password.email
GET  /reset-password     auth.password.reset
POST /reset-password     auth.password.update
GET  /verify-email       auth.verification.notice
GET  /verify-email/{id}  auth.verification.verify
```

### Admin Routes

```
GET  /admin                     admin.dashboard
GET  /admin/users               admin.users.index
GET  /admin/users/{id}          admin.users.show
GET  /admin/users/{id}/edit     admin.users.edit
PUT  /admin/users/{id}          admin.users.update

GET  /admin/settings            admin.settings.index
PUT  /admin/settings            admin.settings.update
```

### API Versioning

```
/api/v1/posts
/api/v1/users
/api/v2/posts       # Breaking changes in v2
```

---

## CHECKLIST

- [ ] URLs use kebab-case
- [ ] Resource URLs use plural nouns
- [ ] RESTful conventions followed
- [ ] All routes have names
- [ ] No hardcoded paths in views
- [ ] Route model binding used
- [ ] Routes grouped logically
- [ ] API routes versioned
- [ ] Auth routes follow conventions
- [ ] Admin routes prefixed
