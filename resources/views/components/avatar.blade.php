<div {{ $attributes->class([
    'avatar',
    'avatar--sm' => ($size ?? 'md') === 'sm',
    'avatar--lg' => ($size ?? 'md') === 'lg',
    'avatar--xl' => ($size ?? 'md') === 'xl',
]) }}>
    @if(isset($src) && $src)
        <img 
            src="{{ $src }}" 
            alt="{{ $name ?? 'User avatar' }}" 
            width="{{ ($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40)) }}"
            height="{{ ($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40)) }}"
            loading="lazy"
        >
    @else
        {{ strtoupper(substr($name ?? 'U', 0, 1)) }}
    @endif
</div>
