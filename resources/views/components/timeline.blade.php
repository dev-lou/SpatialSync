{{-- Timeline Component --}}
@props(['items' => []])

<div {{ $attributes->merge(['class' => 'timeline']) }}>
    @foreach($items as $index => $item)
        <div class="timeline__item reveal" style="--delay: {{ $index * 100 }}ms;">
            <div class="timeline__dot">
                @if(isset($item['icon']))
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
                @endif
            </div>
            <div class="timeline__content">
                <span class="timeline__year">{{ $item['year'] }}</span>
                <h4 class="timeline__title">{{ $item['title'] }}</h4>
                <p class="timeline__description">{{ $item['description'] }}</p>
            </div>
        </div>
    @endforeach
</div>
