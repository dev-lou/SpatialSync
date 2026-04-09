
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'discount' => 20
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
    'discount' => 20
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'pricing-toggle'])); ?> x-data="{ annual: false }">
    <span class="pricing-toggle__label" :class="{ 'pricing-toggle__label--active': !annual }">Monthly</span>
    
    <button 
        type="button"
        class="pricing-toggle__switch"
        :class="{ 'pricing-toggle__switch--active': annual }"
        @click="annual = !annual; $dispatch('billing-change', { annual })"
        role="switch"
        :aria-checked="annual"
        aria-label="Toggle billing period"
    >
        <span class="pricing-toggle__thumb"></span>
    </button>
    
    <span class="pricing-toggle__label" :class="{ 'pricing-toggle__label--active': annual }">
        Annual
        <span class="pricing-toggle__badge">Save <?php echo e($discount); ?>%</span>
    </span>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/pricing-toggle.blade.php ENDPATH**/ ?>