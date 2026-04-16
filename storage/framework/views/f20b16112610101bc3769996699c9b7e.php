<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'ConstructHub'); ?> — 3D House Builder</title>
    <meta name="description" content="<?php echo $__env->yieldContent('description', 'Build houses and buildings together with real-time collaboration. Perfect for anyone who wants to create their dream home.'); ?>">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230066FF'/><path d='M25 70 L50 30 L75 70 Z' fill='white'/></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400|jetbrains-mono:400,500">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="<?php echo e(asset('css/tokens.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/components.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/layout.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/effects.css')); ?>">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Styles -->
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="font-body antialiased">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">
        Skip to main content
    </a>

    <!-- Navigation -->
    <?php if (isset($component)) { $__componentOriginala591787d01fe92c5706972626cdf7231 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala591787d01fe92c5706972626cdf7231 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.navbar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('navbar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $attributes = $__attributesOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__attributesOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala591787d01fe92c5706972626cdf7231)): ?>
<?php $component = $__componentOriginala591787d01fe92c5706972626cdf7231; ?>
<?php unset($__componentOriginala591787d01fe92c5706972626cdf7231); ?>
<?php endif; ?>

    <!-- Main Content -->
    <main id="main-content" class="page__content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <!-- Footer -->
    <?php if (isset($component)) { $__componentOriginal8a8716efb3c62a45938aca52e78e0322 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a8716efb3c62a45938aca52e78e0322 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $attributes = $__attributesOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__attributesOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a8716efb3c62a45938aca52e78e0322)): ?>
<?php $component = $__componentOriginal8a8716efb3c62a45938aca52e78e0322; ?>
<?php unset($__componentOriginal8a8716efb3c62a45938aca52e78e0322); ?>
<?php endif; ?>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container" role="region" aria-label="Notifications"></div>

    <!-- Initialize Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Toast notification helper
            window.showToast = function(message, type = 'info') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast toast--${type}`;
                toast.innerHTML = `
                    <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info'}" class="w-5 h-5"></i>
                    <span>${message}</span>
                `;
                container.appendChild(toast);
                lucide.createIcons();

                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 300);
                }, 4000);
            };

            // Show success/error messages from session
            <?php if(session('success')): ?>
                showToast('<?php echo e(session('success')); ?>', 'success');
            <?php endif; ?>
            <?php if(session('error')): ?>
                showToast('<?php echo e(session('error')); ?>', 'error');
            <?php endif; ?>
        });
    </script>

    <!-- Scripts -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <!-- Scroll Reveal Observer -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.reveal').forEach(function(el) {
            observer.observe(el);
        });
    });
    </script>

    <!-- Number Counter Animation -->
    <script>
    (function() {
        // Format number with commas, decimals, prefix/suffix
        function formatNumber(value, decimals, prefix, suffix, separator) {
            var fixed = value.toFixed(decimals);
            var parts = fixed.split('.');
            var integer = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, separator);
            var decimal = parts[1];
            return prefix + integer + (decimal ? '.' + decimal : '') + suffix;
        }
        
        // Easing function (easeOut cubic)
        function easeOut(t) {
            return 1 - Math.pow(1 - t, 3);
        }
        
        // Count up animation
        function countUp(element, endValue, options) {
            options = options || {};
            var duration = options.duration || 2000;
            var decimals = options.decimals || 0;
            var prefix = options.prefix || '';
            var suffix = options.suffix || '';
            var separator = options.separator || ',';
            
            // Check reduced motion
            var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            if (prefersReducedMotion) {
                element.textContent = formatNumber(endValue, decimals, prefix, suffix, separator);
                element.setAttribute('data-counted', 'true');
                return;
            }
            
            var startTime = performance.now();
            var startValue = 0;
            
            function animate(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var easedProgress = easeOut(progress);
                var currentValue = startValue + (endValue - startValue) * easedProgress;
                
                element.textContent = formatNumber(currentValue, decimals, prefix, suffix, separator);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    element.setAttribute('data-counted', 'true');
                }
            }
            
            requestAnimationFrame(animate);
        }
        
        // Initialize counters with Intersection Observer
        function initCounters() {
            var counters = document.querySelectorAll('[data-count-to]');
            
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var element = entry.target;
                        
                        if (element.getAttribute('data-counted') === 'true') return;
                        
                        var endValue = parseFloat(element.dataset.countTo || '0');
                        var duration = parseInt(element.dataset.countDuration || '2000', 10);
                        var decimals = parseInt(element.dataset.countDecimals || '0', 10);
                        var prefix = element.dataset.countPrefix || '';
                        var suffix = element.dataset.countSuffix || '';
                        
                        countUp(element, endValue, {
                            duration: duration,
                            decimals: decimals,
                            prefix: prefix,
                            suffix: suffix
                        });
                        
                        observer.unobserve(element);
                    }
                });
            }, {
                threshold: 0.5,
                rootMargin: '0px 0px -10% 0px'
            });
            
            counters.forEach(function(counter) {
                observer.observe(counter);
            });
        }
        
        document.addEventListener('DOMContentLoaded', initCounters);
    })();
    </script>

    <!-- Glow Card Mouse Tracking -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var glowCards = document.querySelectorAll('.glow-card');
        
        glowCards.forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                var rect = card.getBoundingClientRect();
                var x = ((e.clientX - rect.left) / rect.width) * 100;
                var y = ((e.clientY - rect.top) / rect.height) * 100;
                card.style.setProperty('--mouse-x', x + '%');
                card.style.setProperty('--mouse-y', y + '%');
            });
        });
    });
    </script>

    <!-- 3D Tilt Card Effect -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var tiltCards = document.querySelectorAll('.tilt-card');
        
        tiltCards.forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                var rect = card.getBoundingClientRect();
                var x = (e.clientX - rect.left) / rect.width;
                var y = (e.clientY - rect.top) / rect.height;
                var tiltX = (y - 0.5) * 10;
                var tiltY = (x - 0.5) * -10;
                card.style.setProperty('--tilt-x', tiltX + 'deg');
                card.style.setProperty('--tilt-y', tiltY + 'deg');
            });
            
            card.addEventListener('mouseleave', function() {
                card.style.setProperty('--tilt-x', '0deg');
                card.style.setProperty('--tilt-y', '0deg');
            });
        });
    });
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/layouts/app.blade.php ENDPATH**/ ?>