<button
    {{ $attributes->class([
        'btn',
        'btn--' . ($variant ?? 'primary'),
        'btn--' . ($size ?? 'md'),
        'btn--loading' => $loading ?? false,
        'btn--disabled' => $disabled ?? false,
    ])->merge([
        'type' => $type ?? 'button',
        'disabled' => $disabled || $loading ? true : null,
    ]) }}
>
    @if($loading)
        <span class="btn__spinner"></span>
    @endif
    @if(isset($icon) && !$loading)
        <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
    @endif
    {{ $slot }}
</button>
