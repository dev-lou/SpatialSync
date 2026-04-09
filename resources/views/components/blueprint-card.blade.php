<style>
    .blueprint-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--surface);
        border-radius: var(--radius-xl);
        overflow: hidden;
        position: relative;
        text-decoration: none;
        transition: transform var(--dur-base) var(--ease-out), box-shadow var(--dur-base) var(--ease-out);
        box-shadow: var(--shadow-sm);
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

    .blueprint-card__status {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--success);
        background: var(--success-light);
        padding: 2px 6px;
        border-radius: var(--radius-sm);
    }
</style>

<a href="{{ route('builds.show', $blueprint) }}" class="blueprint-card">
    <div class="blueprint-card__thumb">
        <div class="blueprint-card__grid">
            <svg width="100%" height="100%">
                <defs>
                    <pattern id="grid-{{ $blueprint->id }}" width="24" height="24" patternUnits="userSpaceOnUse">
                        <path d="M 24 0 L 0 0 0 24" fill="none" stroke="currentColor" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid-{{ $blueprint->id }})"/>
            </svg>
        </div>
        <div class="blueprint-card__overlay"></div>
        <div class="blueprint-card__icon-wrap">
            <div class="blueprint-card__icon">
                <i data-lucide="layout" class="text-accent"></i>
            </div>
        </div>
    </div>

    <div class="blueprint-card__body">
        <div class="blueprint-card__header">
            <h3 class="blueprint-card__title">{{ $blueprint->name }}</h3>
            
            <div class="blueprint-card__avatar-stack">
                @php 
                    $members = $blueprint->members->take(3);
                    $totalMembers = $blueprint->members->count();
                @endphp
                
                @if($totalMembers > 3)
                    <div class="blueprint-card__avatar-item" style="background: var(--bg-tertiary); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: var(--text-secondary);">
                        +{{ $totalMembers - 3 }}
                    </div>
                @endif

                @foreach($members as $member)
                    <div class="blueprint-card__avatar-item">
                        <x-avatar :name="$member->name" size="sm" style="width: 24px; height: 24px;" />
                    </div>
                @endforeach
            </div>
        </div>

        <p class="blueprint-card__desc">{{ $blueprint->description ?? 'No description provided for this design project.' }}</p>
        
        <div class="blueprint-card__footer">
            <div class="blueprint-card__meta-item">
                <i data-lucide="clock"></i>
                <span>{{ $blueprint->updated_at->diffForHumans() }}</span>
            </div>
            <div class="blueprint-card__meta-group">
                <div class="blueprint-card__meta-item">
                    <i data-lucide="users"></i>
                    <span style="font-weight: 700; color: var(--text-secondary);">{{ $blueprint->members->count() }}</span>
                </div>
                @if($blueprint->is_public)
                    <span class="blueprint-card__status">Public</span>
                @endif
            </div>
        </div>
    </div>
</a>

