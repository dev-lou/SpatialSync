@extends('layouts.app')
@section('title', 'ConstructHub')
@section('description', 'Build houses and buildings together with real-time collaboration. Perfect for anyone who wants to create their dream home.')

@push('styles')
<style>
/* ── HERO SECTION ────────────────────────────── */
.hero {
    position: relative;
    min-height: calc(100vh - var(--header-height));
    display: flex;
    align-items: center;
    padding: var(--space-16) 0;
    overflow: hidden;
}

.hero__bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.hero__content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
    padding: 0 var(--space-4);
}

.hero__badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-full);
    font-size: var(--text-sm);
    font-weight: 600;
    margin-bottom: var(--space-6);
    animation: fade-in-up 0.6s var(--ease-out) both;
    animation-delay: 0.1s;
    border: 1px solid rgba(0, 102, 255, 0.2);
    box-shadow: 0 2px 12px rgba(0, 102, 255, 0.1);
}

.hero__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 400;
    line-height: 1.1;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    animation: fade-in-up 0.6s var(--ease-out) both;
    animation-delay: 0.2s;
}

.hero__title-accent {
    background: linear-gradient(135deg, var(--accent) 0%, #818CF8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: var(--space-8);
    max-width: 560px;
    margin-inline: auto;
    animation: fade-in-up 0.6s var(--ease-out) both;
    animation-delay: 0.3s;
}

.hero__actions {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    align-items: center;
    animation: fade-in-up 0.6s var(--ease-out) both;
    animation-delay: 0.4s;
}

@media (min-width: 768px) {
    .hero__actions {
        flex-direction: row;
        justify-content: center;
    }
}

/* ── HERO VISUAL ─────────────────────────────── */
.hero__visual {
    position: relative;
    margin-top: var(--space-16);
    animation: fade-in-up 0.8s var(--ease-out) both;
    animation-delay: 0.5s;
}

.hero-visual {
    position: relative;
    background: var(--surface);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xl);
    overflow: hidden;
    border: 1px solid var(--border-default);
}

.hero-visual__canvas {
    position: relative;
    aspect-ratio: 16/10;
    background: linear-gradient(135deg, #FAFBFC 0%, #F1F5F9 100%);
}

.hero-visual__grid {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(to right, var(--border-default) 1px, transparent 1px),
        linear-gradient(to bottom, var(--border-default) 1px, transparent 1px);
    background-size: 24px 24px;
    opacity: 0.6;
}

.hero-visual__content {
    position: absolute;
    inset: 0;
    padding: var(--space-8);
}

/* Animated Blueprint Elements */
.bp-element {
    position: absolute;
    opacity: 0;
    animation: bp-draw 0.5s var(--ease-out) forwards;
}

.bp-wall-h {
    height: 12px;
    background: #475569;
    border-radius: 2px;
}

.bp-wall-v {
    width: 12px;
    background: #475569;
    border-radius: 2px;
}

.bp-room {
    border: 3px solid #475569;
    border-radius: 4px;
    background: rgba(0, 102, 255, 0.03);
}

.bp-door {
    position: relative;
}

.bp-door-panel {
    width: 50px;
    height: 12px;
    background: #FFFFFF;
    border: 3px solid var(--accent);
    border-radius: 2px;
}

.bp-door-arc {
    position: absolute;
    top: -25px;
    left: 0;
    width: 50px;
    height: 50px;
    border: 2px dashed var(--accent);
    border-radius: 50%;
    border-bottom-color: transparent;
    border-right-color: transparent;
    transform: rotate(-45deg);
    opacity: 0.5;
}

.bp-window {
    height: 16px;
    background: #FFFFFF;
    border: 3px solid var(--accent);
    border-radius: 2px;
    position: relative;
}

.bp-window::before,
.bp-window::after {
    content: '';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 100%;
    background: var(--accent);
}

.bp-window::before { left: 33%; }
.bp-window::after { right: 33%; }

.bp-label {
    font-family: var(--font-mono);
    font-size: var(--text-xs);
    color: var(--text-tertiary);
    font-weight: 500;
    letter-spacing: 0.05em;
}

@keyframes bp-draw {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Floating UI Elements */
.hero-float-ui {
    position: absolute;
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    animation: float-ui 5s ease-in-out infinite;
}

.hero-float-ui--toolbar {
    bottom: var(--space-4);
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: var(--space-2);
    padding: var(--space-2);
    animation-delay: -1s;
}

.hero-float-ui--panel {
    top: var(--space-4);
    right: var(--space-4);
    padding: var(--space-3);
    animation-delay: -2s;
}

.hero-float-ui--mini {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-micro);
}

.hero-float-ui--mini:hover {
    background: var(--bg-secondary);
    color: var(--accent);
}

@keyframes float-ui {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

/* ── FEATURES SECTION ────────────────────────── */
.features {
    padding: var(--space-24) 0;
    background: var(--bg-secondary);
    position: relative;
}

.features::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border-default), transparent);
}

.features__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-16);
}

.features__title {
    font-family: var(--font-display);
    font-size: var(--text-4xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.features__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── PREMIUM FEATURE CARDS ───────────────────── */
.feature-card {
    padding: var(--space-8);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-sm);
    transition: all var(--dur-base) var(--ease-out);
    cursor: pointer;
}

.feature-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-xl), 0 0 60px rgba(0, 102, 255, 0.08);
    transform: translateY(-6px);
}

.feature-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    background: linear-gradient(135deg, var(--accent-light) 0%, var(--accent-muted) 100%);
    color: var(--accent);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-6);
    transition: all var(--dur-base) var(--ease-spring);
}

.feature-card:hover .feature-card__icon {
    transform: scale(1.1) rotate(-5deg);
    box-shadow: var(--shadow-accent);
}

.feature-card__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-3);
}

.feature-card__description {
    font-size: var(--text-base);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── CTA SECTION ─────────────────────────────── */
.cta {
    padding: var(--space-24) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    color: var(--text-inverse);
    text-align: center;
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    margin-top: var(--space-16);
}

.cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.cta__content {
    position: relative;
    z-index: 1;
}

.cta__title {
    font-family: var(--font-display);
    font-size: var(--text-4xl);
    font-weight: 400;
    margin-bottom: var(--space-4);
}

.cta__subtitle {
    font-size: var(--text-lg);
    opacity: 0.9;
    margin-bottom: var(--space-8);
    max-width: 500px;
    margin-inline: auto;
}

.cta .btn {
    background: var(--text-inverse);
    color: var(--accent);
    font-weight: 600;
}

.cta .btn:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* ── SECTION COMMON STYLES ───────────────────── */
.section {
    padding: var(--space-20) 0;
}

.section__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-12);
}

.section__badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-1) var(--space-3);
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-full);
    font-size: var(--text-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-4);
}

.section__title {
    font-family: var(--font-display);
    font-size: var(--text-4xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── VIDEO SECTION ───────────────────────────── */
.section--video {
    background: var(--bg);
}

.video-section {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
    align-items: center;
}

@media (min-width: 1024px) {
    .video-section {
        grid-template-columns: 1fr 1.5fr;
        gap: var(--space-16);
    }
}

.video-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.video-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── STATS SECTION ───────────────────────────── */
.section--stats {
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg) 100%);
    position: relative;
}

.section--stats::before,
.section--stats::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border-default), transparent);
}

.section--stats::before { top: 0; }
.section--stats::after { bottom: 0; }

/* ── USE CASES SECTION ───────────────────────── */
.section--use-cases {
    background: var(--bg);
}

.use-cases-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .use-cases-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (min-width: 1024px) {
    .use-cases-grid {
        grid-template-columns: 1.5fr 1fr 1fr;
    }
    
    .use-case-card--large {
        grid-row: span 2;
    }
}

.use-case-card {
    position: relative;
    border-radius: var(--radius-xl);
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--border-default);
    transition: border-color var(--dur-base), box-shadow var(--dur-base);
}

.use-case-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-xl);
}

.use-case-card__image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform var(--dur-slow) var(--ease-out);
}

.use-case-card--large .use-case-card__image {
    height: 100%;
    min-height: 300px;
}

@media (min-width: 1024px) {
    .use-case-card--large .use-case-card__image {
        min-height: 100%;
        position: absolute;
        inset: 0;
    }
}

.use-case-card:hover .use-case-card__image {
    transform: scale(1.05);
}

.use-case-card__content {
    padding: var(--space-6);
    position: relative;
    z-index: 1;
}

.use-case-card--large .use-case-card__content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, transparent 100%);
    padding: var(--space-8);
    color: white;
}

.use-case-card__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.use-case-card--large .use-case-card__title {
    color: white;
    font-size: var(--text-2xl);
}

.use-case-card__description {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: 1.6;
}

.use-case-card--large .use-case-card__description {
    color: rgba(255,255,255,0.8);
}

/* ── TESTIMONIALS SECTION ────────────────────── */
.section--testimonials {
    background: var(--bg-secondary);
}

/* ── ANIMATIONS ──────────────────────────────── */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scroll reveal for features */
.reveal {
    opacity: 0;
    transform: translateY(var(--entrance-y, 20px));
    transition: opacity var(--dur-slow) var(--ease-out), transform var(--dur-slow) var(--ease-out);
}

.reveal.visible {
    opacity: 1;
    transform: translateY(0);
}

.stagger > *:nth-child(1) { transition-delay: calc(var(--stagger) * 0); }
.stagger > *:nth-child(2) { transition-delay: calc(var(--stagger) * 1); }
.stagger > *:nth-child(3) { transition-delay: calc(var(--stagger) * 2); }
.stagger > *:nth-child(4) { transition-delay: calc(var(--stagger) * 3); }
.stagger > *:nth-child(5) { transition-delay: calc(var(--stagger) * 4); }
.stagger > *:nth-child(5) { transition-delay: calc(var(--stagger) * 5); }
.stagger > *:nth-child(6) { transition-delay: calc(var(--stagger) * 6); }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .hero__badge,
    .hero__title,
    .hero__subtitle,
    .hero__actions,
    .hero__visual,
    .hero-float-ui,
    .feature-card,
    .feature-card__icon,
    .cta .btn {
        animation: none !important;
        transition: none !important;
    }
    
    .reveal {
        opacity: 1;
        transform: none;
    }
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="hero">
    <div class="hero__bg mesh-bg"></div>
    <div class="hero__bg particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>
    
    <div class="container">
        <div class="hero__content">
            <span class="hero__badge">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                Real-time collaboration is here
            </span>

            <h1 class="hero__title">
                Build houses<br>
                <span class="hero__title-accent">together, in real-time</span>
            </h1>

            <p class="hero__subtitle">
                ConstructHub is the collaborative platform for architects and engineers. 
                Create, share, and edit building plans with your team — no downloads required.
            </p>

            <div class="hero__actions">
                <a href="{{ route('register') }}" class="btn btn--primary btn--xl btn-glow">
                    Start for free
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
                <a href="{{ route('features') }}" class="btn btn--secondary btn--xl">
                    See how it works
                </a>
            </div>

            <!-- Premium Hero Visual -->
            <div class="hero__visual">
                <div class="hero-visual">
                    <div class="hero-visual__canvas">
                        <div class="hero-visual__grid"></div>
                        <div class="hero-visual__content">
                            <!-- Animated Blueprint Layout -->
                            <div class="bp-element bp-room" style="width: 200px; height: 160px; top: 20px; left: 20px; animation-delay: 0.8s;">
                                <span class="bp-label" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">LIVING</span>
                            </div>
                            <div class="bp-element bp-room" style="width: 140px; height: 120px; top: 20px; right: 20px; animation-delay: 1s;">
                                <span class="bp-label" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">KITCHEN</span>
                            </div>
                            <div class="bp-element bp-room" style="width: 120px; height: 100px; bottom: 20px; left: 20px; animation-delay: 1.2s;">
                                <span class="bp-label" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">BEDROOM</span>
                            </div>
                            
                            <!-- Walls -->
                            <div class="bp-element bp-wall-h" style="width: 380px; top: 16px; left: 16px; animation-delay: 0.6s;"></div>
                            <div class="bp-element bp-wall-v" style="height: 200px; top: 16px; left: 16px; animation-delay: 0.7s;"></div>
                            
                            <!-- Doors -->
                            <div class="bp-element bp-door" style="top: 170px; left: 70px; animation-delay: 1.4s;"></div>
                            <div class="bp-element bp-door" style="top: 50px; right: 175px; animation-delay: 1.6s;"></div>
                            
                            <!-- Windows -->
                            <div class="bp-element bp-window" style="width: 80px; top: 20px; left: 80px; animation-delay: 1.3s;"></div>
                            <div class="bp-element bp-window" style="width: 60px; bottom: 20px; left: 25px; animation-delay: 1.5s;"></div>
                            
                            <!-- Floating UI Elements -->
                            <div class="hero-float-ui hero-float-ui--toolbar">
                                <button class="hero-float-ui--mini"><i data-lucide="undo-2" class="w-4 h-4"></i></button>
                                <button class="hero-float-ui--mini"><i data-lucide="grid-3x3" class="w-4 h-4"></i></button>
                                <button class="hero-float-ui--mini" style="background: var(--accent); color: white;"><i data-lucide="layers" class="w-4 h-4"></i></button>
                                <button class="hero-float-ui--mini"><i data-lucide="save" class="w-4 h-4"></i></button>
                            </div>
                            
                            <div class="hero-float-ui hero-float-ui--panel">
                                <div style="font-size: 10px; color: var(--text-tertiary); font-weight: 600; margin-bottom: 4px;">TEAM</div>
                                <div style="display: flex; gap: 4px;">
                                    <div style="width: 24px; height: 24px; background: linear-gradient(135deg, #0066FF, #818CF8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">A</div>
                                    <div style="width: 24px; height: 24px; background: linear-gradient(135deg, #10B981, #34D399); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: 600;">B</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features__header reveal">
            <h2 class="features__title">Everything you need to design</h2>
            <p class="features__subtitle">
                Powerful tools designed specifically for building houses and construction projects.
            </p>
        </div>

        <div class="grid grid--3 gap-8 stagger">
            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="layers" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Building Shapes Library</h3>
                <p class="feature-card__description">
                    Pre-built shapes for walls, doors, windows, rooms, and more. 
                    Drag and drop to create professional floor plans in minutes.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="users" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Team Collaboration</h3>
                <p class="feature-card__description">
                    Invite team members as editors or viewers. 
                    Share builds via link and collaborate in real-time.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="smartphone" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Works Everywhere</h3>
                <p class="feature-card__description">
                    Access your builds from any device. 
                    No software to install — just open your browser and start designing.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="lock" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Secure Access Control</h3>
                <p class="feature-card__description">
                    You control who can view and edit your builds. 
                    Editors can share with viewers, but only you manage permissions.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="download" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Export Anywhere</h3>
                <p class="feature-card__description">
                    Export your builds as PNG images or JSON files. 
                    Perfect for presentations, documentation, or further editing.
                </p>
            </div>

            <div class="feature-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="zap" class="w-7 h-7"></i>
                </div>
                <h3 class="feature-card__title">Fast & Responsive</h3>
                <p class="feature-card__description">
                    Built for performance. Smooth canvas interactions, 
                    instant saves, and snappy controls make designing a pleasure.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Logo Cloud Section -->
<x-logo-cloud title="Trusted by teams at top firms" />

<!-- Video Demo Section -->
<section class="section section--video">
    <div class="container">
        <div class="video-section reveal">
            <div class="video-section__content">
                <h2 class="video-section__title">See ConstructHub in action</h2>
                <p class="video-section__subtitle">
                    Watch how architects at Arup use ConstructHub to collaborate on complex building designs in real-time.
                </p>
            </div>
            <div class="video-section__player">
                <x-video-player 
                    youtubeId="dQw4w9WgXcQ"
                    poster="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1280&h=720&fit=crop"
                    title="ConstructHub Product Demo"
                />
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section section--stats">
    <div class="container">
        <div class="stats-grid">
            <x-stat-counter value="50000" suffix="+" label="Blueprints Created" icon="file-text" />
            <x-stat-counter value="12000" suffix="+" label="Active Users" icon="users" />
            <x-stat-counter value="99.9" suffix="%" label="Uptime" icon="shield-check" :duration="2500" />
            <x-stat-counter value="4.9" suffix="/5" label="User Rating" icon="star" />
        </div>
    </div>
</section>

<!-- Use Cases Section -->
<section class="section section--use-cases">
    <div class="container">
        <div class="section__header reveal">
            <span class="section__badge">Use Cases</span>
            <h2 class="section__title">Built for every building project</h2>
            <p class="section__subtitle">
                From residential homes to commercial complexes, ConstructHub adapts to your workflow.
            </p>
        </div>

        <div class="use-cases-grid">
            <div class="use-case-card use-case-card--large glow-card reveal">
                <img 
                    src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&fit=crop" 
                    alt="Commercial architecture"
                    width="800"
                    height="600"
                    loading="lazy"
                    class="use-case-card__image"
                >
                <div class="use-case-card__content">
                    <h3 class="use-case-card__title">Commercial Architecture</h3>
                    <p class="use-case-card__description">Design office buildings, retail spaces, and mixed-use developments with tools built for scale.</p>
                </div>
            </div>

            <div class="use-case-card glow-card reveal">
                <img 
                    src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&h=400&fit=crop" 
                    alt="Residential homes"
                    width="600"
                    height="400"
                    loading="lazy"
                    class="use-case-card__image"
                >
                <div class="use-case-card__content">
                    <h3 class="use-case-card__title">Residential Design</h3>
                    <p class="use-case-card__description">Create floor plans for single-family homes, apartments, and luxury villas.</p>
                </div>
            </div>

            <div class="use-case-card glow-card reveal">
                <img 
                    src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=600&h=400&fit=crop" 
                    alt="Interior design"
                    width="600"
                    height="400"
                    loading="lazy"
                    class="use-case-card__image"
                >
                <div class="use-case-card__content">
                    <h3 class="use-case-card__title">Interior Planning</h3>
                    <p class="use-case-card__description">Plan interior layouts with furniture placement and spatial flow optimization.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section section--testimonials">
    <div class="container">
        <div class="section__header reveal">
            <span class="section__badge">Testimonials</span>
            <h2 class="section__title">Loved by architects worldwide</h2>
            <p class="section__subtitle">
                Join thousands of design professionals who trust ConstructHub for their projects.
            </p>
        </div>

        <div class="testimonials-grid">
            <x-testimonial-card 
                quote="ConstructHub transformed how our team collaborates. We can now iterate on designs 3x faster with real-time feedback."
                name="Sarah Chen"
                title="Principal Architect"
                company="Foster + Partners"
                image="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=96&h=96&fit=crop"
                featured
            />
            <x-testimonial-card 
                quote="The building shapes library is incredibly comprehensive. It's like having a CAD system in your browser."
                name="Michael Torres"
                title="Senior Engineer"
                company="Arup"
                image="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=96&h=96&fit=crop"
            />
            <x-testimonial-card 
                quote="Finally, a house building tool that doesn't require IT support to set up. Our entire team was onboarded in minutes."
                name="Emma Wilson"
                title="Design Director"
                company="Gensler"
                image="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=96&h=96&fit=crop"
            />
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta__content reveal">
            <h2 class="cta__title">Ready to start designing?</h2>
            <p class="cta__subtitle">
                Join architects and engineers who use ConstructHub to create better buildings together.
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
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            card.style.setProperty('--mouse-x', `${x}%`);
            card.style.setProperty('--mouse-y', `${y}%`);
        });
    });
});
</script>
@endpush
