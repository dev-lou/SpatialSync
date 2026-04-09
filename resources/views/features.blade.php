@extends('layouts.app')
@section('title', 'Features')
@section('description', 'Discover the powerful features that make ConstructHub the ultimate blueprint editor for architects and engineers.')

@push('styles')
<style>
/* ── FEATURES PAGE STYLES ────────────────────── */

/* ── HERO SECTION ────────────────────────────── */
.features-hero {
    position: relative;
    padding: var(--space-20) 0 var(--space-16);
    background: linear-gradient(135deg, var(--bg) 0%, var(--accent-light) 50%, var(--bg-secondary) 100%);
    overflow: hidden;
}

.features-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 50%, rgba(0, 102, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 40%),
        radial-gradient(circle at 60% 80%, rgba(0, 102, 255, 0.06) 0%, transparent 45%);
    pointer-events: none;
}

.features-hero__content {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-12);
    align-items: center;
}

@media (min-width: 1024px) {
    .features-hero__content {
        grid-template-columns: 1fr 1fr;
    }
}

.features-hero__text {
    text-align: center;
}

@media (min-width: 1024px) {
    .features-hero__text {
        text-align: left;
    }
}

.features-hero__badge {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-4);
    background: var(--accent-light);
    color: var(--accent);
    font-size: var(--text-sm);
    font-weight: 600;
    border-radius: var(--radius-full);
    margin-bottom: var(--space-6);
}

.features-hero__title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 400;
    color: var(--text-primary);
    line-height: 1.1;
    margin-bottom: var(--space-6);
}

.features-hero__title span {
    background: linear-gradient(90deg, var(--accent) 0%, #818CF8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.features-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: var(--space-8);
    max-width: 500px;
}

@media (min-width: 1024px) {
    .features-hero__subtitle {
        margin-left: 0;
        margin-right: auto;
    }
}

.features-hero__actions {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-4);
    justify-content: center;
}

@media (min-width: 1024px) {
    .features-hero__actions {
        justify-content: flex-start;
    }
}

/* Hero Visual with Glassmorphism */
.features-hero__visual {
    position: relative;
    display: flex;
    justify-content: center;
}

.hero-glass-card {
    position: relative;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: var(--radius-2xl);
    padding: var(--space-6);
    box-shadow: 
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.5);
    max-width: 480px;
    width: 100%;
}

.hero-glass-card__image {
    width: 100%;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
}

.hero-float-element {
    position: absolute;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.6);
    border-radius: var(--radius-lg);
    padding: var(--space-3) var(--space-4);
    box-shadow: var(--shadow-lg);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    font-size: var(--text-sm);
    font-weight: 500;
    color: var(--text-primary);
    animation: float-bob 5s ease-in-out infinite;
}

.hero-float-element--top {
    top: -20px;
    right: -20px;
    animation-delay: 0s;
}

.hero-float-element--bottom {
    bottom: 40px;
    left: -30px;
    animation-delay: -2s;
}

.hero-float-element--side {
    top: 50%;
    right: -40px;
    transform: translateY(-50%);
    animation-delay: -1s;
}

.hero-float-element__icon {
    width: 24px;
    height: 24px;
    color: var(--accent);
}

.hero-float-element__icon--success {
    color: var(--success);
}

@media (max-width: 1023px) {
    .hero-float-element--side {
        display: none;
    }
    .hero-float-element--top {
        right: 10px;
    }
    .hero-float-element--bottom {
        left: 10px;
    }
}

/* ── BENTO GRID ──────────────────────────────── */
.bento-section {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.bento-section__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.bento-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.bento-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    max-width: 600px;
    margin: 0 auto;
}

.bento-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-4);
}

@media (min-width: 768px) {
    .bento-grid {
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(2, auto);
    }
    
    .bento-grid__item--large {
        grid-column: span 1;
        grid-row: span 2;
    }
}

@media (min-width: 1024px) {
    .bento-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.bento-card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
    overflow: hidden;
}

.bento-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-xl);
    transform: translateY(-4px);
}

.bento-card--large {
    padding: var(--space-8);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 360px;
}

.bento-card--accent {
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    border-color: transparent;
    color: white;
}

.bento-card--accent:hover {
    border-color: transparent;
}

.bento-card--accent .bento-card__title,
.bento-card--accent .bento-card__description {
    color: white;
}

.bento-card--accent .bento-card__description {
    opacity: 0.9;
}

.bento-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
    transition: transform var(--dur-base) var(--ease-spring);
}

.bento-card:hover .bento-card__icon {
    transform: scale(1.1) rotate(-5deg);
}

.bento-card--accent .bento-card__icon {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.bento-card__title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.bento-card__description {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: 1.6;
}

.bento-card__image {
    margin-top: var(--space-6);
    border-radius: var(--radius-lg);
    width: 100%;
    height: auto;
    box-shadow: var(--shadow-md);
}

/* ── FEATURE SHOWCASE ────────────────────────── */
.feature-showcase {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.feature-showcase--alt {
    background: var(--bg-secondary);
}

.feature-showcase__content {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-12);
    align-items: center;
}

@media (min-width: 1024px) {
    .feature-showcase__content {
        grid-template-columns: 1fr 1fr;
    }
    
    .feature-showcase--reverse .feature-showcase__content {
        direction: rtl;
    }
    
    .feature-showcase--reverse .feature-showcase__content > * {
        direction: ltr;
    }
}

.feature-showcase__image-wrapper {
    position: relative;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-2xl);
}

.feature-showcase__image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform var(--dur-slow) var(--ease-out);
}

.feature-showcase__image-wrapper:hover .feature-showcase__image {
    transform: scale(1.03);
}

.feature-showcase__image-badge {
    position: absolute;
    bottom: var(--space-4);
    left: var(--space-4);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    padding: var(--space-2) var(--space-4);
    border-radius: var(--radius-lg);
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--space-2);
    box-shadow: var(--shadow-lg);
}

.feature-showcase__image-badge i {
    color: var(--success);
}

.feature-showcase__text {
    max-width: 520px;
}

.feature-showcase__badge {
    display: inline-block;
    padding: var(--space-1) var(--space-3);
    background: var(--accent-light);
    color: var(--accent);
    font-size: var(--text-xs);
    font-weight: 600;
    border-radius: var(--radius-sm);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--space-4);
}

.feature-showcase__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.feature-showcase__description {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: var(--space-6);
}

.feature-showcase__list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
    margin-bottom: var(--space-8);
}

.feature-showcase__list-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    font-size: var(--text-base);
    color: var(--text-secondary);
}

.feature-showcase__list-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    color: var(--success);
    margin-top: 2px;
}

/* ── DIAGONAL SECTION ────────────────────────── */
.diagonal-section {
    position: relative;
    padding: var(--space-24) 0;
    margin: var(--space-12) 0;
}

.diagonal-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--accent-light) 100%);
    clip-path: polygon(0 8%, 100% 0, 100% 92%, 0 100%);
    z-index: -1;
}

/* ── STATS SECTION ───────────────────────────── */
.stats-section {
    padding: var(--space-16) 0;
    background: var(--bg);
}

.stats-section__grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .stats-section__grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.stat-card {
    text-align: center;
    padding: var(--space-8);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    transition: border-color var(--dur-base), box-shadow var(--dur-base);
}

.stat-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-lg);
}

.stat-card__number {
    font-size: var(--text-4xl);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
    font-variant-numeric: tabular-nums;
}

.stat-card__label {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

/* ── VIDEO SECTION ───────────────────────────── */
.video-section {
    position: relative;
    padding: var(--space-20) 0;
    background: var(--bg-secondary);
    overflow: hidden;
}

.video-section__header {
    text-align: center;
    margin-bottom: var(--space-12);
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
}

.video-wrapper {
    position: relative;
    border-radius: var(--radius-2xl);
    overflow: hidden;
    box-shadow: var(--shadow-2xl);
    aspect-ratio: 16/9;
}

.video-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-wrapper__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 50%);
    display: flex;
    align-items: flex-end;
    padding: var(--space-8);
    pointer-events: none;
}

.video-wrapper__text {
    color: white;
}

.video-wrapper__title {
    font-size: var(--text-2xl);
    font-weight: 600;
    margin-bottom: var(--space-2);
}

.video-wrapper__description {
    font-size: var(--text-base);
    opacity: 0.9;
}

/* Reduced motion: show poster image instead */
@media (prefers-reduced-motion: reduce) {
    .video-wrapper video {
        display: none;
    }
    
    .video-wrapper {
        background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200');
        background-size: cover;
        background-position: center;
    }
}

/* ── MORE FEATURES GRID ──────────────────────── */
.more-features {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.more-features__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.more-features__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.more-features__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

.more-features__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .more-features__grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .more-features__grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.feature-card {
    padding: var(--space-6);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
}

.feature-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.feature-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
    transition: transform var(--dur-base) var(--ease-spring);
}

.feature-card:hover .feature-card__icon {
    transform: scale(1.1);
}

.feature-card__title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.feature-card__description {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: 1.6;
}

/* ── TESTIMONIALS ────────────────────────────── */
.features-testimonials {
    padding: var(--space-20) 0;
    background: var(--bg-secondary);
}

.features-testimonials__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.features-testimonials__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.features-testimonials__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

/* ── CTA SECTION ─────────────────────────────── */
.features-cta {
    position: relative;
    padding: var(--space-20) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    text-align: center;
    color: white;
    overflow: hidden;
}

.features-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 20% 80%, rgba(255,255,255,0.1) 0%, transparent 40%),
        radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 40%);
    pointer-events: none;
}

.features-cta__content {
    position: relative;
    z-index: 1;
}

.features-cta__title {
    font-family: var(--font-display);
    font-size: var(--text-4xl);
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

.features-cta .btn {
    background: white;
    color: var(--accent);
}

.features-cta .btn:hover {
    background: var(--bg-secondary);
    transform: translateY(-2px);
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="features-hero">
    <div class="particles">
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
        <div class="features-hero__content">
            <div class="features-hero__text reveal">
                <div class="features-hero__badge">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    Built for professionals
                </div>
                <h1 class="features-hero__title">
                    Powerful features for <span>modern architects</span>
                </h1>
                <p class="features-hero__subtitle">
                    Everything you need to create professional blueprints and floor plans. 
                    Real-time collaboration, precision tools, and seamless exports.
                </p>
                <div class="features-hero__actions">
                    <a href="{{ route('register') }}" class="btn btn--primary btn--lg btn-glow">
                        Start free trial
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                    <a href="#demo-video" class="btn btn--secondary btn--lg">
                        <i data-lucide="play" class="w-5 h-5"></i>
                        Watch demo
                    </a>
                </div>
            </div>
            <div class="features-hero__visual reveal">
                <div class="hero-glass-card float">
                    <img 
                        src="https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&h=600&fit=crop" 
                        alt="Architectural blueprint design"
                        class="hero-glass-card__image"
                        width="800"
                        height="600"
                        loading="eager"
                        fetchpriority="high"
                    >
                    <div class="hero-float-element hero-float-element--top">
                        <i data-lucide="users" class="hero-float-element__icon"></i>
                        <span>3 collaborators</span>
                    </div>
                    <div class="hero-float-element hero-float-element--bottom">
                        <i data-lucide="check-circle" class="hero-float-element__icon hero-float-element__icon--success"></i>
                        <span>Auto-saved</span>
                    </div>
                    <div class="hero-float-element hero-float-element--side">
                        <i data-lucide="zap" class="hero-float-element__icon"></i>
                        <span>Real-time sync</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bento Grid Features -->
<section class="bento-section">
    <div class="container">
        <div class="bento-section__header reveal">
            <h2 class="bento-section__title">Everything you need, nothing you don't</h2>
            <p class="bento-section__subtitle">
                Designed with architects and engineers in mind. Every feature serves a purpose.
            </p>
        </div>
        
        <div class="bento-grid stagger">
            <!-- Large Feature Card -->
            <div class="bento-card bento-card--large bento-card--accent bento-grid__item--large glow-card tilt-card reveal">
                <div>
                    <div class="bento-card__icon">
                        <i data-lucide="move" class="w-6 h-6"></i>
                    </div>
                    <h3 class="bento-card__title">Intuitive Drag & Drop</h3>
                    <p class="bento-card__description">
                        Build floor plans effortlessly with our intuitive drag-and-drop interface. 
                        Walls, doors, windows, and furniture snap into place perfectly.
                    </p>
                </div>
                <img 
                    src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&h=300&fit=crop" 
                    alt="Floor plan design interface"
                    class="bento-card__image"
                    width="600"
                    height="300"
                    loading="lazy"
                >
            </div>
            
            <!-- Small Feature Cards -->
            <div class="bento-card glow-card tilt-card reveal">
                <div class="bento-card__icon">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="bento-card__title">Real-time Collaboration</h3>
                <p class="bento-card__description">
                    Work together with your team simultaneously. See changes as they happen.
                </p>
            </div>
            
            <div class="bento-card glow-card tilt-card reveal">
                <div class="bento-card__icon">
                    <i data-lucide="grid-3x3" class="w-6 h-6"></i>
                </div>
                <h3 class="bento-card__title">Precision Grid</h3>
                <p class="bento-card__description">
                    20px grid with snap-to-grid alignment ensures pixel-perfect precision.
                </p>
            </div>
            
            <div class="bento-card glow-card tilt-card reveal">
                <div class="bento-card__icon">
                    <i data-lucide="download" class="w-6 h-6"></i>
                </div>
                <h3 class="bento-card__title">Export Anywhere</h3>
                <p class="bento-card__description">
                    Export to PNG, PDF, or JSON. Share with clients or import into CAD software.
                </p>
            </div>
            
            <div class="bento-card glow-card tilt-card reveal">
                <div class="bento-card__icon">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </div>
                <h3 class="bento-card__title">Version History</h3>
                <p class="bento-card__description">
                    Never lose work. Roll back to any previous version with one click.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Feature Showcase 1: Real-time Collaboration -->
<section class="feature-showcase">
    <div class="container">
        <div class="feature-showcase__content">
            <div class="feature-showcase__image-wrapper reveal">
                <img 
                    src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&h=600&fit=crop" 
                    alt="Team of architects collaborating on blueprints"
                    class="feature-showcase__image"
                    width="800"
                    height="600"
                    loading="lazy"
                >
                <div class="feature-showcase__image-badge">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Live collaboration active
                </div>
            </div>
            <div class="feature-showcase__text reveal">
                <span class="feature-showcase__badge">Collaboration</span>
                <h2 class="feature-showcase__title">Work together in real-time</h2>
                <p class="feature-showcase__description">
                    Invite your team to collaborate on blueprints simultaneously. 
                    See cursors move, watch changes appear instantly, and communicate 
                    through built-in chat.
                </p>
                <ul class="feature-showcase__list">
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Invite unlimited team members by email</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Role-based permissions (Editor, Viewer)</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Built-in comments and chat</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Share via unique link for client review</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn-glow">
                    Try collaboration free
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Feature Showcase 2: Precision Tools (Diagonal Section) -->
<section class="feature-showcase feature-showcase--reverse diagonal-section">
    <div class="container">
        <div class="feature-showcase__content">
            <div class="feature-showcase__image-wrapper reveal">
                <img 
                    src="https://images.unsplash.com/photo-1574958269340-fa927503f3dd?w=800&h=600&fit=crop" 
                    alt="Architectural blueprint with precise measurements"
                    class="feature-showcase__image"
                    width="800"
                    height="600"
                    loading="lazy"
                >
                <div class="feature-showcase__image-badge">
                    <i data-lucide="ruler" class="w-4 h-4"></i>
                    Precision mode
                </div>
            </div>
            <div class="feature-showcase__text reveal">
                <span class="feature-showcase__badge">Precision</span>
                <h2 class="feature-showcase__title">Architectural-grade precision</h2>
                <p class="feature-showcase__description">
                    Every line, every wall, every dimension - perfectly aligned. 
                    Our grid system and snap-to-grid functionality ensure your 
                    blueprints meet professional standards.
                </p>
                <ul class="feature-showcase__list">
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Configurable grid sizes (10px, 20px, 40px)</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Smart snapping to edges and corners</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Zoom from 25% to 200% for detail work</span>
                    </li>
                    <li class="feature-showcase__list-item">
                        <i data-lucide="check-circle" class="feature-showcase__list-icon"></i>
                        <span>Measurement overlays and dimension lines</span>
                    </li>
                </ul>
                <a href="{{ route('register') }}" class="btn btn--primary btn-glow">
                    Experience precision tools
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-section__grid stagger">
            <div class="stat-card glow-card reveal">
                <div class="stat-card__number">
                    <span data-counter="12000" data-suffix="+">0</span>
                </div>
                <div class="stat-card__label">Active architects</div>
            </div>
            <div class="stat-card glow-card reveal">
                <div class="stat-card__number">
                    <span data-counter="58000" data-suffix="+">0</span>
                </div>
                <div class="stat-card__label">Blueprints created</div>
            </div>
            <div class="stat-card glow-card reveal">
                <div class="stat-card__number">
                    <span data-counter="99.9" data-suffix="%" data-decimal="1">0</span>
                </div>
                <div class="stat-card__label">Uptime guarantee</div>
            </div>
            <div class="stat-card glow-card reveal">
                <div class="stat-card__number">
                    <span data-counter="4.9" data-suffix="/5" data-decimal="1">0</span>
                </div>
                <div class="stat-card__label">User rating</div>
            </div>
        </div>
    </div>
</section>

<!-- Video Demo Section -->
<section class="video-section" id="demo-video">
    <div class="container">
        <div class="video-section__header reveal">
            <h2 class="video-section__title">See ConstructHub in action</h2>
            <p class="video-section__subtitle">Watch how architects use ConstructHub to bring ideas to life</p>
        </div>
        
        <div class="video-wrapper reveal">
            <video 
                autoplay 
                muted 
                loop 
                playsinline
                poster="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200"
            >
                <source src="https://videos.pexels.com/video-files/3129671/3129671-uhd_2560_1440_30fps.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="video-wrapper__overlay">
                <div class="video-wrapper__text">
                    <h3 class="video-wrapper__title">Design without limits</h3>
                    <p class="video-wrapper__description">From concept to completion, all in your browser</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- More Features Grid -->
<section class="more-features">
    <div class="container">
        <div class="more-features__header reveal">
            <h2 class="more-features__title">And so much more</h2>
            <p class="more-features__subtitle">Every feature designed to make your workflow smoother</p>
        </div>
        
        <div class="more-features__grid stagger">
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Shape Library</h3>
                <p class="feature-card__description">
                    Pre-built walls, doors, windows, stairs, and furniture. Everything snaps together perfectly.
                </p>
            </div>
            
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="palette" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Custom Styling</h3>
                <p class="feature-card__description">
                    Customize colors, line weights, and fills. Match your firm's brand or client preferences.
                </p>
            </div>
            
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="lock" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Enterprise Security</h3>
                <p class="feature-card__description">
                    SOC 2 compliant with SSO/SAML support. Your designs stay private and secure.
                </p>
            </div>
            
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="smartphone" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Mobile Friendly</h3>
                <p class="feature-card__description">
                    Review blueprints on any device. Present to clients from your tablet on-site.
                </p>
            </div>
            
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Keyboard Shortcuts</h3>
                <p class="feature-card__description">
                    Power users love our extensive keyboard shortcuts. Work faster than ever.
                </p>
            </div>
            
            <div class="feature-card glow-card reveal">
                <div class="feature-card__icon">
                    <i data-lucide="headphones" class="w-6 h-6"></i>
                </div>
                <h3 class="feature-card__title">Priority Support</h3>
                <p class="feature-card__description">
                    Get help when you need it. Our support team responds within 2 hours.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="features-testimonials">
    <div class="container">
        <div class="features-testimonials__header reveal">
            <h2 class="features-testimonials__title">Trusted by leading architects</h2>
            <p class="features-testimonials__subtitle">See what professionals are saying about ConstructHub</p>
        </div>
        
        <div class="testimonials-grid stagger">
            <x-testimonial-card 
                quote="ConstructHub has transformed how our team collaborates. We've cut our design review time in half."
                name="Sarah Chen"
                role="Principal Architect, Chen & Associates"
                image="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop"
                class="reveal"
            />
            
            <x-testimonial-card 
                quote="The precision tools are incredible. It's like having AutoCAD in your browser, but actually intuitive."
                name="Marcus Williams"
                role="Senior Designer, Urban Edge Studio"
                image="https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop"
                :featured="true"
                class="reveal"
            />
            
            <x-testimonial-card 
                quote="Our clients love the share links. They can view and comment without needing an account. Game changer."
                name="Elena Rodriguez"
                role="Founder, Rodriguez Architecture"
                image="https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop"
                class="reveal"
            />
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="features-cta">
    <div class="container">
        <div class="features-cta__content reveal">
            <h2 class="features-cta__title">Ready to design smarter?</h2>
            <p class="features-cta__subtitle">
                Join 12,000+ architects and engineers who've upgraded their workflow with ConstructHub.
            </p>
            <a href="{{ route('register') }}" class="btn btn--xl">
                Start your free trial
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</section>
@endsection
