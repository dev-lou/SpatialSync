<div {{ $attributes->class([
    'card',
    'card--hover' => $hover ?? false,
    'card--shadow' => $shadow ?? false,
]) }}>
    @if($title ?? false || isset($header))
        <div class="flex--between mb-4">
            @if($title ?? false)
                <h3 class="font-semibold text-lg text-primary">{{ $title }}</h3>
            @else
                {{ $header ?? '' }}
            @endif
            @if(isset($actions))
                <div class="flex gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-border">
            {{ $footer }}
        </div>
    @endif
</div>
