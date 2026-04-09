<div {{ $attributes->merge(['class' => 'avatar' . ($size ?? 'md' ? ' avatar--' . ($size ?? 'md') : '')]) }}>
    @if(isset($src) && $src)
        <img 
            src="{{ $src }}" 
            alt="{{ $name ?? 'User avatar' }}" 
            width="{{ ($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40)) }}"
            height="{{ ($size ?? 'md') === 'sm' ? 32 : (($size ?? 'md') === 'lg' ? 48 : (($size ?? 'md') === 'xl' ? 64 : 40)) }}"
            loading="lazy"
            class="w-full h-full object-cover"
        >
    @else
        <span class="avatar__initials">{{ strtoupper(substr($name ?? 'U', 0, 1)) }}</span>
    @endif
</div>
