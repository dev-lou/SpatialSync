<a href="{{ route('builds.show', $blueprint) }}" class="group block">
    <div class="card card--hover h-full">
        <div class="aspect-[4/3] bg-secondary rounded-lg mb-4 overflow-hidden relative">
            <div class="absolute inset-0 opacity-20">
                <svg width="100%" height="100%">
                    <defs>
                        <pattern id="grid-{{ $blueprint->id }}" width="20" height="20" patternUnits="userSpaceOnUse">
                            <path d="M 20 0 L 0 0 0 20" fill="none" stroke="currentColor" stroke-width="0.5" class="text-border"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid-{{ $blueprint->id }})"/>
                </svg>
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <i data-lucide="layout" class="w-12 h-12 text-tertiary group-hover:text-accent transition-colors"></i>
            </div>
            <div class="absolute bottom-3 right-3 flex -space-x-2">
                @foreach($blueprint->members->take(3) as $member)
                    <x-avatar :name="$member->name" size="sm" class="border-2 border-surface" />
                @endforeach
                @if($blueprint->members->count() > 3)
                    <div class="w-8 h-8 rounded-full bg-tertiary border-2 border-surface flex items-center justify-center text-xs font-medium text-primary">
                        +{{ $blueprint->members->count() - 3 }}
                    </div>
                @endif
            </div>
        </div>
        <div>
            <h3 class="font-semibold text-primary group-hover:text-accent transition-colors mb-1">
                {{ $blueprint->name }}
            </h3>
            <p class="text-sm text-secondary line-clamp-2 mb-3">
                {{ $blueprint->description ?? 'No description' }}
            </p>
            <div class="flex items-center justify-between text-xs text-tertiary">
                <span>{{ $blueprint->updated_at->diffForHumans() }}</span>
                <span class="flex items-center gap-1">
                    <i data-lucide="users" class="w-3 h-3"></i>
                    {{ $blueprint->members->count() }}
                </span>
            </div>
        </div>
    </div>
</a>
