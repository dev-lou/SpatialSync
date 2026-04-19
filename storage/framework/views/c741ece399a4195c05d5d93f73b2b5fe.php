<style>
    .blueprint-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--surface);
        border-radius: var(--radius-xl);
        overflow: visible;
        position: relative;
        text-decoration: none;
        transition: transform var(--dur-base) var(--ease-out), box-shadow var(--dur-base) var(--ease-out);
        box-shadow: var(--shadow-sm);
        z-index: 1;
    }

    .blueprint-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .blueprint-card__thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 10;
        background-color: var(--bg-secondary);
        overflow: hidden;
    }

    .blueprint-card__grid {
        position: absolute;
        inset: 0;
        opacity: 0.15;
        color: var(--accent);
    }

    .blueprint-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top right, rgba(0, 102, 255, 0.05), transparent);
    }

    .blueprint-card__icon-wrap {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .blueprint-card__icon {
        width: 4rem;
        height: 4rem;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
        transition: transform var(--dur-base) var(--ease-spring);
    }

    .blueprint-card:hover .blueprint-card__icon {
        transform: scale(1.1);
    }

    .blueprint-card__body {
        flex: 1;
        padding: var(--space-6);
        display: flex;
        flex-direction: column;
    }

    .blueprint-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--space-4);
        margin-bottom: var(--space-2);
    }

    .blueprint-card__title {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--text-primary);
        transition: color var(--dur-micro);
        line-height: 1.3;
    }

    .blueprint-card:hover .blueprint-card__title {
        color: var(--accent);
    }

    .blueprint-card__avatar-stack {
        display: flex;
        align-items: center;
        flex-direction: row-reverse;
    }

    .blueprint-card__avatar-item {
        margin-left: -8px;
        border: 2px solid var(--surface);
        box-shadow: var(--shadow-xs);
        border-radius: var(--radius-full);
        overflow: hidden;
    }

    .blueprint-card__desc {
        font-size: var(--text-sm);
        color: var(--text-secondary);
        opacity: 0.8;
        line-height: 1.6;
        margin-bottom: var(--space-6);
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .blueprint-card__footer {
        padding-top: var(--space-4);
        border-top: 1px solid var(--border-default);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .blueprint-card__meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--text-tertiary);
        font-size: var(--text-xs);
        font-weight: 500;
    }

    .blueprint-card__meta-item i {
        width: 14px;
        height: 14px;
    }

    .blueprint-card__meta-group {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .blueprint-card__role-badge {
        position: absolute;
        top: var(--space-4);
        left: var(--space-4);
        z-index: 10;
        display: flex;
        align-items: center;
        gap: var(--space-2);
        padding: 6px 12px;
        background: var(--text-primary);
        color: var(--surface);
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-radius: var(--radius-full);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .blueprint-card__role-badge i {
        width: 12px;
        height: 12px;
    }

    /* 3-dot dropdown styles */
    .build-options {
        position: absolute;
        top: var(--space-4);
        right: var(--space-4);
        z-index: 10;
    }

    .build-options__btn {
        width: 32px;
        height: 32px;
        border-radius: var(--radius-lg);
        background: rgba(255, 255, 255, 0.9);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        transition: all var(--dur-micro);
        backdrop-filter: blur(8px);
        box-shadow: var(--shadow-sm);
    }

    .build-options__btn:hover {
        background: white;
        color: var(--text-primary);
    }

    .build-options__dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: var(--space-2);
        min-width: 160px;
        background: var(--surface);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: all var(--dur-base);
        z-index: 50;
    }

    .build-options__dropdown.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .build-options__item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        width: 100%;
        padding: var(--space-3) var(--space-4);
        border: none;
        background: none;
        color: var(--text-secondary);
        font-size: var(--text-sm);
        cursor: pointer;
        transition: all var(--dur-micro);
        text-align: left;
    }

    .build-options__item:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .build-options__item--danger {
        color: var(--error);
    }

    .build-options__item--danger:hover {
        background: var(--error-light);
        color: var(--error);
    }

    .build-options__item i {
        width: 16px;
        height: 16px;
    }
</style>

<div class="blueprint-card" style="position: relative;">
    <!-- Identity Badge (Owner/Collaborator) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($blueprint->user_role) && $blueprint->user_role !== 'owner'): ?>
        <div class="blueprint-card__role-badge">
            <i data-lucide="users"></i>
            <?php echo e(ucfirst($blueprint->user_role)); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- 3-dot Options Button (Hide if not owner) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($blueprint->user_role) || $blueprint->user_role === 'owner'): ?>
    <div class="build-options">
        <button class="build-options__btn" onclick="toggleBuildOptions(this, event)">
            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
        </button>
        <div class="build-options__dropdown">
            <button class="build-options__item build-options__item--danger" onclick="deleteBuild('<?php echo e($blueprint->id); ?>', '<?php echo e(addslashes($blueprint->name)); ?>')">
                <i data-lucide="trash-2"></i>
                Delete Build
            </button>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <a href="<?php echo e(route('builds.show', $blueprint->id)); ?>" class="blueprint-card__thumb" style="display: block;">
        <div class="blueprint-card__grid">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="grid-<?php echo e($blueprint->id); ?>" width="24" height="24" patternUnits="userSpaceOnUse">
                        <path d="M 24 0 L 0 0 0 24" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-<?php echo e($blueprint->id); ?>)"/>
            </svg>
        </div>
        <div class="blueprint-card__overlay"></div>
        <div class="blueprint-card__icon-wrap">
            <div class="blueprint-card__icon">
                <i data-lucide="layout" class="text-accent"></i>
            </div>
        </div>
    </a>

    <div class="blueprint-card__body">
        <div class="blueprint-card__header">
            <a href="<?php echo e(route('builds.show', $blueprint->id)); ?>" class="blueprint-card__title"><?php echo e($blueprint->name); ?></a>
            
            <div class="blueprint-card__avatar-stack">
                <?php 
                    $members = isset($blueprint->members) ? ($blueprint->members->take(3) ?? collect([])) : collect([]);
                    $totalMembers = isset($blueprint->members) ? ($blueprint->members->count() ?? 0) : 0;
                ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalMembers > 3): ?>
                    <div class="blueprint-card__avatar-item" style="background: var(--bg-tertiary); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: var(--text-secondary);">
                        +<?php echo e($totalMembers - 3); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="blueprint-card__avatar-item">
                        <?php if (isset($component)) { $__componentOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8ca5b43b8fff8bb34ab2ba4eb4bdd67b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.avatar','data' => ['name' => $member->name,'size' => 'sm','style' => 'width: 24px; height: 24px;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('avatar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($member->name),'size' => 'sm','style' => 'width: 24px; height: 24px;']); ?>
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
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <p class="blueprint-card__desc"><?php echo e($blueprint->description ?? 'No description provided for this design project.'); ?></p>
        
        <div class="blueprint-card__footer">
            <div class="blueprint-card__meta-item">
                <i data-lucide="clock"></i>
                <span><?php echo e($blueprint->updated_at ? \Carbon\Carbon::parse($blueprint->updated_at)->diffForHumans() : 'Recently'); ?></span>
            </div>
            <div class="blueprint-card__meta-group">
                <div class="blueprint-card__meta-item">
                    <i data-lucide="users"></i>
                    <span style="font-weight: 700; color: var(--text-secondary);"><?php echo e($totalMembers); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($blueprint->is_public) && $blueprint->is_public): ?>
                    <span class="blueprint-card__status">Public</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleBuildOptions(btn, event) {
    event.stopPropagation();
    event.preventDefault();
    
    const dropdown = btn.nextElementSibling;
    const isShowing = dropdown.classList.contains('show');
    
    // Close all other dropdowns first
    document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    
    // Toggle this one
    if (!isShowing) {
        dropdown.classList.add('show');
    }
}

function deleteBuild(buildId, buildName) {
    // Close dropdown first
    document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    
    Swal.fire({
        title: 'Delete Build?',
        text: `Are you sure you want to delete "${buildName}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn--danger',
            cancelButton: 'btn btn--secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send DELETE request
            fetch(`/builds/${buildId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Build has been deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload the page to reflect changes
                        window.location.reload();
                    });
                } else {
                    return response.json();
                }
            })
            .then(data => {
                if (data && data.error) {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                console.error('Delete error:', error);
            });
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.build-options')) {
        document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    }
});
</script>

<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/blueprint-card.blade.php ENDPATH**/ ?>