{{-- Testimonial Card Component --}}
@props([
    'quote',
    'name',
    'role' => null,
    'title' => null,
    'company' => null,
    'image' => null,
    'featured' => false
])

@php
    // Support both 'role' (combined) and 'title'/'company' (separate) props
    $displayRole = $role ?? (($title && $company) ? "{$title}, {$company}" : ($title ?? $company ?? ''));
@endphp

<blockquote {{ $attributes->merge(['class' => 'testimonial-card' . ($featured ? ' testimonial-card--featured' : '') . ' glow-card reveal']) }}>
    <div class="testimonial-card__quote">
        <svg class="testimonial-card__icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
        </svg>
        <p class="testimonial-card__text">{{ $quote }}</p>
    </div>
    
    <footer class="testimonial-card__author">
        @if($image)
            <img 
                src="{{ $image }}" 
                alt="{{ $name }}" 
                class="testimonial-card__avatar"
                width="48"
                height="48"
                loading="lazy"
            >
        @else
            <div class="testimonial-card__avatar testimonial-card__avatar--placeholder">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
        @endif
        <div class="testimonial-card__info">
            <cite class="testimonial-card__name">{{ $name }}</cite>
            @if($displayRole)
                <span class="testimonial-card__role">{{ $displayRole }}</span>
            @endif
        </div>
    </footer>
</blockquote>
