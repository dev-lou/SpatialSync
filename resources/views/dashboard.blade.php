@extends('layouts.app')
@section('title', 'Dashboard')
@section('description', 'Manage your house builds and collaborate with your team.')

@push('styles')
<style>
/* ── DASHBOARD STYLES ────────────────────────── */
.dashboard {
    padding: var(--space-8) 0 var(--space-16);
    background: var(--bg);
    min-height: calc(100vh - var(--header-height) - 200px);
}

/* ── WELCOME SECTION ─────────────────────────── */
.welcome-section {
    margin-bottom: var(--space-8);
}

.welcome-card {
    position: relative;
    background: linear-gradient(135deg, var(--accent) 0%, #0052CC 100%);
    border-radius: var(--radius-xl);
    padding: var(--space-8);
    color: white;
    overflow: hidden;
}

.welcome-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.welcome-card__content {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
}

@media (min-width: 768px) {
    .welcome-card__content {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.welcome-card__text h2 {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: 400;
    margin-bottom: var(--space-2);
}

.welcome-card__text p {
    opacity: 0.9;
    font-size: var(--text-base);
}

/* ── KPI CARDS ───────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-6);
    margin-bottom: var(--space-10);
}

@media (min-width: 768px) {
    .kpi-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.kpi-card {
    position: relative;
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    padding: var(--space-6);
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
}

.kpi-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-lg);
    transform: translateY(-4px);
}

.kpi-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-4);
}

.kpi-card__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
}

.kpi-card__trend {
    display: flex;
    align-items: center;
    gap: var(--space-1);
    font-size: var(--text-xs);
    font-weight: 600;
}

.kpi-card__trend--up {
    color: var(--success);
}

.kpi-card__trend--down {
    color: var(--error);
}

.kpi-card__value {
    font-size: var(--text-3xl);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-1);
}

.kpi-card__label {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

/* ── SECTION HEADER ──────────────────────────── */
.dashboard-header {
    display: flex;
    flex-direction: column;
    gap: var(--space-4);
    margin-bottom: var(--space-6);
}

@media (min-width: 768px) {
    .dashboard-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.dashboard-header__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
}

.dashboard-header__subtitle {
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.dashboard-header__actions {
    display: flex;
    gap: var(--space-3);
}

/* ── BLUEPRINTS GRID ─────────────────────────── */
.blueprints-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--space-8);
}

@media (min-width: 768px) {
    .blueprints-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .blueprints-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ── QUICK ACTIONS ───────────────────────────── */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--space-6);
    margin-bottom: var(--space-12);
}

@media (max-width: 767px) {
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .quick-actions > *:last-child {
        grid-column: span 2;
    }
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-3);
    padding: var(--space-6);
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    text-decoration: none;
    cursor: pointer;
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
}

.quick-action:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.quick-action__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    background: var(--accent-light);
    color: var(--accent);
    border-radius: var(--radius-lg);
    transition: transform var(--dur-base) var(--ease-spring);
}

.quick-action:hover .quick-action__icon {
    transform: scale(1.1);
}

.quick-action__label {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-primary);
    text-align: center;
}

/* ── EMPTY STATE ENHANCEMENT ─────────────────── */
.dashboard-empty {
    padding: var(--space-16);
    text-align: center;
    background: var(--surface);
    border: 2px dashed var(--border-default);
    border-radius: var(--radius-xl);
}

.dashboard-empty__icon {
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

.dashboard-empty__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-2);
}

.dashboard-empty__description {
    font-size: var(--text-base);
    color: var(--text-secondary);
    max-width: 400px;
    margin: 0 auto var(--space-8);
}

/* ── RECENT ACTIVITY ─────────────────────────── */
.activity-section {
    margin-top: var(--space-12);
}

.activity-list {
    background: var(--surface);
    border: 1px solid var(--border-default);
    border-radius: var(--radius-xl);
    overflow: hidden;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    padding: var(--space-4) var(--space-6);
    border-bottom: 1px solid var(--border-default);
    transition: background-color var(--dur-base);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: var(--bg-secondary);
}

.activity-item__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    background: var(--bg-secondary);
    color: var(--text-secondary);
    border-radius: var(--radius-lg);
    flex-shrink: 0;
}

.activity-item__content {
    flex: 1;
    min-width: 0;
}

.activity-item__text {
    font-size: var(--text-sm);
    color: var(--text-primary);
}

.activity-item__text strong {
    font-weight: 600;
}

.activity-item__time {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
}

/* ── CREATE BLUEPRINT MODAL ──────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-4);
    opacity: 0;
    visibility: hidden;
    transition: opacity var(--dur-base), visibility var(--dur-base);
}

.modal-backdrop.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: var(--surface);
    border-radius: var(--radius-2xl);
    box-shadow: var(--shadow-2xl);
    width: 100%;
    max-width: 900px;
    max-height: 85vh;
    overflow-x: hidden;
    overflow-y: auto;
    transform: scale(0.95) translateY(20px);
    transition: transform var(--dur-base) var(--ease-spring);
}

@media (max-width: 768px) {
    .modal {
        max-width: calc(100vw - 32px);
    }
}

.modal-backdrop.active .modal {
    transform: scale(1) translateY(0);
}

.modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-6);
    border-bottom: 1px solid var(--border-default);
}

.modal__title {
    font-size: var(--text-xl);
    font-weight: 600;
    color: var(--text-primary);
}

.modal__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border: none;
    background: var(--bg-secondary);
    border-radius: var(--radius-lg);
    color: var(--text-secondary);
    cursor: pointer;
    transition: background-color var(--dur-base), color var(--dur-base);
}

.modal__close:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.modal__body {
    padding: var(--space-6);
    overflow-x: hidden;
}

.modal__footer {
    display: flex;
    gap: var(--space-3);
    justify-content: flex-end;
    padding: var(--space-6);
    border-top: 1px solid var(--border-default);
    background: var(--bg-secondary);
    border-radius: 0 0 var(--radius-2xl) var(--radius-2xl);
}

/* ── TEMPLATE PICKER ─────────────────────────── */
.template-picker {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-3);
    margin-bottom: var(--space-6);
}

@media (max-width: 768px) {
    .template-picker {
        grid-template-columns: repeat(2, 1fr);
    }
}

.template-option {
    position: relative;
    padding: var(--space-4);
    background: var(--bg-secondary);
    border: 2px solid var(--border-default);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: border-color var(--dur-base), box-shadow var(--dur-base), transform var(--dur-base);
    text-align: center;
}

.template-option:hover {
    border-color: var(--border-strong);
    transform: translateY(-2px);
}

.template-option.selected {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-light);
    transform: translateY(-2px);
}

.template-option__icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    margin: 0 auto var(--space-2);
    background: var(--surface);
    color: var(--accent);
    border-radius: var(--radius-lg);
}

.template-option__label {
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--space-1);
}

.template-option__description {
    font-size: var(--text-xs);
    color: var(--text-tertiary);
}

.template-option__check {
    position: absolute;
    top: var(--space-2);
    right: var(--space-2);
    width: 1.25rem;
    height: 1.25rem;
    background: var(--accent);
    color: white;
    border-radius: var(--radius-full);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.5);
    transition: opacity var(--dur-base), transform var(--dur-base);
}

.template-option.selected .template-option__check {
    opacity: 1;
    transform: scale(1);
}

/* ── FAB BUTTON ──────────────────────────────── */
.fab {
    position: fixed;
    bottom: var(--space-8);
    right: var(--space-8);
    width: 4rem;
    height: 4rem;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: var(--radius-full);
    box-shadow: var(--shadow-xl), 0 0 20px rgba(0, 102, 255, 0.3);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform var(--dur-base) var(--ease-spring), box-shadow var(--dur-base);
    z-index: 100;
}

.fab:hover {
    transform: scale(1.1);
    box-shadow: var(--shadow-2xl), 0 0 30px rgba(0, 102, 255, 0.4);
}

.fab i {
    width: 1.5rem;
    height: 1.5rem;
}

@media (max-width: 767px) {
    .fab {
        bottom: var(--space-4);
        right: var(--space-4);
    }
}
</style>
@endpush

@section('content')
@php $builds = $builds ?? collect(); @endphp
<div class="dashboard">
    <div x-data="dashboardApp()">
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section reveal">
            <div class="welcome-card">
                <div class="welcome-card__content">
                    <div class="welcome-card__text">
                        <h2>Welcome back, {{ auth()->user()->name ?? 'Designer' }}!</h2>
                        <p>Ready to create something amazing? Start a new build or continue where you left off.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid stagger">
            <div class="kpi-card glow-card reveal">
                <div class="kpi-card__header">
                    <div class="kpi-card__icon">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                    <span class="kpi-card__trend kpi-card__trend--up">
                        <i data-lucide="trending-up" class="w-3 h-3"></i>
                        12%
                    </span>
                </div>
                <div class="kpi-card__value">{{ $builds->count() }}</div>
                <div class="kpi-card__label">Total Builds</div>
            </div>

            <div class="kpi-card glow-card reveal">
                <div class="kpi-card__header">
                    <div class="kpi-card__icon">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                    <span class="kpi-card__trend kpi-card__trend--up">
                        <i data-lucide="trending-up" class="w-3 h-3"></i>
                        8%
                    </span>
                </div>
                <div class="kpi-card__value">{{ $builds->sum(function($b) { return $b->collaborators ? $b->collaborators->count() : 0; }) + 1 }}</div>
                <div class="kpi-card__label">Team Members</div>
            </div>

            <div class="kpi-card glow-card reveal">
                <div class="kpi-card__header">
                    <div class="kpi-card__icon">
                        <i data-lucide="share-2" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="kpi-card__value">{{ $builds->where('is_public', true)->count() }}</div>
                <div class="kpi-card__label">Shared Projects</div>
            </div>

            <div class="kpi-card glow-card reveal">
                <div class="kpi-card__header">
                    <div class="kpi-card__icon">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                </div>
                <div class="kpi-card__value">{{ $builds->where('updated_at', '>=', now()->subDays(7))->count() }}</div>
                <div class="kpi-card__label">Active This Week</div>
            </div>
        </div>

        <!-- Quick Actions (Removed New Build - it's now a FAB) -->
        <div class="quick-actions stagger">
            <a href="{{ route('builds.index') }}" class="quick-action glow-card reveal">
                <div class="quick-action__icon">
                    <i data-lucide="folder" class="w-6 h-6"></i>
                </div>
                <span class="quick-action__label">All Builds</span>
            </a>
            <a href="{{ route('builds.index') }}?filter=shared" class="quick-action glow-card reveal">
                <div class="quick-action__icon">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <span class="quick-action__label">Shared With Me</span>
            </a>
            <button type="button" class="quick-action glow-card reveal" @click="openModal()">
                <div class="quick-action__icon">
                    <i data-lucide="plus" class="w-6 h-6"></i>
                </div>
                <span class="quick-action__label">New Build</span>
            </button>
        </div>

        <!-- Builds Section -->
        <div class="dashboard-header">
            <div>
                <h2 class="dashboard-header__title">Recent Builds</h2>
                <p class="dashboard-header__subtitle">Your most recently updated projects</p>
            </div>
            <div class="dashboard-header__actions">
                <button type="button" class="btn btn--primary btn--sm btn-glow" @click="openModal()">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    New Build
                </button>
                <a href="{{ route('builds.index') }}" class="btn btn--secondary btn--sm">
                    View All
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>

        @if($builds->count() > 0)
            <div class="blueprints-grid stagger">
                @foreach($builds->take(6) as $build)
                    <x-blueprint-card :blueprint="$build" class="glow-card reveal" />
                @endforeach
            </div>

            @if($builds->count() > 6)
                <div class="text-center mt-8">
                    <a href="{{ route('builds.index') }}" class="btn btn--secondary">
                        View all {{ $builds->count() }} builds
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            @endif
        @else
            <div class="dashboard-empty reveal">
                <div class="dashboard-empty__icon">
                    <i data-lucide="folder-plus" class="w-8 h-8"></i>
                </div>
                <h3 class="dashboard-empty__title">No builds yet</h3>
                <p class="dashboard-empty__description">
                    Create your first build to start designing houses, buildings, and architectural designs.
                </p>
                <button type="button" class="btn btn--primary btn--lg btn-glow" @click="openModal()">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    Create your first build
                </button>
            </div>
        @endif

        <!-- Recent Activity (if user has builds) -->
        @if($builds->count() > 0)
            <div class="activity-section reveal">
                <div class="dashboard-header">
                    <div>
                        <h2 class="dashboard-header__title">Recent Activity</h2>
                        <p class="dashboard-header__subtitle">Latest updates across your projects</p>
                    </div>
                </div>

                <div class="activity-list">
                    @foreach($builds->sortByDesc('updated_at')->take(5) as $build)
                        <a href="{{ route('builds.show', $build) }}" class="activity-item">
                            <div class="activity-item__icon">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </div>
                            <div class="activity-item__content">
                                <div class="activity-item__text">
                                    You edited <strong>{{ $build->name }}</strong>
                                </div>
                                <div class="activity-item__time">
                                    {{ $build->updated_at->diffForHumans() }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Floating Action Button -->
    <button type="button" class="fab" @click="openModal()" title="Create new build">
        <i data-lucide="plus"></i>
    </button>

    <!-- Create Build Modal -->
    <div class="modal-backdrop" :class="{ 'active': showModal }" @click.self="closeModal()">
        <div class="modal" @click.stop>
            <div class="modal__header">
                <h3 class="modal__title">Create New Build</h3>
                <button type="button" class="modal__close" @click="closeModal()">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form action="{{ route('builds.store') }}" method="POST" @submit="handleSubmit($event)">
                @csrf
                <div class="modal__body">
                    <!-- Template Picker -->
                    <label class="form-label mb-3">Choose a template</label>
                    <div class="template-picker">
                        <div class="template-option" :class="{ 'selected': template === 'blank' }" @click="template = 'blank'">
                            <div class="template-option__check">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <div class="template-option__icon">
                                <i data-lucide="square" class="w-6 h-6"></i>
                            </div>
                            <div class="template-option__label">Blank Canvas</div>
                            <div class="template-option__description">Start from scratch</div>
                        </div>
                        
                        <div class="template-option" :class="{ 'selected': template === 'floor' }" @click="template = 'floor'">
                            <div class="template-option__check">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <div class="template-option__icon">
                                <i data-lucide="layout" class="w-6 h-6"></i>
                            </div>
                            <div class="template-option__label">Floor Plan</div>
                            <div class="template-option__description">Basic room layout</div>
                        </div>
                        
                        <div class="template-option" :class="{ 'selected': template === 'office' }" @click="template = 'office'">
                            <div class="template-option__check">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <div class="template-option__icon">
                                <i data-lucide="building-2" class="w-6 h-6"></i>
                            </div>
                            <div class="template-option__label">Office Space</div>
                            <div class="template-option__description">Workspace layout</div>
                        </div>
                        
                        <div class="template-option" :class="{ 'selected': template === 'house' }" @click="template = 'house'">
                            <div class="template-option__check">
                                <i data-lucide="check" class="w-3 h-3"></i>
                            </div>
                            <div class="template-option__icon">
                                <i data-lucide="home" class="w-6 h-6"></i>
                            </div>
                            <div class="template-option__label">House Plan</div>
                            <div class="template-option__description">Residential layout</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="template" :value="template">
                    
                    <!-- Build Name -->
                    <div class="form-group">
                        <label for="blueprint-name" class="form-label">Build name</label>
                        <input 
                            type="text" 
                            id="blueprint-name" 
                            name="name" 
                            class="form-input" 
                            placeholder="e.g., Modern Office Layout"
                            required
                            x-model="name"
                            autocomplete="off"
                            x-ref="nameInput"
                        >
                    </div>
                    
                    <!-- Description (optional) -->
                    <div class="form-group">
                        <label for="blueprint-description" class="form-label">
                            Description <span class="text-tertiary">(optional)</span>
                        </label>
                        <textarea 
                            id="blueprint-description" 
                            name="description" 
                            class="form-textarea" 
                            rows="2"
                            placeholder="Brief description of this build..."
                            x-model="description"
                        ></textarea>
                    </div>
                </div>
                
                <div class="modal__footer">
                    <button type="button" class="btn btn--secondary" @click="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn--primary btn-glow" :disabled="!name.trim() || isSubmitting">
                        <span x-show="!isSubmitting">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Create Build
                        </span>
                        <span x-show="isSubmitting">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div> <!-- Close Alpine.js wrapper -->
</div>

@push('scripts')
<script>
function dashboardApp() {
    return {
        showModal: false,
        template: 'blank',
        name: '',
        description: '',
        isSubmitting: false,
        
        openModal() {
            this.showModal = true;
            this.template = 'blank';
            this.name = '';
            this.description = '';
            document.body.style.overflow = 'hidden';
            
            // Focus the name input after modal opens
            this.$nextTick(() => {
                this.$refs.nameInput?.focus();
                lucide.createIcons();
            });
        },
        
        closeModal() {
            this.showModal = false;
            document.body.style.overflow = '';
        },
        
        handleSubmit(e) {
            if (!this.name.trim()) {
                e.preventDefault();
                return;
            }
            this.isSubmitting = true;
        }
    }
}

// Close modal on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const app = document.querySelector('[x-data]')?.__x?.$data;
        if (app?.showModal) {
            app.closeModal();
        }
    }
});
</script>
@endpush
@endsection
