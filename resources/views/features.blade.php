@extends('layouts.app')
@section('title', 'Features')
@section('description', 'Explore the powerful features of SpatialSync — 3D spatial canvas, real-time collaboration, multi-floor design, issue tracking, and more.')

@push('styles')
<style>
/* ── FEATURES HERO ───────────────────────────── */
.features-hero {
    position: relative;
    padding: calc(100px + var(--space-8)) 0 var(--space-24);
    background: radial-gradient(circle at 50% -20%, var(--accent-muted) 0%, var(--bg) 70%);
    overflow: hidden;
}

.features-hero__content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 800px;
    margin: 0 auto;
}

.features-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: rgba(0, 102, 255, 0.08);
    backdrop-filter: blur(10px);
    color: var(--accent);
    font-size: var(--text-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    border-radius: var(--radius-full);
    margin-bottom: var(--space-8);
    border: 1px solid rgba(0, 102, 255, 0.1);
}

.features-hero__title {
    font-family: var(--font-display);
    font-size: clamp(3.5rem, 8vw, 5.5rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    line-height: 1;
}

.features-hero__title span {
    display: block;
    background: linear-gradient(135deg, var(--accent) 0%, #9333EA 50%, var(--accent) 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 5s linear infinite;
}

.features-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.6;
    max-width: 640px;
    margin: 0 auto;
}

/* ── SHOWCASE (alternating) ───────────────────── */
.showcase {
    padding: var(--space-24) 0;
    background: var(--bg);
}

.showcase--alt { background: var(--bg-secondary); }

.showcase__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-16);
    align-items: center;
}

@media (min-width: 1024px) {
    .showcase__grid { grid-template-columns: 1.1fr 0.9fr; }
    .showcase--reverse .showcase__grid { direction: rtl; }
    .showcase--reverse .showcase__grid > * { direction: ltr; }
}

.showcase__img-wrap {
    position: relative;
    border-radius: 40px;
    overflow: hidden;
    background: var(--surface);
    padding: var(--space-4);
    box-shadow: 
        0 40px 100px -20px rgba(0, 0, 0, 0.1),
        0 0 0 1px rgba(0, 102, 255, 0.05);
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.showcase__img-wrap:hover {
    transform: translateY(-10px) scale(1.01);
    box-shadow: 
        0 60px 120px -20px rgba(0, 102, 255, 0.15),
        0 0 0 1px rgba(0, 102, 255, 0.1);
}

.showcase__img {
    width: 100%;
    height: auto;
    border-radius: 32px;
    display: block;
}

.showcase__img-badge {
    position: absolute;
    top: var(--space-8);
    right: var(--space-8);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    padding: var(--space-3) var(--space-5);
    border-radius: var(--radius-xl);
    font-size: var(--text-sm);
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--space-3);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid white;
}

.showcase__text { max-width: 540px; }

.showcase__badge {
    display: inline-flex;
    padding: var(--space-1) var(--space-3);
    background: var(--accent-light);
    color: var(--accent);
    font-size: var(--text-xs);
    font-weight: 700;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-6);
}

.showcase__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.showcase__desc {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: var(--space-8);
}

.showcase__list {
    list-style: none;
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-4);
}

@media (min-width: 640px) {
    .showcase__list { grid-template-columns: 1fr 1fr; }
}

.showcase__list li {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--text-primary);
    padding: var(--space-3) var(--space-4);
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-default);
    transition: all 0.3s ease;
}

.showcase__list li:hover {
    border-color: var(--accent);
    background: var(--bg);
    transform: translateX(4px);
}

.showcase__list li i {
    color: var(--accent);
    width: 18px;
    height: 18px;
}

/* ── BENTO EXTRAS ────────────────────────────── */
.extras {
    padding: var(--space-24) 0;
    background: var(--bg);
}

.extras__header {
    text-align: center;
    margin-bottom: var(--space-16);
}

.extras__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.03em;
}

.extras__grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    grid-template-rows: repeat(2, auto);
    gap: var(--space-6);
}

.ecard {
    position: relative;
    padding: var(--space-10);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: 40px;
    overflow: hidden;
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.ecard:hover {
    border-color: rgba(0, 102, 255, 0.3);
    transform: translateY(-8px);
    box-shadow: 0 40px 80px -20px rgba(0, 102, 255, 0.1);
}

/* Bento Sizing */
.ecard--large { grid-column: span 12; }
.ecard--medium { grid-column: span 12; }
.ecard--small { grid-column: span 12; }

@media (min-width: 768px) {
    .ecard--large { grid-column: span 8; }
    .ecard--medium { grid-column: span 4; }
    .ecard--small { grid-column: span 4; }
}

@media (min-width: 1024px) {
    .ecard--large { grid-column: span 7; }
    .ecard--medium { grid-column: span 5; }
    .ecard--small { grid-column: span 4; }
}

.ecard__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: 20px;
    margin-bottom: var(--space-6);
    transition: transform 0.4s var(--ease-spring);
}

.ecard:hover .ecard__icon { transform: scale(1.1) rotate(5deg); }

.ecard__title {
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-3);
    letter-spacing: -0.01em;
}

.ecard__desc {
    font-size: var(--text-base);
    color: var(--text-secondary);
    line-height: 1.6;
}

/* ── CTA ─────────────────────────────────────── */
.features-cta {
    position: relative;
    padding: var(--space-24) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #4338CA 100%);
    text-align: center;
    color: white;
    overflow: hidden;
}

.features-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 80%, rgba(255,255,255,0.08) 0%, transparent 40%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 40%);
    pointer-events: none;
}

.features-cta__content { position: relative; z-index: 1; }

.features-cta__title {
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 400;
    margin-bottom: var(--space-4);
}

.features-cta__subtitle {
    font-size: var(--text-xl);
    opacity: 0.9;
    margin-bottom: var(--space-8);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.features-cta .btn { background: white; color: var(--accent); font-weight: 600; }
.features-cta .btn:hover { background: var(--bg-secondary); transform: translateY(-2px); }

/* ── GSAP ─────────────────────────────────────── */
.gs-fade { opacity: 0; transform: translateY(40px); }
.gs-fade-right { opacity: 0; transform: translateX(60px); }
.gs-fade-left { opacity: 0; transform: translateX(-60px); }

@media (prefers-reduced-motion: reduce) {
    .gs-fade, .gs-fade-right, .gs-fade-left { opacity: 1 !important; transform: none !important; }
}
</style>
@endpush

@section('content')
<!-- Hero -->
<section class="features-hero">
    <div class="container">
        <div class="features-hero__content hero-fade">
            <div class="features-hero__badge">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                Built for spatial design
            </div>
            <h1 class="features-hero__title">
                Everything you need to <span>design in 3D</span>
            </h1>
            <p class="features-hero__subtitle">
                A complete 3D building editor with real-time collaboration, 
                multi-floor design, issue tracking, and role-based access — 
                all running in your browser.
            </p>
        </div>
    </div>
</section>

<!-- Showcase 1: 3D Editor -->
<section class="showcase" id="showcase-editor">
    <div class="container">
        <div class="showcase__grid">
            <div class="showcase__img-wrap gs-fade-left">
                <img 
                    src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&h=600&fit=crop&q=80" 
                    alt="Architectural building design"
                    width="800" height="600" loading="lazy"
                    class="showcase__img"
                >
                <div class="showcase__img-badge">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Browser-based 3D
                </div>
            </div>
            <div class="showcase__text gs-fade">
                <span class="showcase__badge">3D Editor</span>
                <h2 class="showcase__title">A full spatial canvas in your browser</h2>
                <p class="showcase__desc">
                    Place, rotate, and customize building components in a true 3D viewport. 
                    Snap-to-grid placement ensures precision without complexity.
                </p>
                <ul class="showcase__list">
                    <li><i data-lucide="layers" class="w-5 h-5"></i> Advanced Geometry engine</li>
                    <li><i data-lucide="palette" class="w-5 h-5"></i> Real-time Material swapping</li>
                    <li><i data-lucide="layout" class="w-5 h-5"></i> Multi-floor architectural stack</li>
                    <li><i data-lucide="mouse-pointer-2" class="w-5 h-5"></i> Precision Snap-to-grid</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Showcase 2: Collaboration -->
<section class="showcase showcase--alt showcase--reverse">
    <div class="container">
        <div class="showcase__grid">
            <div class="showcase__img-wrap gs-fade-right">
                <img 
                    src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&h=600&fit=crop&q=80" 
                    alt="Team working together on project"
                    width="800" height="600" loading="lazy"
                    class="showcase__img"
                >
                <div class="showcase__img-badge">
                    <i data-lucide="radio" class="w-4 h-4"></i>
                    Real-time sync
                </div>
            </div>
            <div class="showcase__text gs-fade">
                <span class="showcase__badge">Collaboration</span>
                <h2 class="showcase__title">Design together, in real-time</h2>
                <p class="showcase__desc">
                    See your teammates' changes appear instantly. Built-in chat, 
                    presence indicators, and shared sessions keep everyone aligned.
                </p>
                <ul class="showcase__list">
                    <li><i data-lucide="zap" class="w-5 h-5"></i> Low-latency Supabase Realtime</li>
                    <li><i data-lucide="message-square" class="w-5 h-5"></i> Contextual in-app chat</li>
                    <li><i data-lucide="user-plus" class="w-5 h-5"></i> Instant invitation links</li>
                    <li><i data-lucide="shield" class="w-5 h-5"></i> Granular role permissions</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Showcase 3: Issue Tracking -->
<section class="showcase">
    <div class="container">
        <div class="showcase__grid">
            <div class="showcase__img-wrap gs-fade-left">
                <img 
                    src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&h=600&fit=crop&q=80" 
                    alt="Issue tracking and project management"
                    width="800" height="600" loading="lazy"
                    class="showcase__img"
                >
                <div class="showcase__img-badge">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Pinned to 3D
                </div>
            </div>
            <div class="showcase__text gs-fade">
                <span class="showcase__badge">Issue Tracking</span>
                <h2 class="showcase__title">Pin feedback directly on the model</h2>
                <p class="showcase__desc">
                    No more vague design review emails. Pin issues directly on 3D objects, 
                    track their status, and close them out when resolved.
                </p>
                <ul class="showcase__list">
                    <li><i data-lucide="map-pin" class="w-5 h-5"></i> 3D Coordinate pinning</li>
                    <li><i data-lucide="activity" class="w-5 h-5"></i> Lifecycle status tracking</li>
                    <li><i data-lucide="alert-triangle" class="w-5 h-5"></i> Dynamic priority levels</li>
                    <li><i data-lucide="eye" class="w-5 h-5"></i> Visual audit trails</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Extras — unique to Features page, not on Home -->
<section class="extras">
    <div class="container">
        <div class="extras__header gs-fade">
            <span class="showcase__badge">Platform Extras</span>
            <h2 class="extras__title">Built-in tools that complete the workflow</h2>
        </div>

        <div class="extras__grid">
            <!-- Large Card -->
            <div class="ecard ecard--large gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="layout-dashboard" class="w-7 h-7"></i>
                </div>
                <h3 class="ecard__title">Smart Workspace Dashboard</h3>
                <p class="ecard__desc">
                    Manage all your projects from a single command center. Personal builds and team-shared 
                    projects are clearly separated, featuring live activity feeds, member presence, 
                    and quick-action shortcuts to jump back into your designs.
                </p>
            </div>

            <!-- Medium Card -->
            <div class="ecard ecard--medium gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="cloud" class="w-7 h-7"></i>
                </div>
                <h3 class="ecard__title">Cloud Intelligence</h3>
                <p class="ecard__desc">
                    Every move you make is instantly synchronized and backed up to our cloud. 
                    Never worry about lost progress or manual saving again.
                </p>
            </div>

            <!-- Small Card -->
            <div class="ecard ecard--small gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">100% Web Native</h3>
                <p class="ecard__desc">
                    No installations. No heavy desktop software. Just open your browser and design.
                </p>
            </div>

            <!-- Small Card -->
            <div class="ecard ecard--small gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">Ultra-Fast 3D</h3>
                <p class="ecard__desc">
                    Custom Three.js engine optimized for smooth performance on any modern device.
                </p>
            </div>

            <!-- Small Card -->
            <div class="ecard ecard--small gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">Enterprise Security</h3>
                <p class="ecard__desc">
                    Advanced RBAC and secure sessions keep your architectural data private.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="features-cta">
    <div class="container">
        <div class="features-cta__content gs-fade">
            <h2 class="features-cta__title">Ready to start building?</h2>
            <p class="features-cta__subtitle">
                Experience the full power of SpatialSync — create your first 3D build in under a minute.
            </p>
            <a href="{{ route('register') }}" class="btn btn--xl">
                Get started for free
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();

    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        // Animate hero immediately without ScrollTrigger, using .from so it's safe without JS
        gsap.from('.hero-fade', {
            opacity: 0, y: 40, duration: 0.8, ease: 'power3.out', delay: 0.1
        });

        gsap.utils.toArray('.gs-fade').forEach(el => {
            gsap.to(el, {
                opacity: 1, y: 0, duration: 0.8, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 88%', toggleActions: 'play none none none' }
            });
        });

        gsap.utils.toArray('.gs-fade-right').forEach(el => {
            gsap.to(el, {
                opacity: 1, x: 0, duration: 1, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%', toggleActions: 'play none none none' }
            });
        });

        gsap.utils.toArray('.gs-fade-left').forEach(el => {
            gsap.to(el, {
                opacity: 1, x: 0, duration: 1, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 85%', toggleActions: 'play none none none' }
            });
        });

        const ecards = gsap.utils.toArray('.extras__grid .ecard');
        if (ecards.length) {
            gsap.to(ecards, {
                opacity: 1, y: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out',
                scrollTrigger: { trigger: '.extras__grid', start: 'top 85%', toggleActions: 'play none none none' }
            });
        }
    }
});
</script>
@endpush
