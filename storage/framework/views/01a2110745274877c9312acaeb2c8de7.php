<?php $__env->startSection('title', 'SpatialSync'); ?>
<?php $__env->startSection('description', 'Design buildings in 3D with your team in real-time. Place walls, doors, roofs and more — collaborate live from any browser.'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* The frame viewport — fits exactly below the navbar */
    .scrolly-viewport {
        width: 100%;
        height: calc(100vh - var(--header-height));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 12px;
        box-sizing: border-box;
        background: var(--bg-primary);
    }

    /* Rounded 3D frame */
    .scrolly-frame {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 24px;
        overflow: hidden;
        background: #000;
        box-shadow: 0 8px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.06);
    }

    #scrolly-canvas { width: 100%; height: 100%; display: block; }

    /* ── TEXT OVERLAYS ────────────────────────────── */
    .narrative-tier {
        position: absolute; inset: 0;
        display: flex; flex-direction: column; justify-content: center;
        padding: 0 clamp(32px, 5vw, 80px);
        opacity: 0; z-index: 10; pointer-events: none;
        transition: opacity 0.4s ease, transform 0.4s ease;
    }
    .narrative-tier--right { align-items: flex-end; text-align: right; }
    .narrative-tier--center { align-items: center; text-align: center; }
    .narrative-tier__content {
        max-width: 600px; pointer-events: auto;
    }
    .narrative-tier__glass {
        background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.01) 100%);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: 40px 48px;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.15);
        box-shadow: inset 0 0 20px rgba(255,255,255,0.04), 0 8px 32px rgba(0,0,0,0.3);
    }

    .narrative-tier__eyebrow {
        text-transform: uppercase; letter-spacing: 0.25em; font-size: 12px;
        font-weight: 800; color: #38BDF8; margin-bottom: 1.5rem;
        display: block; 
        text-shadow: 0 2px 8px rgba(0,0,0,0.8), 0 0 20px rgba(56,189,248,0.4);
    }
    .narrative-tier__title {
        font-family: var(--font-display);
        font-size: clamp(3rem, 6vw, 5.5rem);
        line-height: 1.05; font-weight: 600; color: #fff;
        margin-bottom: 1.25rem; letter-spacing: -0.03em;
        text-shadow: 0 4px 30px rgba(0,0,0,0.8), 0 1px 6px rgba(0,0,0,0.9);
    }
    .hero__title-accent {
        color: #38BDF8;
        padding-right: 0.1em;
        text-shadow: 0 4px 30px rgba(0,0,0,0.8), 0 1px 6px rgba(0,0,0,0.9);
    }
    .narrative-tier__desc {
        font-size: clamp(1rem, 1.8vw, 1.25rem);
        color: rgba(255,255,255,0.85); max-width: 500px; line-height: 1.7;
        text-shadow: 0 2px 10px rgba(0,0,0,0.8);
    }
    .tier-3-bg {
        position: absolute; inset: 0; opacity: 0;
        background: radial-gradient(ellipse at center, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.6) 100%);
        z-index: 5; pointer-events: none;
        transition: opacity 0.4s ease;
    }

    /* ── STORY DOTS ──────────────────────────────── */
    .story-progress {
        position: absolute; right: -28px; top: 50%;
        transform: translateY(-50%);
        display: flex; flex-direction: column; gap: 1.5rem; z-index: 100;
    }
    .story-dot {
        width: 5px; height: 5px; background: rgba(0,0,0,0.12);
        border-radius: 50%; transition: all 0.4s ease;
    }
    .story-dot.active {
        background: var(--accent); transform: scale(1.8);
        box-shadow: 0 0 10px var(--accent);
    }

    /* ── LOADER ───────────────────────────────────── */
    .spatial-loader {
        position: fixed; inset: 0;
        background: radial-gradient(circle at center, #fff 0%, #f0f4f8 100%);
        z-index: 9999;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 2.5rem;
        transition: opacity 0.8s ease;
    }
    .spatial-loader.done { opacity: 0; pointer-events: none; }
    .loader-orb {
        width: 6px; height: 6px; background: var(--accent); border-radius: 50%;
        box-shadow: 0 0 30px 20px rgba(0, 102, 255, 0.12);
        animation: pulse-orb 2s ease-in-out infinite;
    }
    @keyframes pulse-orb {
        0%, 100% { transform: scale(1); opacity: 0.8; }
        50% { transform: scale(1.6); opacity: 1; }
    }
    .loader-text {
        font-family: var(--font-display); font-size: 1rem; color: #1e293b;
        font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase;
    }
    .loader-bar-track {
        width: 200px; height: 2px; background: rgba(0,0,0,0.08);
        border-radius: 4px; overflow: hidden;
    }
    .loader-bar-fill { height: 100%; background: var(--accent); transition: width 0.3s ease; }

    /* ── SIGN IN BUTTON ──────────────────────────── */
    .btn--ghost-dark {
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; border: 1.5px solid rgba(255,255,255,0.35);
        background: rgba(255,255,255,0.08); backdrop-filter: blur(8px);
        padding: 0.75rem 2rem; border-radius: var(--radius-full);
        font-weight: 600; font-size: 1rem; cursor: pointer; text-decoration: none;
        transition: background 0.3s ease, color 0.3s ease;
    }
    .btn--ghost-dark:hover {
        background: rgba(255,255,255,0.95); color: #0a0a0a; border-color: transparent;
    }

    /* ── SCROLL PROGRESS BAR ─────────────────────── */
    .scroll-progress {
        position: absolute; bottom: 24px; left: 50%;
        transform: translateX(-50%);
        width: 120px; height: 3px;
        background: rgba(255,255,255,0.15);
        border-radius: 4px; overflow: hidden;
        z-index: 20;
    }
    .scroll-progress__fill {
        height: 100%; background: var(--accent);
        border-radius: 4px; width: 0%;
        transition: width 0.15s ease;
    }
    .scroll-hint {
        position: absolute; bottom: 36px; left: 50%;
        transform: translateX(-50%);
        font-size: 11px; color: rgba(255,255,255,0.5);
        text-transform: uppercase; letter-spacing: 0.2em;
        z-index: 20; pointer-events: none;
        animation: hint-fade 3s ease-in-out infinite;
    }
    @keyframes hint-fade {
        0%, 100% { opacity: 0.5; }
        50% { opacity: 1; }
    }

    [x-cloak] { display: none !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div x-data="scrollytellingEngine()" x-init="init()" x-cloak>

    <!-- Loader -->
    <div class="spatial-loader" :class="ready ? 'done' : ''">
        <div class="loader-orb"></div>
        <div class="loader-text">Initializing Spatial Sync</div>
        <div class="loader-bar-track">
            <div class="loader-bar-fill" :style="`width: ${progress}%`"></div>
        </div>
    </div>

    <!-- The Frame — never moves, fills viewport below navbar -->
    <div class="scrolly-viewport">
        <div class="scrolly-frame" id="scrolly-frame">
            <div class="tier-3-bg" id="tier-3-bg"></div>
            <canvas id="scrolly-canvas"></canvas>

            <div class="narrative-tier" id="tier-1">
                <div class="narrative-tier__content narrative-tier__glass">
                    <!-- Removed eyebrow text per user request -->
                    <h1 class="narrative-tier__title">Design the <br><span class="hero__title-accent">Impossible.</span></h1>
                    <p class="narrative-tier__desc">The world's first spatial design engine built for the next generation of architects and builders.</p>
                </div>
            </div>

            <div class="narrative-tier narrative-tier--right" id="tier-2">
                <div class="narrative-tier__content narrative-tier__glass">
                    <span class="narrative-tier__eyebrow">Real-Time Sync</span>
                    <h2 class="narrative-tier__title">Build with <br><span class="hero__title-accent">your team.</span></h2>
                    <p class="narrative-tier__desc">Every click, every wall, every room — synchronized live. Zero lag, pure creation.</p>
                </div>
            </div>

            <div class="narrative-tier narrative-tier--center" id="tier-3">
                <div class="narrative-tier__content">
                    <h2 class="narrative-tier__title">Ready to build <br>the future?</h2>
                    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;pointer-events:auto;">
                        <a href="<?php echo e(route('register')); ?>" class="btn btn--primary btn--lg">Start Building Free</a>
                        <a href="<?php echo e(route('login')); ?>" class="btn--ghost-dark">Sign In</a>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="scroll-hint" x-show="!finished">Scroll to explore</div>
            <div class="scroll-progress">
                <div class="scroll-progress__fill" :style="`width: ${frameProgress}%`"></div>
            </div>

            <!-- Story dots -->
            <div class="story-progress">
                <div class="story-dot" :class="currentTier === 1 ? 'active' : ''"></div>
                <div class="story-dot" :class="currentTier === 2 ? 'active' : ''"></div>
                <div class="story-dot" :class="currentTier === 3 ? 'active' : ''"></div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function scrollytellingEngine() {
    return {
        ready: false,
        progress: 0,
        frameCount: 340,
        currentFrame: 0,
        currentTier: 1,
        frameProgress: 0,
        finished: false,
        images: [],
        canvas: null,
        ctx: null,
        _wheelHandler: null,
        _touchHandler: null,
        _accumulator: 0,

        async init() {
            this.canvas = document.getElementById('scrolly-canvas');
            this.ctx = this.canvas.getContext('2d');
            const loads = [];

            for (let i = 0; i < 160; i++) {
                const img = new Image();
                img.src = `/img/sequence/frame_${i.toString().padStart(3,'0')}_delay-0.05s.webp`;
                loads.push(this._load(img));
                this.images.push(img);
            }
            for (let i = 0; i < 180; i++) {
                const img = new Image();
                img.src = `/img/sequence/part2/frame_${i.toString().padStart(3,'0')}_delay-0.05s.webp`;
                loads.push(this._load(img));
                this.images.push(img);
            }

            await Promise.all(loads);
            this._resize();
            window.addEventListener('resize', () => this._resize());
            this._draw(0);
            this.ready = true;

            this._lock();

            // Show chapter 1 immediately
            document.getElementById('tier-1').style.opacity = '1';
            document.getElementById('tier-1').style.transform = 'translateY(0)';

            // Listen for wheel events to advance frames
            this._wheelHandler = (e) => {
                if (this.finished) {
                    // Relock if we scrolled back up to the top
                    if (window.scrollY <= 0 && e.deltaY < 0) {
                        e.preventDefault();
                        this._lock();
                    } else {
                        return; // Yield to native scrolling down
                    }
                } else {
                    e.preventDefault(); // Lock the page and eat the scroll
                }

                // Accumulate wheel delta, ~120 per scroll tick
                this._accumulator += e.deltaY;
                const step = Math.sign(this._accumulator) * Math.floor(Math.abs(this._accumulator) / 40);

                if (step !== 0) {
                    this._accumulator -= step * 40;
                    this.currentFrame = Math.max(0, Math.min(this.frameCount - 1, this.currentFrame + step));
                    this._draw(this.currentFrame);
                    this.frameProgress = (this.currentFrame / (this.frameCount - 1)) * 100;
                    this._updateChapters(this.currentFrame / (this.frameCount - 1));

                    // Unlock when we reach the last frame and keep scrolling down
                    if (this.currentFrame >= this.frameCount - 1 && e.deltaY > 0) {
                        this._unlock();
                    }
                }
            };

            // Touch support
            let lastTouchY = 0;
            this._touchStartHandler = (e) => { lastTouchY = e.touches[0].clientY; };
            this._touchMoveHandler = (e) => {
                const dy = lastTouchY - e.touches[0].clientY;
                if (this.finished) {
                    if (window.scrollY <= 0 && dy < 0) {
                        e.preventDefault();
                        this._lock();
                    } else {
                        lastTouchY = e.touches[0].clientY;
                        return;
                    }
                } else {
                    e.preventDefault();
                }
                
                lastTouchY = e.touches[0].clientY;
                this._accumulator += dy * 3;
                const step = Math.sign(this._accumulator) * Math.floor(Math.abs(this._accumulator) / 40);
                if (step !== 0) {
                    this._accumulator -= step * 40;
                    this.currentFrame = Math.max(0, Math.min(this.frameCount - 1, this.currentFrame + step));
                    this._draw(this.currentFrame);
                    this.frameProgress = (this.currentFrame / (this.frameCount - 1)) * 100;
                    this._updateChapters(this.currentFrame / (this.frameCount - 1));
                    
                    if (this.currentFrame >= this.frameCount - 1 && dy > 0) {
                        this._unlock();
                    }
                }
            };

            window.addEventListener('wheel', this._wheelHandler, { passive: false });
            window.addEventListener('touchstart', this._touchStartHandler, { passive: true });
            window.addEventListener('touchmove', this._touchMoveHandler, { passive: false });
        },

        _lock() {
            this.finished = false;
            document.body.style.overflow = 'hidden';
            window.scrollTo({ top: 0, behavior: 'instant' });
            this._accumulator = 0;
        },

        _unlock() {
            this.finished = true;
            document.body.style.overflow = '';
            // Deliberately NOT removing event listeners here.
            // This allows us to yield to native scrolling down to the footer,
            // while still monitoring if the user scrolls back up to the top.
        },

        _load(img) {
            return new Promise(r => {
                img.onload = () => {
                    this.progress = Math.round(((this.images.indexOf(img) + 1) / this.frameCount) * 100);
                    r();
                };
                img.onerror = () => r();
            });
        },

        _resize() {
            const f = document.getElementById('scrolly-frame');
            if (!f) return;
            this.canvas.width = f.clientWidth * devicePixelRatio;
            this.canvas.height = f.clientHeight * devicePixelRatio;
            this._draw(this.currentFrame);
        },

        _draw(idx) {
            const img = this.images[idx];
            if (!img || !img.width) return;
            const cw = this.canvas.width, ch = this.canvas.height;
            const ia = img.width / img.height, ca = cw / ch;
            let w, h, x, y;
            if (ca > ia) { w = cw; h = cw / ia; x = 0; y = (ch - h) / 2; }
            else          { w = ch * ia; h = ch; x = (cw - w) / 2; y = 0; }
            this.ctx.clearRect(0, 0, cw, ch);
            this.ctx.drawImage(img, x, y, w, h);
        },

        _updateChapters(p) {
            const t1 = document.getElementById('tier-1');
            const t2 = document.getElementById('tier-2');
            const t3 = document.getElementById('tier-3');
            const bg = document.getElementById('tier-3-bg');

            // Chapter 1: visible 0–20%, fades 20–30%
            if (p < 0.20) {
                t1.style.opacity = '1'; t1.style.transform = 'translateY(0)';
                this.currentTier = 1;
            } else if (p < 0.30) {
                const f = 1 - (p - 0.20) / 0.10;
                t1.style.opacity = f; t1.style.transform = `translateY(${-(1-f)*40}px)`;
            } else {
                t1.style.opacity = '0';
            }

            // Chapter 2: in 35–45%, hold 45–60%, out 60–70%
            if (p < 0.35 || p > 0.70) {
                t2.style.opacity = '0';
            } else if (p < 0.45) {
                const f = (p - 0.35) / 0.10;
                t2.style.opacity = f; t2.style.transform = `translateY(${(1-f)*40}px)`;
                this.currentTier = 2;
            } else if (p < 0.60) {
                t2.style.opacity = '1'; t2.style.transform = 'translateY(0)';
                this.currentTier = 2;
            } else {
                const f = 1 - (p - 0.60) / 0.10;
                t2.style.opacity = f; t2.style.transform = `translateY(${-(1-f)*40}px)`;
            }

            // Chapter 3: in 78–88%, holds to end
            if (p < 0.78) {
                t3.style.opacity = '0'; bg.style.opacity = '0';
            } else if (p < 0.88) {
                const f = (p - 0.78) / 0.10;
                t3.style.opacity = f; t3.style.transform = `translateY(${(1-f)*30}px)`;
                bg.style.opacity = f;
                this.currentTier = 3;
            } else {
                t3.style.opacity = '1'; t3.style.transform = 'translateY(0)';
                bg.style.opacity = '1';
                this.currentTier = 3;
            }
        }
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\flow\resources\views/home.blade.php ENDPATH**/ ?>