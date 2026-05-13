<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Primary Meta Tags -->
    <title>@yield('title', 'SpatialSync') — Collaborative 3D Architecture</title>
    <meta name="title" content="@yield('title', 'SpatialSync') — Collaborative 3D Architecture">
    <meta name="description" content="@yield('description', 'Design, iterate, and collaborate on premium 3D blueprints in real-time. Built for the next generation of architects.')">

    <!-- Schema.org for Google+ / Apps -->
    <meta itemprop="name" content="SpatialSync — Collaborative 3D Architecture">
    <meta itemprop="description" content="@yield('description', 'Design, iterate, and collaborate on premium 3D blueprints in real-time.')">
    <meta itemprop="image" content="{{ url('/images/og-meta.png') }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="SpatialSync">
    <meta property="og:title" content="@yield('title', 'SpatialSync') — Collaborative 3D Architecture">
    <meta property="og:description" content="@yield('description', 'Design, iterate, and collaborate on premium 3D blueprints in real-time. Built for the next generation of architects.')">
    <meta property="og:image" content="{{ url('/images/og-meta.png') }}">
    <meta property="og:image:secure_url" content="{{ url('/images/og-meta.png') }}">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:alt" content="SpatialSync — Collaborative 3D architecture and design platform">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_US">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'SpatialSync') — Collaborative 3D Architecture">
    <meta property="twitter:description" content="@yield('description', 'Design, iterate, and collaborate on premium 3D blueprints in real-time.')">
    <meta property="twitter:image" content="{{ url('/images/og-meta.png') }}">


    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230066FF'/><path d='M50 20 L80 38 L80 62 L50 80 L20 62 L20 38 Z' fill='none' stroke='white' stroke-width='5'/><path d='M50 20 L50 80 M20 38 L80 62 M80 38 L20 62' stroke='white' stroke-width='3' opacity='0.5'/></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400|jetbrains-mono:400,500">

    <!-- Vite Assets (CSS & JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Design Tokens -->
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    <link rel="stylesheet" href="{{ asset('css/effects.css') }}">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- GSAP + ScrollTrigger -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3/dist/ScrollTrigger.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global SweetAlert2 light-mode defaults for consistency
        const _SwalBase = Swal;
        window.Swal = _SwalBase.mixin({
            background: '#ffffff',
            color: '#1a1a2e',
            confirmButtonColor: '#0066FF',
            cancelButtonColor: '#64748b',
            iconColor: '#0066FF',
            customClass: {
                popup: 'swal-global-popup',
                title: 'swal-global-title',
                htmlContainer: 'swal-global-body'
            }
        });
    </script>
    <style>
        .swal-global-popup {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            border-radius: 24px !important;
            padding: 2rem !important;
            border: 1px solid rgba(0,0,0,0.06) !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12) !important;
        }
        .swal-global-title {
            font-weight: 800 !important;
            letter-spacing: -0.02em !important;
        }
        .swal-global-body {
            font-size: 1rem !important;
            line-height: 1.6 !important;
        }
    </style>

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

    <!-- ═══ FEATURE 2: PAGE TRANSITIONS ═══ -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.startViewTransition) {
            var curtain = document.createElement('div');
            curtain.className = 'page-curtain';
            document.body.appendChild(curtain);
            var links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"]):not([download]):not(.mobile-drawer a):not([x-data] a)');
            links.forEach(function(link) {
                var href = link.getAttribute('href');
                if (!href || href.startsWith('http') || href.startsWith('//')) return;
                link.addEventListener('click', function(e) {
                    if (e.metaKey || e.ctrlKey || e.shiftKey) return;
                    e.preventDefault();
                    var target = href;
                    gsap.to(curtain, { opacity: 1, duration: 0.2, ease: 'power2.in', onComplete: function() {
                        window.location.href = target;
                    }});
                });
            });
            window.addEventListener('pageshow', function() {
                gsap.to(curtain, { opacity: 0, duration: 0.3, ease: 'power2.out' });
            });
        } else {
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a:not([target="_blank"]):not([href^="#"]):not([href^="mailto:"]):not([href^="tel:"]):not([download])');
                if (!link) return;
                var href = link.getAttribute('href');
                if (!href || href.startsWith('http') || href.startsWith('//') || e.metaKey || e.ctrlKey || e.shiftKey) return;
                e.preventDefault();
                document.startViewTransition(function() {
                    window.location.href = href;
                });
            });
        }
    });
    </script>

    <!-- ═══ FEATURE 3: CUSTOM CURSOR ═══ -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (isTouchDevice || prefersReduced) return;

        var body = document.body;
        body.classList.add('custom-cursor-active');

        var cursor = document.createElement('div');
        cursor.className = 'custom-cursor';
        cursor.innerHTML = '<div class="custom-cursor__dot"></div><div class="custom-cursor__ring"></div>';
        document.body.appendChild(cursor);

        var dot = cursor.querySelector('.custom-cursor__dot');
        var ring = cursor.querySelector('.custom-cursor__ring');
        var mouseX = 0, mouseY = 0;
        var ringX = 0, ringY = 0;
        var isHidden = false;

        document.addEventListener('mousemove', function(e) {
            mouseX = e.clientX;
            mouseY = e.clientY;
            dot.style.transform = 'translate(' + mouseX + 'px, ' + mouseY + 'px) translate(-50%, -50%)';
            cursor.classList.remove('custom-cursor--hidden');
            isHidden = false;

            var target = e.target.closest('a, button, .btn, input, select, textarea, [role="button"]');
            cursor.classList.toggle('custom-cursor--interact', !!target);
        });

        document.addEventListener('mouseleave', function() {
            cursor.classList.add('custom-cursor--hidden');
            isHidden = true;
        });

        document.addEventListener('mouseenter', function() {
            cursor.classList.remove('custom-cursor--hidden');
            isHidden = false;
        });

        function animateRing() {
            ringX += (mouseX - ringX) * 0.15;
            ringY += (mouseY - ringY) * 0.15;
            ring.style.transform = 'translate(' + ringX + 'px, ' + ringY + 'px) translate(-50%, -50%)';
            if (!isHidden) requestAnimationFrame(animateRing);
        }
        animateRing();
    });
    </script>

    <!-- ═══ FEATURE 6: FORM MICRO-INTERACTIONS ═══ -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.input-field__input').forEach(function(input) {
            if (input.value) input.classList.add('has-value');
            input.addEventListener('input', function() {
                input.classList.toggle('has-value', !!input.value);
            });
            input.addEventListener('focus', function() {
                input.closest('.input-field')?.classList.add('input-field--focused');
            });
            input.addEventListener('blur', function() {
                input.closest('.input-field')?.classList.remove('input-field--focused');
            });
        });
    });
    </script>
</body>
</html>
