<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'SpatialSync'); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="<?php echo e(asset('css/tokens.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/components.css')); ?>">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: var(--bg);
            margin: 0;
        }

        .auth-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ── LEFT PANE: FORM ───────────────────────────── */
        .auth-pane-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: var(--space-8);
            max-width: 100%;
            position: relative;
            background: var(--bg);
            animation: pane-in-left 0.8s var(--ease-out);
        }

        @keyframes pane-in-left {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (min-width: 1024px) {
            .auth-pane-form {
                flex: 0 0 520px;
                padding: var(--space-8) min(80px, var(--space-16));
            }
        }

        .auth-form-wrapper {
            margin: auto 0;
            width: 100%;
            max-width: 400px;
            align-self: center;
        }

        /* ── RIGHT PANE: VISUAL ────────────────────────── */
        .auth-pane-visual {
            display: none;
            flex: 1;
            position: relative;
            background: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #0f172a 100%);
            overflow: hidden;
            padding: var(--space-12);
        }

        @media (min-width: 1024px) {
            .auth-pane-visual {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        .auth-pane-visual::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 30%, rgba(139, 92, 246, 0.08) 0%, transparent 40%);
            pointer-events: none;
        }

        /* Mesh Background Grid */
        .auth-mesh {
            position: absolute;
            inset: 0;
            background-size: 40px 40px;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            perspective: 1000px;
            transform-style: preserve-3d;
            transform: rotateX(60deg) translateY(-100px) translateZ(-200px);
            opacity: 0.5;
        }

        .auth-glass-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: var(--space-12);
            border-radius: 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
            max-width: 480px;
            overflow: hidden;
        }

        .auth-glass-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(125deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            opacity: 0.5;
            pointer-events: none;
        }

        .auth-header {
            margin-bottom: var(--space-10);
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent) 0%, #8B5CF6 100%);
            border-radius: 14px;
            color: #fff;
            margin-bottom: var(--space-8);
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .auth-logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.4);
        }

        .auth-title {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 3.2rem);
            font-weight: 900;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
            line-height: 1;
        }

        .auth-title span {
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

        .auth-subtitle {
            font-size: var(--text-base);
            color: var(--text-secondary);
            line-height: 1.6;
            opacity: 0;
            animation: fade-in 0.6s var(--ease-out) 0.3s forwards;
        }

        @keyframes fade-in {
            to { opacity: 1; }
        }

        .auth-form-stagger > * {
            opacity: 0;
            transform: translateY(12px);
            animation: fade-up 0.5s var(--ease-out) forwards;
        }

        @keyframes fade-up {
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            padding-top: var(--space-6);
            border-top: 1px solid var(--border-default);
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-layout">
        <!-- Left: Form -->
        <main class="auth-pane-form">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <a href="<?php echo e(route('home')); ?>" class="auth-logo">
                        <i data-lucide="box" class="w-7 h-7"></i>
                    </a>
                    <h1 class="auth-title">
                        <?php
                            $title = View::getSection('title', 'Welcome Back');
                            $parts = explode(' ', $title, 2);
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($parts) > 1): ?>
                            <?php echo e($parts[0]); ?> <span><?php echo e($parts[1]); ?></span>
                        <?php else: ?>
                            <?php echo e($title); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h1>
                    <?php if (! empty(trim($__env->yieldContent('subtitle')))): ?>
                        <p class="auth-subtitle"><?php echo $__env->yieldContent('subtitle'); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="auth-form-stagger">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </main>

        <!-- Right: Visual -->
        <aside class="auth-pane-visual">
            <div class="auth-mesh"></div>
            
            <div class="auth-glass-card">
                <div style="margin-bottom: var(--space-6);">
                    <i data-lucide="layers" style="width: 32px; height: 32px; color: var(--accent);"></i>
                </div>
                <h2 style="font-family: var(--font-display); font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: var(--space-4); line-height: 1.1; letter-spacing: -0.02em;">
                    Design the <span style="background: linear-gradient(to right, var(--accent), #A855F7); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Impossible.</span>
                </h2>
                <p style="font-size: 1.125rem; color: rgba(255,255,255,0.7); line-height: 1.6; max-width: 400px;">
                    Join the world's most advanced spatial design engine. Built for the next generation of architects and builders to collaborate in real-time.
                </p>
                
                <div style="display: flex; align-items: center; gap: var(--space-4); margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; align-items: center;">
                        <!-- Avatar Stack -->
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #4F46E5; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: 0; z-index: 3;">A</div>
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #059669; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: -8px; z-index: 2;">B</div>
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #D97706; border: 2px solid #1E293B; display: grid; place-items: center; color: white; font-size: 10px; font-weight: bold; margin-left: -8px; z-index: 1;">C</div>
                    </div>
                    <span style="font-size: 0.875rem; color: rgba(255,255,255,0.6); font-weight: 500;">Trusted by 10,000+ spatial engineers</span>
                </div>
            </div>
        </aside>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/layouts/auth.blade.php ENDPATH**/ ?>