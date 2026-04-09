
<div class="profile-dropdown" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
    
    <button 
        type="button" 
        class="profile-dropdown__trigger" 
        @click="open = !open"
        :aria-expanded="open"
        aria-label="User menu"
    >
        <div class="profile-dropdown__avatar">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->profile_photo_path): ?>
                <img src="<?php echo e(auth()->user()->profile_photo_url); ?>" alt="<?php echo e(auth()->user()->name); ?>" class="profile-dropdown__avatar-img">
            <?php else: ?>
                <span class="profile-dropdown__initials">
                    <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?><?php echo e(strtoupper(substr(explode(' ', auth()->user()->name)[1] ?? auth()->user()->name, 0, 1))); ?>

                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <i data-lucide="chevron-down" class="profile-dropdown__chevron" :class="{ 'rotate-180': open }"></i>
    </button>

    
    <div 
        class="profile-dropdown__menu" 
        x-show="open"
        x-transition:enter="profile-dropdown-enter"
        x-transition:enter-start="profile-dropdown-enter-start"
        x-transition:enter-end="profile-dropdown-enter-end"
        x-transition:leave="profile-dropdown-leave"
        x-transition:leave-start="profile-dropdown-leave-start"
        x-transition:leave-end="profile-dropdown-leave-end"
        x-cloak
    >
        
        <div class="profile-dropdown__header">
            <div class="profile-dropdown__name"><?php echo e(auth()->user()->name); ?></div>
            <div class="profile-dropdown__email"><?php echo e(auth()->user()->email); ?></div>
        </div>

        <div class="profile-dropdown__divider"></div>

        
        <a href="<?php echo e(route('home')); ?>" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="home" class="profile-dropdown__icon"></i>
            <span>Home</span>
        </a>

        <a href="<?php echo e(route('dashboard')); ?>" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="layout-dashboard" class="profile-dropdown__icon"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo e(route('profile.show')); ?>" class="profile-dropdown__item" @click="open = false">
            <i data-lucide="settings" class="profile-dropdown__icon"></i>
            <span>Settings</span>
        </a>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->is_admin ?? false): ?>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="profile-dropdown__item profile-dropdown__item--admin" @click="open = false">
                <i data-lucide="shield" class="profile-dropdown__icon"></i>
                <span>Admin Dashboard</span>
                <span class="profile-dropdown__badge">Admin</span>
            </a>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="profile-dropdown__divider"></div>

        
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="profile-dropdown__item profile-dropdown__item--logout">
                <i data-lucide="log-out" class="profile-dropdown__icon"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/profile-dropdown.blade.php ENDPATH**/ ?>