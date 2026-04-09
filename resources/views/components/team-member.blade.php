{{-- Team Member Card Component --}}
@props([
    'name',
    'role',
    'image',
    'bio' => null,
    'linkedin' => null,
    'twitter' => null
])

<div {{ $attributes->merge(['class' => 'team-card tilt-card glow-card reveal']) }}>
    <div class="team-card__image-wrapper">
        <img 
            src="{{ $image }}" 
            alt="{{ $name }}"
            class="team-card__image"
            width="280"
            height="280"
            loading="lazy"
        >
    </div>
    <div class="team-card__info">
        <h4 class="team-card__name">{{ $name }}</h4>
        <p class="team-card__role">{{ $role }}</p>
        @if($bio)
            <p class="team-card__bio">{{ $bio }}</p>
        @endif
        @if($linkedin || $twitter)
            <div class="team-card__social">
                @if($linkedin)
                    <a href="{{ $linkedin }}" target="_blank" rel="noopener" class="team-card__link" aria-label="LinkedIn">
                        <i data-lucide="linkedin" class="w-4 h-4"></i>
                    </a>
                @endif
                @if($twitter)
                    <a href="{{ $twitter }}" target="_blank" rel="noopener" class="team-card__link" aria-label="Twitter">
                        <i data-lucide="twitter" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
