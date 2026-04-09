@extends('layouts.app')
@section('title', 'About')
@section('description', 'Learn about ConstructHub - the team behind the collaborative blueprint editor for architects and engineers.')

@push('styles')
<style>
/* ── ABOUT PAGE STYLES ───────────────────────── */
.about-hero {
    position: relative;
    padding: var(--space-20) 0;
    background: linear-gradient(135deg, var(--bg) 0%, var(--bg-secondary) 100%);
    overflow: hidden;
}

.about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(to right, var(--border-default) 1px, transparent 1px),
        linear-gradient(to bottom, var(--border-default) 1px, transparent 1px);
    background-size: 60px 60px;
    opacity: 0.3;
}

.about-hero__content {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.about-hero__badge {
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
    margin-bottom: var(--space-6);
}

.about-hero__title {
    font-family: var(--font-display);
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    line-height: 1.2;
}

.about-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.7;
    max-width: 600px;
    margin: 0 auto;
}

/* ── STORY SECTION ───────────────────────────── */
.story-section {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.story-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-12);
    align-items: center;
}

@media (min-width: 1024px) {
    .story-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.story-content {
    order: 2;
}

@media (min-width: 1024px) {
    .story-content {
        order: 1;
    }
}

.story-video {
    order: 1;
}

@media (min-width: 1024px) {
    .story-video {
        order: 2;
    }
}

.story-content__badge {
    display: inline-flex;
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

.story-content__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
}

.story-content__text {
    font-size: var(--text-base);
    color: var(--text-secondary);
    line-height: 1.8;
    margin-bottom: var(--space-4);
}

.story-content__quote {
    padding-left: var(--space-6);
    border-left: 3px solid var(--accent);
    font-size: var(--text-lg);
    font-style: italic;
    color: var(--text-primary);
    margin: var(--space-8) 0;
}

/* ── VALUES SECTION ──────────────────────────── */
.values-section {
    padding: var(--space-20) 0;
    background: var(--bg-secondary);
}

.values-section__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-12);
}

.values-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.values-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

.values-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .values-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .values-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.value-card {
    padding: var(--space-8);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
}

.value-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.value-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
    margin-bottom: var(--space-4);
}

.value-card__title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.value-card__description {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── TIMELINE SECTION ────────────────────────── */
.timeline-section {
    padding: var(--space-20) 0;
    background: var(--bg);
}

.timeline-section__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-12);
}

.timeline-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.timeline-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

.timeline-wrapper {
    max-width: 700px;
    margin: 0 auto;
}

/* ── TEAM SECTION ────────────────────────────── */
.team-section {
    padding: var(--space-20) 0;
    background: var(--bg-secondary);
}

.team-section__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-12);
}

.team-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.team-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
}

/* ── STATS SECTION ───────────────────────────── */
.stats-section {
    padding: var(--space-16) 0;
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    color: white;
}

.stats-section .stat-counter__number {
    color: white;
}

.stats-section .stat-counter__label {
    color: rgba(255, 255, 255, 0.8);
}

.stats-section .stat-counter__icon {
    background: rgba(255, 255, 255, 0.15);
    color: white;
}

/* ── CTA SECTION ─────────────────────────────── */
.about-cta {
    padding: var(--space-20) 0;
    background: var(--bg);
    text-align: center;
}

.about-cta__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.about-cta__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    margin-bottom: var(--space-8);
    max-width: 500px;
    margin-inline: auto;
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="about-hero__content reveal">
            <span class="about-hero__badge">About Us</span>
            <h1 class="about-hero__title">Building the future of architectural design</h1>
            <p class="about-hero__subtitle">
                We're on a mission to make professional blueprint design accessible to every architect, 
                engineer, and designer — no expensive software required.
            </p>
        </div>
    </div>
</section>

<!-- Our Story with Video -->
<section class="story-section">
    <div class="container">
        <div class="story-grid">
            <div class="story-content reveal">
                <span class="story-content__badge">Our Story</span>
                <h2 class="story-content__title">From frustration to innovation</h2>
                <p class="story-content__text">
                    ConstructHub was born in 2022 when our founders, both architects, grew tired of expensive, 
                    clunky CAD software that required endless updates and licenses.
                </p>
                <p class="story-content__text">
                    They envisioned a world where architects could collaborate in real-time, share their work 
                    instantly, and access professional-grade tools from any device — for free.
                </p>
                <blockquote class="story-content__quote">
                    "Architecture should be about creativity, not software licenses."
                </blockquote>
            </div>
            <div class="story-video reveal">
                <x-video-player 
                    youtubeId="dQw4w9WgXcQ"
                    poster="https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=800&h=450&fit=crop"
                    title="Our Story"
                />
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <x-stat-counter value="50000" suffix="+" label="Blueprints Created" icon="file-text" />
            <x-stat-counter value="12000" suffix="+" label="Active Users" icon="users" />
            <x-stat-counter value="85" suffix="+" label="Countries" icon="globe" />
            <x-stat-counter value="99.9" suffix="%" label="Uptime" icon="shield-check" />
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section">
    <div class="container">
        <div class="values-section__header reveal">
            <h2 class="values-section__title">Our values</h2>
            <p class="values-section__subtitle">
                The principles that guide everything we build.
            </p>
        </div>

        <div class="values-grid stagger">
            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Collaboration First</h3>
                <p class="value-card__description">
                    Great buildings are built by teams. We design every feature with collaboration at its core.
                </p>
            </div>

            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="unlock" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Accessibility</h3>
                <p class="value-card__description">
                    Professional tools shouldn't require a professional budget. ConstructHub is free to start.
                </p>
            </div>

            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Speed & Simplicity</h3>
                <p class="value-card__description">
                    No downloads, no installations. Open your browser and start designing in seconds.
                </p>
            </div>

            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="shield" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Security & Privacy</h3>
                <p class="value-card__description">
                    Your blueprints are your intellectual property. We use enterprise-grade encryption.
                </p>
            </div>

            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="heart" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">User-Centric</h3>
                <p class="value-card__description">
                    Every feature is designed with real architects and engineers in mind.
                </p>
            </div>

            <div class="value-card glow-card reveal">
                <div class="value-card__icon">
                    <i data-lucide="refresh-cw" class="w-6 h-6"></i>
                </div>
                <h3 class="value-card__title">Continuous Innovation</h3>
                <p class="value-card__description">
                    We ship improvements weekly based on user feedback and industry needs.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="timeline-section">
    <div class="container">
        <div class="timeline-section__header reveal">
            <h2 class="timeline-section__title">Our journey</h2>
            <p class="timeline-section__subtitle">
                From a simple idea to a platform used by thousands of architects worldwide.
            </p>
        </div>

        <div class="timeline-wrapper">
            <x-timeline :items="[
                ['year' => '2022', 'title' => 'The Idea', 'description' => 'Two architects frustrated with expensive CAD software decide to build something better.', 'icon' => 'lightbulb'],
                ['year' => '2023', 'title' => 'First Launch', 'description' => 'ConstructHub beta launches with 500 early adopters testing the platform.', 'icon' => 'rocket'],
                ['year' => '2023', 'title' => 'Real-Time Collaboration', 'description' => 'We introduce real-time collaboration, allowing teams to design together.', 'icon' => 'users'],
                ['year' => '2024', 'title' => '10K Users', 'description' => 'ConstructHub reaches 10,000 active users across 50 countries.', 'icon' => 'trending-up'],
                ['year' => '2025', 'title' => 'Enterprise Launch', 'description' => 'We launch ConstructHub Enterprise for large architecture firms.', 'icon' => 'building'],
                ['year' => '2026', 'title' => 'The Future', 'description' => 'AI-assisted design, 3D visualization, and much more on the horizon.', 'icon' => 'sparkles'],
            ]" />
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <div class="team-section__header reveal">
            <h2 class="team-section__title">Meet the team</h2>
            <p class="team-section__subtitle">
                A diverse group of architects, engineers, and designers building the future of design.
            </p>
        </div>

        <div class="team-grid stagger">
            <x-team-member 
                name="Alex Chen"
                role="Co-Founder & CEO"
                image="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=280&h=280&fit=crop"
                bio="Former architect at Foster + Partners. 15 years of experience in sustainable design."
                linkedin="https://linkedin.com"
                twitter="https://twitter.com"
            />
            <x-team-member 
                name="Sarah Mitchell"
                role="Co-Founder & CTO"
                image="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=280&h=280&fit=crop"
                bio="Ex-Google engineer. Passionate about building tools that empower creators."
                linkedin="https://linkedin.com"
            />
            <x-team-member 
                name="James Wilson"
                role="Head of Design"
                image="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=280&h=280&fit=crop"
                bio="Award-winning UX designer. Previously led design at Figma."
                linkedin="https://linkedin.com"
                twitter="https://twitter.com"
            />
            <x-team-member 
                name="Maria Garcia"
                role="Head of Product"
                image="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=280&h=280&fit=crop"
                bio="10+ years in product management. Former PM at Autodesk."
                linkedin="https://linkedin.com"
            />
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="about-cta">
    <div class="container">
        <div class="reveal">
            <h2 class="about-cta__title">Join our growing community</h2>
            <p class="about-cta__subtitle">
                Start designing blueprints with thousands of architects and engineers worldwide.
            </p>
            <a href="{{ route('register') }}" class="btn btn--primary btn--xl btn-glow">
                Get started for free
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</section>
@endsection
