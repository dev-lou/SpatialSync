<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ConstructHub') — 3D House Builder</title>
    <meta name="description" content="@yield('description', 'Build houses and buildings together with real-time collaboration. Perfect for anyone who wants to create their dream home.')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400|jetbrains-mono:400,500">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/effects.css') }}">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Styles -->
    @stack('styles')
</head>
<body class="font-body antialiased">
    <!-- Skip Link -->
    <a href="#main-content" class="skip-link">
        Skip to main content
    </a>

    <!-- Navigation -->
    <x-navbar />

    <!-- Main Content -->
    <main id="main-content" class="page__content">
        @yield('content')
    </main>

    <!-- Footer -->
    <x-footer />

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
            @if(session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif
            @if(session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
        });
    </script>

    <!-- Scripts -->
    @stack('scripts')
    
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
