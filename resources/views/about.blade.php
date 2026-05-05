@extends('layouts.app')
@section('title', 'About')
@section('description', 'Learn about SpatialSync — a collaborative 3D building design platform built by ISUFST students.')

@push('styles')
<style>
/* ── ABOUT HERO ────────────────────────────── */
.about-hero {
    position: relative;
    padding: calc(120px + var(--space-8)) 0 var(--space-24);
    background: radial-gradient(circle at 50% -20%, var(--accent-muted) 0%, var(--bg) 70%);
    overflow: hidden;
}

.about-hero__content {
    position: relative;
    z-index: 1;
    max-width: 900px;
    margin: 0 auto;
    text-align: center;
}

.about-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: rgba(0, 102, 255, 0.08);
    backdrop-filter: blur(10px);
    color: var(--accent);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-8);
    border: 1px solid rgba(0, 102, 255, 0.1);
}

.about-hero__title {
    font-family: var(--font-display);
    font-size: clamp(3.5rem, 8vw, 6rem);
    font-weight: 900;
    letter-spacing: -0.04em;
    color: var(--text-primary);
    margin-bottom: var(--space-8);
    line-height: 0.95;
}

.about-hero__title span {
    display: block;
    background: linear-gradient(135deg, var(--accent) 0%, #9333EA 50%, var(--accent) 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 5s linear infinite;
}

.about-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.6;
    max-width: 720px;
    margin: 0 auto;
}

/* ── STORY SECTION ───────────────────────────── */
.story-section {
    padding: var(--space-24) 0;
    background: var(--bg);
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-20);
    align-items: center;
}

@media (min-width: 1024px) {
    .story-grid { grid-template-columns: 1fr 1fr; }
}

.story-content__badge {
    display: inline-flex;
    padding: var(--space-1) var(--space-3);
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: var(--space-6);
}

.story-content__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: var(--space-8);
    letter-spacing: -0.03em;
    line-height: 1.1;
}

.story-content__text {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: var(--space-6);
}

.story-content__quote {
    position: relative;
    padding: var(--space-8);
    background: var(--bg-secondary);
    border-radius: 32px;
    font-size: var(--text-xl);
    font-weight: 500;
    font-style: italic;
    color: var(--text-primary);
    margin: var(--space-10) 0;
    border-left: 4px solid var(--accent);
}

.story-image {
    position: relative;
    border-radius: 48px;
    overflow: hidden;
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.1);
    transform: rotate(-1deg);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.story-image:hover { transform: rotate(0deg) scale(1.02); }

.story-image img {
    width: 100%;
    height: auto;
    display: block;
}

/* ── VALUES GRID ─────────────────────────────── */
.values-section {
    padding: var(--space-24) 0;
    background: var(--bg-secondary);
}

.values-section__header {
    text-align: center;
    max-width: 800px;
    margin: 0 auto var(--space-16);
}

.values-section__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 5vw, 3.5rem);
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.03em;
}

.values-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: var(--space-6);
}

.value-card {
    grid-column: span 12;
    padding: var(--space-10);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: 40px;
    transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@media (min-width: 768px) {
    .value-card { grid-column: span 6; }
}

@media (min-width: 1024px) {
    .value-card { grid-column: span 4; }
}

.value-card:hover {
    border-color: var(--accent);
    transform: translateY(-8px);
    box-shadow: 0 40px 80px -20px rgba(0, 102, 255, 0.1);
}

.value-card__icon {
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

.value-card:hover .value-card__icon { transform: scale(1.1) rotate(-5deg); }

.value-card__title {
    font-size: var(--text-xl);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-3);
    letter-spacing: -0.01em;
}

.value-card__description {
    font-size: var(--text-base);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── TECH MOSAIC ─────────────────────────────── */
.tech-section {
    padding: var(--space-24) 0;
    background: var(--bg);
}

.tech-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-4);
    max-width: 1000px;
    margin: 0 auto;
}

.tech-item {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-4) var(--space-6);
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: 20px;
    transition: all 0.3s ease;
}

.tech-item:hover {
    border-color: var(--accent);
    background: var(--surface);
    transform: scale(1.05);
}

.tech-item__icon { color: var(--accent); }

.tech-item__name {
    font-size: var(--text-sm);
    font-weight: 700;
    color: var(--text-primary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ── CTA ─────────────────────────────────────── */
.about-cta {
    padding: var(--space-24) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #4338CA 100%);
    text-align: center;
    color: white;
}

.about-cta__title {
    font-family: var(--font-display);
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 400;
    margin-bottom: var(--space-4);
}

.about-cta__subtitle {
    font-size: var(--text-lg);
    opacity: 0.9;
    margin-bottom: var(--space-8);
    max-width: 500px;
    margin-inline: auto;
}

.about-cta .btn {
    background: white;
    color: var(--accent);
    font-weight: 600;
}

.about-cta .btn:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
}

/* ── GSAP ─────────────────────────────────────── */
.gs-fade { opacity: 0; transform: translateY(40px); }

@media (prefers-reduced-motion: reduce) {
    .gs-fade { opacity: 1 !important; transform: none !important; }
}
</style>
@endpush

@section('content')
<!-- Hero -->
<section class="about-hero">
    <div class="container">
        <div class="about-hero__content hero-fade">
            <span class="about-hero__badge">About SpatialSync</span>
            <h1 class="about-hero__title">Building the future of <span>collaborative 3D design</span></h1>
            <p class="about-hero__subtitle">
                SpatialSync is a premium web-based platform that empowers teams to design buildings together in a shared, ultra-fast, real-time 3D workspace.
            </p>
        </div>
    </div>
</section>

<!-- Story -->
<section class="story-section">
    <div class="container">
        <div class="story-grid">
            <div class="gs-fade">
                <span class="story-content__badge">Our Story</span>
                <h2 class="story-content__title">Born from a real need</h2>
                <p class="story-content__text">
                    SpatialSync was built specifically to solve a real, pervasive problem in the industry: 
                    how do you let multiple people work on an architectural design at the exact same time 
                    — fully in 3D — without requiring expensive desktop software, heavy downloads, or messy file versioning?
                </p>
                <p class="story-content__text">
                    The answer: a browser-based 3D editor with real-time sync, role-based access, 
                    built-in chat, and an issue tracking system — all powered by Supabase's real-time 
                    infrastructure and a custom Three.js-powered 3D viewport.
                </p>
                <blockquote class="story-content__quote">
                    "Collaboration shouldn't require expensive licenses. It should be as simple as sharing a link."
                </blockquote>
            </div>
            <div class="story-image gs-fade">
                <img 
                    src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&h=600&fit=crop" 
                    alt="Team collaborating on a project"
                    width="800" height="600" loading="lazy"
                >
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section class="values-section">
    <div class="container">
        <div class="values-section__header gs-fade">
            <h2 class="values-section__title">What drives us</h2>
            <p class="values-section__subtitle">
                The principles behind every feature and design decision.
            </p>
        </div>

        <div class="values-grid">
            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Collaboration First</h3>
                <p class="value-card__description">
                    Every feature is designed for teams. Real-time sync, shared workspaces, 
                    and role-based access make teamwork seamless.
                </p>
            </div>

            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Browser-Native</h3>
                <p class="value-card__description">
                    No downloads, no installations, no system requirements. 
                    If you have a browser, you can design in 3D.
                </p>
            </div>

            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="unlock" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Accessible Design</h3>
                <p class="value-card__description">
                    Professional 3D design tools shouldn't require a professional budget. 
                    SpatialSync is free to start.
                </p>
            </div>

            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Secure by Default</h3>
                <p class="value-card__description">
                    Role-based permissions, authenticated sessions, and cloud-backed storage 
                    keep your designs safe.
                </p>
            </div>

            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Speed & Simplicity</h3>
                <p class="value-card__description">
                    Create a build and start placing objects in seconds. 
                    No onboarding wizards, no complexity walls.
                </p>
            </div>

            <div class="value-card glow-card gs-fade">
                <div class="value-card__icon">
                    <i data-lucide="building-2" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Enterprise-Ready</h3>
                <p class="value-card__description">
                    Engineered to scale with modern organizations. Featuring dedicated workspaces, 
                    advanced role permissions, and rock-solid database reliability.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Tech Stack -->
<section class="tech-section">
    <div class="container">
        <div class="tech-section__header gs-fade">
            <h2 class="tech-section__title">The engine under the hood</h2>
        </div>

        <div class="tech-grid">
            <div class="tech-item gs-fade">
                <i data-lucide="code-2" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Laravel 11</span>
            </div>
            <div class="tech-item gs-fade">
                <i data-lucide="box" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Three.js</span>
            </div>
            <div class="tech-item gs-fade">
                <i data-lucide="database" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Supabase</span>
            </div>
            <div class="tech-item gs-fade">
                <i data-lucide="zap" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Realtime Sync</span>
            </div>
            <div class="tech-item gs-fade">
                <i data-lucide="shield" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Stripe</span>
            </div>
            <div class="tech-item gs-fade">
                <i data-lucide="layout" class="w-5 h-5 tech-item__icon"></i>
                <span class="tech-item__name">Blade + Vite</span>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="about-cta">
    <div class="container">
        <div class="gs-fade">
            <h2 class="about-cta__title">Try SpatialSync today</h2>
            <p class="about-cta__subtitle">
                Experience collaborative 3D building design — free, browser-based, and instant.
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

        // Animate hero immediately without ScrollTrigger
        gsap.from('.hero-fade', {
            opacity: 0, y: 40, duration: 0.8, ease: 'power3.out', delay: 0.1
        });

        gsap.utils.toArray('.gs-fade').forEach(el => {
            gsap.to(el, {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 88%',
                    toggleActions: 'play none none none'
                }
            });
        });
    }
});
</script>
@endpush
