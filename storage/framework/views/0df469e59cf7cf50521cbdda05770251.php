
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Trusted by teams at']));

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

foreach (array_filter((['title' => 'Trusted by teams at']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<section class="logo-cloud reveal">
    <p class="logo-cloud__title"><?php echo e($title); ?></p>
    <div class="logo-cloud__grid">
        
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <rect x="10" y="12" width="16" height="16" rx="3"/>
                <rect x="32" y="8" width="78" height="6" rx="2"/>
                <rect x="32" y="18" width="50" height="6" rx="2"/>
                <rect x="32" y="28" width="65" height="4" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <circle cx="20" cy="20" r="12"/>
                <rect x="40" y="14" width="70" height="12" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <polygon points="20,5 35,35 5,35"/>
                <rect x="45" y="10" width="65" height="8" rx="2"/>
                <rect x="45" y="22" width="45" height="8" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <rect x="5" y="5" width="30" height="30" rx="6"/>
                <rect x="45" y="12" width="70" height="6" rx="2"/>
                <rect x="45" y="22" width="55" height="6" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <path d="M20,5 L35,20 L20,35 L5,20 Z"/>
                <rect x="45" y="14" width="65" height="12" rx="2"/>
            </svg>
        </div>
        <div class="logo-cloud__item">
            <svg viewBox="0 0 120 40" fill="currentColor" class="logo-cloud__logo">
                <ellipse cx="20" cy="20" rx="15" ry="12"/>
                <rect x="42" y="10" width="68" height="8" rx="2"/>
                <rect x="42" y="22" width="50" height="8" rx="2"/>
            </svg>
        </div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/logo-cloud.blade.php ENDPATH**/ ?>