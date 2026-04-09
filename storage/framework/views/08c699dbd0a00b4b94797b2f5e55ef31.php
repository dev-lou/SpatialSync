<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__inner">
            <div class="footer__brand">
                <a href="<?php echo e(route('home')); ?>" class="footer__logo">
                    <span class="navbar__logo">
                        <i data-lucide="layout" class="w-5 h-5"></i>
                    </span>
                    ConstructHub
                </a>
                <p class="footer__tagline">
                    Build houses and buildings together. Real-time 3D construction for everyone.
                </p>
            </div>

            <div>
                <h4 class="footer__section-title">Product</h4>
                <nav class="footer__nav">
                    <a href="<?php echo e(route('features')); ?>" class="footer__link">Features</a>
                    <a href="<?php echo e(route('pricing')); ?>" class="footer__link">Pricing</a>
                    <a href="<?php echo e(route('about')); ?>" class="footer__link">About</a>
                </nav>
            </div>

            <div>
                <h4 class="footer__section-title">Resources</h4>
                <nav class="footer__nav">
                    <a href="#" class="footer__link">Documentation</a>
                    <a href="#" class="footer__link">Tutorials</a>
                    <a href="#" class="footer__link">Blog</a>
                </nav>
            </div>

            <div>
                <h4 class="footer__section-title">Legal</h4>
                <nav class="footer__nav">
                    <a href="#" class="footer__link">Privacy Policy</a>
                    <a href="#" class="footer__link">Terms of Service</a>
                    <a href="#" class="footer__link">Cookie Policy</a>
                </nav>
            </div>
        </div>

        <div class="footer__bottom">
            <p class="footer__copyright">
                &copy; <?php echo e(date('Y')); ?> ConstructHub. All rights reserved.
            </p>
            <div class="footer__legal">
                <a href="#" class="footer__legal-link">Privacy</a>
                <a href="#" class="footer__legal-link">Terms</a>
                <a href="#" class="footer__legal-link">Cookies</a>
            </div>
            <!-- Back to Top -->
            <a href="#" class="btn btn--ghost btn--sm" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" aria-label="Back to top">
                <i data-lucide="arrow-up" class="w-4 h-4"></i>
                <span class="sr-only">Back to top</span>
            </a>
        </div>
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/footer.blade.php ENDPATH**/ ?>