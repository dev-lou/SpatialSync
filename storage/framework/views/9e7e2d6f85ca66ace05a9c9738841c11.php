
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'quote',
    'name',
    'role' => null,
    'title' => null,
    'company' => null,
    'image' => null,
    'featured' => false
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
    'quote',
    'name',
    'role' => null,
    'title' => null,
    'company' => null,
    'image' => null,
    'featured' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    // Support both 'role' (combined) and 'title'/'company' (separate) props
    $displayRole = $role ?? (($title && $company) ? "{$title}, {$company}" : ($title ?? $company ?? ''));
?>

<blockquote <?php echo e($attributes->merge(['class' => 'testimonial-card' . ($featured ? ' testimonial-card--featured' : '') . ' glow-card reveal'])); ?>>
    <div class="testimonial-card__quote">
        <svg class="testimonial-card__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
        <p class="testimonial-card__text"><?php echo e($quote); ?></p>
    </div>
    
    <footer class="testimonial-card__author">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image): ?>
            <img 
                src="<?php echo e($image); ?>" 
                alt="<?php echo e($name); ?>" 
                class="testimonial-card__avatar"
                width="48"
                height="48"
                loading="lazy"
            >
        <?php else: ?>
            <div class="testimonial-card__avatar testimonial-card__avatar--placeholder">
                <?php echo e(strtoupper(substr($name, 0, 1))); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="testimonial-card__info">
            <cite class="testimonial-card__name"><?php echo e($name); ?></cite>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($displayRole): ?>
                <span class="testimonial-card__role"><?php echo e($displayRole); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </footer>
</blockquote>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/testimonial-card.blade.php ENDPATH**/ ?>