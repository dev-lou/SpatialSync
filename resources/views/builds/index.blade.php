@extends('layouts.app')
@section('title', 'My Builds')
@section('description', 'View and manage all your house construction projects.')

@push('styles')
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
@endpush

@section('content')
<div class="builds-page" x-data="buildsIndexApp()">
    <div class="container">
        <!-- Header -->
        <div class="builds-header reveal">
            <div>
                <h1 class="builds-header__title">Workspace Projects</h1>
                <p class="builds-header__subtitle">{{ $builds->count() }} active project{{ $builds->count() !== 1 ? 's' : '' }} (Personal & Shared)</p>
            </div>
            <div class="builds-header__actions">
                <button type="button" @click="openModal()" class="btn btn--primary btn-glow">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    New Build
                </button>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar reveal">
            <div class="filter-bar__search">
                <i data-lucide="search"></i>
                <input type="text" placeholder="Search builds..." x-model="search" autocomplete="off">
            </div>
            <button class="filter-chip" :class="{ active: filter === 'all' }" @click="filter = 'all'">
                All <span class="count-badge">{{ $builds->count() }}</span>
            </button>
            <button class="filter-chip" :class="{ active: filter === 'recent' }" @click="filter = 'recent'">
                Recent
            </button>
        </div>

        @php
            $personalBuilds = $builds->filter(fn($b) => $b->user_role === 'owner');
            $sharedWithMe = $builds->filter(fn($b) => $b->user_role !== 'owner');
        @endphp

        <!-- Personal Builds Grid -->
        @if($personalBuilds->count() > 0)
            <div class="mb-4 mt-8">
                <h2 class="text-lg font-semibold text-primary">Personal Builds</h2>
            </div>
            <div class="builds-grid stagger mb-12">
                @foreach($personalBuilds as $build)
                    <div x-show="
                        (search === '' || '{{ strtolower($build->name) }}'.includes(search.toLowerCase())) &&
                        (filter === 'all' || filter === 'recent')
                    " x-transition>
                        <x-blueprint-card :blueprint="$build" class="glow-card reveal" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="builds-empty reveal mb-12">
                <div class="builds-empty__icon">
                    <i data-lucide="folder-plus" class="w-8 h-8"></i>
                </div>
                <h3 class="builds-empty__title">No personal builds yet</h3>
                <p class="builds-empty__description">
                    Create your first build to start designing houses, buildings, and architectural designs.
                </p>
                <button type="button" @click="openModal()" class="btn btn--primary btn--lg btn-glow">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Create your first build
                </button>
            </div>
        @endif

        <!-- Shared With Me Grid -->
        @if($sharedWithMe->count() > 0)
            <div class="mb-4 mt-8">
                <h2 class="text-lg font-semibold text-primary">Shared With Me</h2>
            </div>
            <div class="builds-grid stagger mb-12">
                @foreach($sharedWithMe as $build)
                    <div x-show="
                        (search === '' || '{{ strtolower($build->name) }}'.includes(search.toLowerCase())) &&
                        (filter === 'all' || filter === 'recent')
                    " x-transition>
                        <x-blueprint-card :blueprint="$build" class="glow-card reveal" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @include('builds.partials.create-modal')
</div>

@push('scripts')
<script>
function buildsIndexApp() {
    return {
        search: '',
        filter: 'all',
        ...createBuildModalApp()
    }
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const app = document.querySelector('[x-data="buildsIndexApp()"]');
        if (app && app.__x && app.__x.$data.showModal) {
            app.__x.$data.closeModal();
        }
    }
});
</script>
@endpush
@endsection
