<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__inner">
            <div class="footer__brand">
                <a href="<?php echo e(route('home')); ?>" class="footer__logo">
                    <span class="navbar__logo">
                        <i data-lucide="box" class="w-5 h-5"></i>
                    </span>
                    SpatialSync
                </a>
                <p class="footer__tagline">
                    Real-time collaborative 3D building design for teams. Design, iterate, and ship — together.
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
                &copy; <?php echo e(date('Y')); ?> SpatialSync. All rights reserved.
            </p>
            <!-- Back to Top -->
            <a href="#" class="btn btn--ghost btn--sm" onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;" aria-label="Back to top">
                <i data-lucide="arrow-up" class="w-4 h-4"></i>
                <span class="sr-only">Back to top</span>
            </a>
        </div>
    </div>

    <!-- Antigravity / Premium Mega Text -->
    <div class="footer__giant-text" aria-hidden="true">
        SpatialSync
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/footer.blade.php ENDPATH**/ ?>