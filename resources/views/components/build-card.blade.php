@props(['build'])
@php
    $build = is_array($build) ? (object) $build : $build;
@endphp
<style>
    .build-card {
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

    .build-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .build-card__thumb {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 10;
        background-color: var(--bg-secondary);
        overflow: hidden;
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    }

    .build-card__grid {
        position: absolute;
        inset: 0;
        opacity: 0.15;
        color: var(--accent);
    }

    .build-card__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top right, rgba(0, 102, 255, 0.05), transparent);
    }

    .build-card__icon-wrap {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .build-card__icon {
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

    .build-card:hover .build-card__icon {
        transform: scale(1.1);
    }

    .build-card__body {
        flex: 1;
        padding: var(--space-6);
        display: flex;
        flex-direction: column;
    }

    .build-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: var(--space-4);
        margin-bottom: var(--space-2);
    }

    .build-card__title {
        font-size: var(--text-lg);
        font-weight: 700;
        color: var(--text-primary);
        transition: color var(--dur-micro);
        line-height: 1.3;
    }

    .build-card:hover .build-card__title {
        color: var(--accent);
    }

    .build-card__avatar-stack {
        display: flex;
        align-items: center;
        flex-direction: row-reverse;
    }

    .build-card__avatar-item {
        margin-left: -8px;
        border: 2px solid var(--surface);
        box-shadow: var(--shadow-xs);
        border-radius: var(--radius-full);
        overflow: hidden;
    }

    .build-card__desc {
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

    .build-card__footer {
        padding-top: var(--space-4);
        border-top: 1px solid var(--border-default);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .build-card__meta-item {
        display: flex;
        align-items: center;
        gap: var(--space-2);
        color: var(--text-tertiary);
        font-size: var(--text-xs);
        font-weight: 500;
    }

    .build-card__meta-item i {
        width: 14px;
        height: 14px;
    }

    .build-card__meta-group {
        display: flex;
        align-items: center;
        gap: var(--space-4);
    }

    .build-card__role-badge {
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

    .build-card__role-badge i {
        width: 12px;
        height: 12px;
    }

    /* Options dropdown */
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
        transition: background-color var(--dur-micro), color var(--dur-micro), box-shadow var(--dur-micro);
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
        transition: opacity var(--dur-base) var(--ease-out), transform var(--dur-base) var(--ease-out), visibility var(--dur-base);
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
        transition: background-color var(--dur-micro), color var(--dur-micro);
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

    .swal-premium-popup {
        border-radius: var(--radius-2xl) !important;
        border: 1px solid var(--border-default) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
    }
</style>

<div class="build-card">
    @if(isset($build->user_role) && $build->user_role !== 'owner')
        <div class="build-card__role-badge">
            <i data-lucide="users"></i>
            {{ ucfirst($build->user_role) }}
        </div>
    @endif

    @if(!isset($build->user_role) || $build->user_role === 'owner')
    <div class="build-options">
        <button class="build-options__btn" onclick="toggleBuildOptions(this, event)">
            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
        </button>
        <div class="build-options__dropdown">
            <button class="build-options__item" onclick="editBuild('{{ $build->id }}', '{{ addslashes($build->name) }}', '{{ addslashes($build->description ?? '') }}')">
                <i data-lucide="edit-2" class="w-4 h-4"></i>
                Edit Details
            </button>
            <button class="build-options__item build-options__item--danger" onclick="deleteBuild('{{ $build->id }}', '{{ addslashes($build->name) }}')">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                Delete Build
            </button>
        </div>
    </div>
    @endif

    <a href="{{ route('builds.show', $build->id) }}" class="build-card__thumb">
        <div class="build-card__grid">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="grid-{{ $build->id }}" width="24" height="24" patternUnits="userSpaceOnUse">
                        <path d="M 24 0 L 0 0 0 24" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-{{ $build->id }})"/>
            </svg>
        </div>
        <div class="build-card__overlay"></div>
        <div class="build-card__icon-wrap">
            <div class="build-card__icon">
                <i data-lucide="layout" class="text-accent w-8 h-8"></i>
            </div>
        </div>
    </a>

    <div class="build-card__body">
        <div class="build-card__header">
            <a href="{{ route('builds.show', $build->id) }}" class="build-card__title">{{ $build->name }}</a>
            
            <div class="build-card__avatar-stack">
                @php 
                    $members = isset($build->members) ? ($build->members->take(3) ?? collect([])) : collect([]);
                    $totalMembers = isset($build->members) ? ($build->members->count() ?? 0) : 0;
                @endphp
                
                @if($totalMembers > 3)
                    <div class="build-card__avatar-item" style="background: var(--bg-tertiary); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: var(--text-secondary);">
                        +{{ $totalMembers - 3 }}
                    </div>
                @endif

                @foreach($members as $member)
                    <div class="build-card__avatar-item">
                        <x-avatar :name="$member->name" :src="$member->avatar_url ?? null" size="sm" style="width: 24px; height: 24px;" />
                    </div>
                @endforeach
            </div>
        </div>

        <p class="build-card__desc">{{ $build->description ?? 'No description provided for this design project.' }}</p>
        
        <div class="build-card__footer">
            <div class="build-card__meta-item">
                <i data-lucide="clock"></i>
                <span>{{ $build->updated_at ? \Carbon\Carbon::parse($build->updated_at)->diffForHumans() : 'Recently' }}</span>
            </div>
            <div class="build-card__meta-group">
                <div class="build-card__meta-item">
                    <i data-lucide="users"></i>
                    <span style="font-weight: 700; color: var(--text-secondary);">{{ $totalMembers }}</span>
                </div>
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
    document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    if (!isShowing) dropdown.classList.add('show');
}

function editBuild(buildId, currentName, currentDesc) {
    document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    Swal.fire({
        title: 'Edit Build Details',
        html: `
            <div style="text-align: left; margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 8px;">Build Name</label>
                <input id="swal-input1" class="swal2-input" value="${currentName}" style="margin: 0; width: 100%;">
            </div>
            <div style="text-align: left;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-tertiary); text-transform: uppercase; margin-bottom: 8px;">Description</label>
                <textarea id="swal-input2" class="swal2-textarea" style="margin: 0; width: 100%; min-height: 100px;">${currentDesc}</textarea>
            </div>
        `,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'btn btn--primary px-8',
            cancelButton: 'btn btn--secondary px-8'
        },
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        preConfirm: () => {
            const name = document.getElementById('swal-input1').value.trim();
            const desc = document.getElementById('swal-input2').value.trim();
            if (!name) {
                Swal.showValidationMessage('Name is required');
                return false;
            }
            return { name, description: desc };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/builds/${buildId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Saved!', '', 'success').then(() => window.location.reload());
                }
            });
        }
    });
}

function deleteBuild(buildId, buildName) {
    document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    Swal.fire({
        title: 'Delete Build?',
        text: `Are you sure you want to delete "${buildName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn--error px-8',
            cancelButton: 'btn btn--secondary px-8'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/builds/${buildId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(() => window.location.reload());
        }
    });
}

document.addEventListener('click', e => {
    if (!e.target.closest('.build-options')) {
        document.querySelectorAll('.build-options__dropdown.show').forEach(d => d.classList.remove('show'));
    }
});
</script>
