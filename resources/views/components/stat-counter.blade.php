{{-- Animated Stat Counter Component --}}
@props([
    'value',
    'label',
    'prefix' => '',
    'suffix' => '',
    'duration' => 2000,
    'icon' => null
])

<div {{ $attributes->merge(['class' => 'stat-counter reveal']) }}>
    @if($icon)
        <div class="stat-counter__icon">
            <i data-lucide="{{ $icon }}" class="w-6 h-6"></i>
        </div>
    @endif
    <span 
        class="stat-counter__number"
        data-count-to="{{ $value }}"
        data-count-prefix="{{ $prefix }}"
        data-count-suffix="{{ $suffix }}"
        data-count-duration="{{ $duration }}"
    >{{ $prefix }}0{{ $suffix }}</span>
    <span class="stat-counter__label">{{ $label }}</span>
</div>
