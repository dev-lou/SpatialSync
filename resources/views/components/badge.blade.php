<span {{ $attributes->class([
    'badge',
    'badge--' . ($variant ?? 'default'),
]) }}>
    @if(isset($icon))
        <i data-lucide="{{ $icon }}" class="w-3 h-3"></i>
    @endif
    {{ $slot }}
</span>
