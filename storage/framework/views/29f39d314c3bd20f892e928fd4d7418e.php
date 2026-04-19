<div <?php echo e($attributes->merge(['class' => 'avatar' . ($size ?? 'md' ? ' avatar--' . ($size ?? 'md') : '')])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($src) && $src): ?>
        <img 
            src="<?php echo e($src); ?>" 
            alt="<?php echo e($name ?? 'User avatar'); ?>" 
            width="<?php echo e(($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40))); ?>"
            height="<?php echo e(($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40))); ?>"
            loading="lazy"
            class="w-full h-full object-cover"
        >
    <?php else: ?>
        <span class="avatar__initials"><?php echo e(strtoupper(substr($name ?? 'U', 0, 1))); ?></span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/avatar.blade.php ENDPATH**/ ?>