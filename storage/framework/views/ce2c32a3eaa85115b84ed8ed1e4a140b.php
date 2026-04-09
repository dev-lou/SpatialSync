
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'value',
    'label',
    'prefix' => '',
    'suffix' => '',
    'duration' => 2000,
    'icon' => null
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'value',
    'label',
    'prefix' => '',
    'suffix' => '',
    'duration' => 2000,
    'icon' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'stat-counter reveal'])); ?>>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($icon): ?>
        <div class="stat-counter__icon">
            <i data-lucide="<?php echo e($icon); ?>" class="w-6 h-6"></i>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <span 
        class="stat-counter__number"
        data-count-to="<?php echo e($value); ?>"
        data-count-prefix="<?php echo e($prefix); ?>"
        data-count-suffix="<?php echo e($suffix); ?>"
        data-count-duration="<?php echo e($duration); ?>"
    ><?php echo e($prefix); ?>0<?php echo e($suffix); ?></span>
    <span class="stat-counter__label"><?php echo e($label); ?></span>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/stat-counter.blade.php ENDPATH**/ ?>