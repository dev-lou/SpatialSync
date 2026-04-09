<a href="<?php echo e(route('builds.show', $blueprint)); ?>" class="group block">
    <div class="card card--hover h-full">
        <div class="aspect-[4/3] bg-secondary rounded-lg mb-4 overflow-hidden relative">
            <div class="absolute inset-0 opacity-20">
                <svg width="100%" height="100%">
                    <defs>
                        <pattern id="grid-<?php echo e($blueprint->id); ?>" width="20" height="20" patternUnits="userSpaceOnUse">
                            <path d="M 20 0 L 0 0 0 20" fill="none" stroke="currentColor" stroke-width="0.5" class="text-border"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-<?php echo e($blueprint->id); ?>)"/>
                </svg>
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i data-lucide="layout" class="w-12 h-12 text-tertiary group-hover:text-accent transition-colors"></i>
            </div>
            <div class="absolute bottom-3 right-3 flex -space-x-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $blueprint->members->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.avatar','data' => ['name' => $member->name,'size' => 'sm','class' => 'border-2 border-surface']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->name),'size' => 'sm','class' => 'border-2 border-surface']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $attributes = $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b)): ?>
<?php $component = $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b; ?>
<?php unset($__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b); ?>
<?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($blueprint->members->count() > 3): ?>
                    <div class="w-8 h-8 rounded-full bg-tertiary border-2 border-surface flex items-center justify-center text-xs font-medium text-primary">
                        +<?php echo e($blueprint->members->count() - 3); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-primary group-hover:text-accent transition-colors mb-1">
                <?php echo e($blueprint->name); ?>

            </h3>
            <p class="text-sm text-secondary line-clamp-2 mb-3">
                <?php echo e($blueprint->description ?? 'No description'); ?>

            </p>
            <div class="flex items-center justify-between text-xs text-tertiary">
                <span><?php echo e($blueprint->updated_at->diffForHumans()); ?></span>
                <span class="flex items-center gap-1">
                    <i data-lucide="users" class="w-3 h-3"></i>
                    <?php echo e($blueprint->members->count()); ?>

                </span>
            </div>
        </div>
    </div>
</a>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/blueprint-card.blade.php ENDPATH**/ ?>