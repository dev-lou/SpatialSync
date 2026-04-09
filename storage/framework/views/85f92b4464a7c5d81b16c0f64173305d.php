<nav class="navbar" role="navigation" aria-label="Main navigation">
    <div class="container navbar__inner">
        <a href="<?php echo e(route('home')); ?>" class="navbar__brand">
            <span class="navbar__logo">
                <i data-lucide="layout" class="w-5 h-5"></i>
            </span>
            ConstructHub
        </a>

        <div class="navbar__nav">
            <a href="<?php echo e(route('home')); ?>" class="navbar__link <?php echo e(request()->routeIs('home') ? 'navbar__link--active' : ''); ?>">
                Home
            </a>
            <a href="<?php echo e(route('features')); ?>" class="navbar__link <?php echo e(request()->routeIs('features') ? 'navbar__link--active' : ''); ?>">
                Features
            </a>
            <a href="<?php echo e(route('pricing')); ?>" class="navbar__link <?php echo e(request()->routeIs('pricing') ? 'navbar__link--active' : ''); ?>">
                Pricing
            </a>
            <a href="<?php echo e(route('about')); ?>" class="navbar__link <?php echo e(request()->routeIs('about') ? 'navbar__link--active' : ''); ?>">
                About
            </a>
        </div>

        <div class="navbar__actions">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!request()->routeIs('dashboard')): ?>
                    <a href="<?php echo e(route('dashboard')); ?>" class="btn btn--ghost btn--sm">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php if (isset($component)) { $__componentOriginalcedb185ae97611e17ef6f86a5f08cf12 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcedb185ae97611e17ef6f86a5f08cf12 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.profile-dropdown','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('profile-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcedb185ae97611e17ef6f86a5f08cf12)): ?>
<?php $attributes = $__attributesOriginalcedb185ae97611e17ef6f86a5f08cf12; ?>
<?php unset($__attributesOriginalcedb185ae97611e17ef6f86a5f08cf12); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcedb185ae97611e17ef6f86a5f08cf12)): ?>
<?php $component = $__componentOriginalcedb185ae97611e17ef6f86a5f08cf12; ?>
<?php unset($__componentOriginalcedb185ae97611e17ef6f86a5f08cf12); ?>
<?php endif; ?>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn--ghost btn--sm">
                    Sign in
                </a>
                <a href="<?php echo e(route('register')); ?>" class="btn btn--primary btn--sm">
                    Get Started
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button class="navbar__mobile-toggle" aria-label="Open menu">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/navbar.blade.php ENDPATH**/ ?>