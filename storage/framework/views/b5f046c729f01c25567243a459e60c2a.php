<?php $__env->startSection('title', 'About'); ?>
<?php $__env->startSection('description', 'Learn about SpatialSync — a collaborative 3D building design platform built by ISUFST students.'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── ABOUT PAGE STYLES ───────────────────────── */
.about-hero {
    position: relative;
    padding: calc(80px + var(--space-8)) 0 var(--space-20);
    background: radial-gradient(circle at 50% -20%, var(--accent-muted) 0%, var(--bg) 60%);
    overflow: hidden;
    border-bottom: 1px solid var(--border-default);
}

.about-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 50%, rgba(0, 102, 255, 0.06) 0%, transparent 50%),
        radial-gradient(circle at 80% 30%, rgba(129, 140, 248, 0.05) 0%, transparent 40%);
    pointer-events: none;
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
    font-size: clamp(3rem, 7vw, 5rem);
    font-weight: 900;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    line-height: 1.05;
}

.about-hero__title span {
    background: linear-gradient(to right, var(--accent), #9333EA, var(--accent));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 4s linear infinite;
}

@keyframes text-shine {
    to { background-position: 200% center; }
}

.about-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.7;
    max-width: 600px;
    margin: 0 auto;
}

/* ── STORY ───────────────────────────────────── */
.story-section {
    padding: var(--space-24) 0;
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

.story-image {
    border-radius: var(--radius-xl);
    overflow: hidden;
    box-shadow: var(--shadow-xl);
}

.story-image img {
    width: 100%;
    height: auto;
    display: block;
}

/* ── VALUES ──────────────────────────────────── */
.values-section {
    padding: var(--space-24) 0;
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
    .values-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
    .values-grid { grid-template-columns: repeat(3, 1fr); }
}

.value-card {
    padding: var(--space-8);
    background: rgba(255, 255, 255, 0.02);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-default);
    border-radius: 32px;
    box-shadow: var(--shadow-sm);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
    position: relative;
    overflow: hidden;
}

.value-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.value-card:hover {
    border-color: var(--accent);
    box-shadow: 0 20px 40px -10px rgba(0, 102, 255, 0.15);
    transform: translateY(-8px);
}

.value-card:hover::before {
    opacity: 1;
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

/* ── TECH STACK ──────────────────────────────── */
.tech-section {
    padding: var(--space-24) 0;
    background: var(--bg);
}

.tech-section__header {
    text-align: center;
    max-width: 640px;
    margin: 0 auto var(--space-12);
}

.tech-section__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.tech-section__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

.tech-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-4);
    max-width: 800px;
    margin: 0 auto;
}

@media (min-width: 768px) {
    .tech-grid { grid-template-columns: repeat(4, 1fr); }
}

.tech-item {
    text-align: center;
    padding: var(--space-8) var(--space-6);
    background: var(--bg-secondary);
    border: 1px solid var(--border-default);
    border-radius: 28px;
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.tech-item:hover {
    border-color: var(--accent);
    background: var(--surface);
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1);
}

.tech-item__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin: 0 auto var(--space-3);
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
}

.tech-item__name {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-primary);
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
            <h2 class="tech-section__title">Built with modern technology</h2>
            <p class="tech-section__subtitle">
                Industry-grade tools powering every part of the platform.
            </p>
        </div>

        <div class="tech-grid">
            <div class="tech-item gs-fade">
                <div class="tech-item__icon">
                    <i data-lucide="code-2" class="w-6 h-6"></i>
                </div>
                <span class="tech-item__name">Laravel</span>
            </div>
            <div class="tech-item gs-fade">
                <div class="tech-item__icon">
                    <i data-lucide="box" class="w-6 h-6"></i>
                </div>
                <span class="tech-item__name">Three.js</span>
            </div>
            <div class="tech-item gs-fade">
                <div class="tech-item__icon">
                    <i data-lucide="database" class="w-6 h-6"></i>
                </div>
                <span class="tech-item__name">Supabase</span>
            </div>
            <div class="tech-item gs-fade">
                <div class="tech-item__icon">
                    <i data-lucide="radio" class="w-6 h-6"></i>
                </div>
                <span class="tech-item__name">Real-Time Sync</span>
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
            <a href="<?php echo e(route('register')); ?>" class="btn btn--xl">
                Get started for free
                <i data-lucide="arrow-right" class="w-5 h-5"></i>
            </a>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\flow\resources\views/about.blade.php ENDPATH**/ ?>