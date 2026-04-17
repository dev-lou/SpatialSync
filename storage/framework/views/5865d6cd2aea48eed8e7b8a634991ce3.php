
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'faq-accordion'])); ?> x-data="{ openIndex: null }">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="faq-accordion__item reveal" style="--delay: <?php echo e($index * 50); ?>ms;">
            <button
                type="button"
                class="faq-accordion__trigger"
                :class="{ 'faq-accordion__trigger--open': openIndex === <?php echo e($index); ?> }"
                @click="openIndex = openIndex === <?php echo e($index); ?> ? null : <?php echo e($index); ?>"
                :aria-expanded="openIndex === <?php echo e($index); ?>"
                aria-controls="faq-content-<?php echo e($index); ?>"
            >
                <span class="faq-accordion__question"><?php echo e($item['question']); ?></span>
                <span class="faq-accordion__icon">
                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                </span>
            </button>
            <div 
                id="faq-content-<?php echo e($index); ?>"
                class="faq-accordion__content"
                x-show="openIndex === <?php echo e($index); ?>"
                x-collapse
                x-cloak
            >
                <p class="faq-accordion__answer"><?php echo e($item['answer']); ?></p>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/faq-accordion.blade.php ENDPATH**/ ?>