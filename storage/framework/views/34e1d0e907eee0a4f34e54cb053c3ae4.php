<?php $__env->startSection('title', 'Features'); ?>
<?php $__env->startSection('description', 'Explore the powerful features of SpatialSync — 3D spatial canvas, real-time collaboration, multi-floor design, issue tracking, and more.'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── FEATURES HERO ───────────────────────────── */
.features-hero {
    position: relative;
    padding: calc(80px + var(--space-8)) 0 var(--space-20);
    background: radial-gradient(circle at 50% -20%, var(--accent-muted) 0%, var(--bg) 60%);
    overflow: hidden;
    border-bottom: 1px solid var(--border-default);
}

.features-hero__content {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 720px;
    margin: 0 auto;
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
    border: 1px solid rgba(0, 102, 255, 0.15);
}

.features-hero__title {
    font-family: var(--font-display);
    font-size: clamp(3rem, 7vw, 5rem);
    font-weight: 900;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    margin-bottom: var(--space-6);
    line-height: 1.05;
}

.features-hero__title span {
    background: linear-gradient(to right, var(--accent), #9333EA, var(--accent));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 4s linear infinite;
}

.features-hero__subtitle {
    font-size: var(--text-xl);
    color: var(--text-secondary);
    line-height: 1.7;
    max-width: 600px;
    margin: 0 auto;
}

/* ── SHOWCASE (alternating left/right) ────────── */
.showcase {
    padding: var(--space-24) 0;
    background: var(--bg-secondary);
}

.showcase--alt { background: var(--bg); }

.showcase__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-12);
    align-items: center;
}

@media (min-width: 1024px) {
    .showcase__grid { grid-template-columns: 1fr 1fr; }
    .showcase--reverse .showcase__grid { direction: rtl; }
    .showcase--reverse .showcase__grid > * { direction: ltr; }
}

.showcase__img-wrap {
    position: relative;
    border-radius: 32px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.08); /* Modern diffuse shadow */
    border: 1px solid rgba(0, 102, 255, 0.1);
    transform: perspective(1000px) rotateY(0deg);
    transition: transform var(--dur-slow) cubic-bezier(0.16, 1, 0.3, 1), box-shadow var(--dur-slow) ease;
}

.showcase__img-wrap:hover {
    transform: perspective(1000px) translateY(-8px);
    box-shadow: 0 32px 80px rgba(0, 102, 255, 0.12); /* Branded glow */
}

.showcase__img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.showcase__img-wrap:hover .showcase__img { transform: scale(1.04); }

.showcase__img-badge {
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

.showcase__img-badge i { color: var(--success); }

.showcase__text { max-width: 520px; }

.showcase__badge {
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

.showcase__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.showcase__desc {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: var(--space-6);
}

.showcase__list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.showcase__list li {
    display: flex;
    align-items: flex-start;
    gap: var(--space-3);
    font-size: var(--text-base);
    color: var(--text-secondary);
}

.showcase__list li i {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    color: var(--success);
    margin-top: 3px;
}

/* ── EXTRAS GRID ─────────────────────────────── */
.extras {
    padding: var(--space-24) 0;
    background: var(--bg-secondary);
}

.extras__header {
    text-align: center;
    margin-bottom: var(--space-12);
}

.extras__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
    margin-bottom: var(--space-4);
}

.extras__subtitle {
    font-size: var(--text-lg);
    color: var(--text-secondary);
}

.extras__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .extras__grid { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
    .extras__grid { grid-template-columns: repeat(3, 1fr); }
}

.ecard {
    padding: var(--space-8);
    background: var(--surface);
    border: 1px solid rgba(0, 102, 255, 0.08);
    border-radius: 24px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.ecard:hover {
    background: var(--bg);
    border-color: rgba(0, 102, 255, 0.3);
    box-shadow: 0 20px 40px rgba(0, 102, 255, 0.06);
    transform: translateY(-8px);
}

.ecard__icon {
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

.ecard:hover .ecard__icon { transform: scale(1.1); }

.ecard__title {
    font-size: var(--text-lg);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.ecard__desc {
    font-size: var(--text-sm);
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
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
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
                    <li><i data-lucide="check" class="w-5 h-5"></i> Walls, floors, roofs, doors, windows, stairs</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Color and material customization per object</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Multi-floor support (up to 10 floors)</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Snap-to-grid with rotation controls</li>
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
                    <li><i data-lucide="check" class="w-5 h-5"></i> Live multiplayer editing via Supabase Realtime</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Built-in real-time chat</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Invite by email search or share via link</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Owner / Editor / Viewer role management</li>
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
                    <li><i data-lucide="check" class="w-5 h-5"></i> Pin issues to specific 3D objects</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Status tracking: Open → In Progress → Resolved</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Priority levels (Low, Medium, High, Critical)</li>
                    <li><i data-lucide="check" class="w-5 h-5"></i> Visual issue pins on the 3D canvas</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Extras — unique to Features page, not on Home -->
<section class="extras">
    <div class="container">
        <div class="extras__header gs-fade">
            <span class="showcase__badge" style="margin-bottom: var(--space-4); display: inline-block;">Platform Extras</span>
            <h2 class="extras__title">Built-in tools that complete the workflow</h2>
            <p class="extras__subtitle">
                Beyond the core editor — everything else that makes SpatialSync a full platform.
            </p>
        </div>

        <div class="extras__grid">
            <div class="ecard gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="globe" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">100% Browser-Based</h3>
                <p class="ecard__desc">
                    No downloads, no installations. SpatialSync runs entirely in your browser on any modern device.
                </p>
            </div>

            <div class="ecard gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="cloud" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">Cloud Auto-Save</h3>
                <p class="ecard__desc">
                    All builds are securely stored in Supabase. Auto-save ensures you never lose progress.
                </p>
            </div>

            <div class="ecard gs-fade">
                <div class="ecard__icon">
                    <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                </div>
                <h3 class="ecard__title">Smart Dashboard</h3>
                <p class="ecard__desc">
                    Personal builds and shared projects separated. Activity feed, member avatars, and quick start setup.
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\flow\resources\views/features.blade.php ENDPATH**/ ?>