<?php $__env->startSection('title', 'My Builds'); ?>
<?php $__env->startSection('description', 'View and manage all your house construction projects.'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── BUILDS INDEX STYLES ─────────────────────── */
.builds-page {
    padding: var(--space-8) 0 var(--space-16);
    background: var(--bg);
    min-height: calc(100vh - var(--header-height) - 200px);
}

.builds-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-8);
}

@media (min-width: 768px) {
    .builds-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.builds-header__title {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: 400;
    color: var(--text-primary);
}

.builds-header__subtitle {
    font-size: var(--text-base);
    color: var(--text-secondary);
    margin-top: var(--space-1);
}

.builds-header__actions {
    display: flex;
    gap: var(--space-3);
}

/* ── FILTER BAR ──────────────────────────────── */
.filter-bar {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    margin-bottom: var(--space-6);
    padding: var(--space-3) var(--space-4);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
}

.filter-bar__search {
    flex: 1;
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-2) var(--space-3);
    background: var(--bg);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-lg);
}

.filter-bar__search input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: var(--text-sm);
    color: var(--text-primary);
    outline: none;
    font-family: var(--font-body);
}

.filter-bar__search input::placeholder {
    color: var(--text-tertiary);
}

.filter-bar__search i {
    color: var(--text-tertiary);
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: var(--space-1);
    padding: var(--space-2) var(--space-3);
    font-size: var(--text-xs);
    font-weight: 600;
    color: var(--text-secondary);
    background: var(--bg);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: background var(--dur-base), color var(--dur-base), border-color var(--dur-base);
    white-space: nowrap;
}

.filter-chip:hover {
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.filter-chip.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
}

/* ── BUILDS GRID ─────────────────────────────── */
.builds-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-6);
}

@media (min-width: 768px) {
    .builds-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .builds-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ── EMPTY STATE ─────────────────────────────── */
.builds-empty {
    padding: var(--space-16);
    text-align: center;
    background: var(--surface);
    border: 2px dashed var(--border-default);
    border-radius: var(--radius-xl);
}

.builds-empty__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 5rem;
    height: 5rem;
    margin: 0 auto var(--space-6);
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-xl);
}

.builds-empty__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.builds-empty__description {
    font-size: var(--text-base);
    color: var(--text-secondary);
    max-width: 400px;
    margin: 0 auto var(--space-8);
}

/* ── COUNT BADGE ─────────────────────────────── */
.count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 24px;
    padding: 0 var(--space-2);
    background: var(--bg-secondary);
    color: var(--text-secondary);
    font-size: var(--text-xs);
    font-weight: 600;
    border-radius: var(--radius-full);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="builds-page" x-data="{ search: '', filter: 'all' }">
    <div class="container">
        <!-- Header -->
        <div class="builds-header reveal">
            <div>
                <h1 class="builds-header__title">My Builds</h1>
                <p class="builds-header__subtitle"><?php echo e($builds->count()); ?> project<?php echo e($builds->count() !== 1 ? 's' : ''); ?> total</p>
            </div>
            <div class="builds-header__actions">
                <a href="<?php echo e(route('builds.create')); ?>" class="btn btn--primary btn-glow">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    New Build
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar reveal">
            <div class="filter-bar__search">
                <i data-lucide="search"></i>
                <input type="text" placeholder="Search builds..." x-model="search" autocomplete="off">
            </div>
            <button class="filter-chip" :class="{ active: filter === 'all' }" @click="filter = 'all'">
                All <span class="count-badge"><?php echo e($builds->count()); ?></span>
            </button>
            <button class="filter-chip" :class="{ active: filter === 'recent' }" @click="filter = 'recent'">
                Recent
            </button>
        </div>

        <!-- Builds Grid -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($builds->count() > 0): ?>
            <div class="builds-grid stagger">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $builds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $build): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div x-show="
                        (search === '' || '<?php echo e(strtolower($build->name)); ?>'.includes(search.toLowerCase())) &&
                        (filter === 'all' || filter === 'recent')
                    " x-transition>
                        <?php if (isset($component)) { $__componentOriginal5fe4e89c5acea8188e4277fe0590d825 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5fe4e89c5acea8188e4277fe0590d825 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.blueprint-card','data' => ['blueprint' => $build,'class' => 'glow-card reveal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('blueprint-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['blueprint' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($build),'class' => 'glow-card reveal']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5fe4e89c5acea8188e4277fe0590d825)): ?>
<?php $attributes = $__attributesOriginal5fe4e89c5acea8188e4277fe0590d825; ?>
<?php unset($__attributesOriginal5fe4e89c5acea8188e4277fe0590d825); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5fe4e89c5acea8188e4277fe0590d825)): ?>
<?php $component = $__componentOriginal5fe4e89c5acea8188e4277fe0590d825; ?>
<?php unset($__componentOriginal5fe4e89c5acea8188e4277fe0590d825); ?>
<?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="builds-empty reveal">
                <div class="builds-empty__icon">
                    <i data-lucide="folder-plus" class="w-8 h-8"></i>
                </div>
                <h3 class="builds-empty__title">No builds yet</h3>
                <p class="builds-empty__description">
                    Create your first build to start designing houses, buildings, and architectural designs.
                </p>
                <a href="<?php echo e(route('builds.create')); ?>" class="btn btn--primary btn--lg btn-glow">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Create your first build
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\flow\resources\views/builds/index.blade.php ENDPATH**/ ?>