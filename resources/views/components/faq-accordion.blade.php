{{-- FAQ Accordion Component --}}
@props(['items' => []])

<div {{ $attributes->merge(['class' => 'faq-accordion']) }} x-data="{ openIndex: null }">
    @foreach($items as $index => $item)
        <div class="faq-accordion__item reveal" style="--delay: {{ $index * 50 }}ms;">
            <button
                type="button"
                class="faq-accordion__trigger"
                :class="{ 'faq-accordion__trigger--open': openIndex === {{ $index }} }"
                @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                :aria-expanded="openIndex === {{ $index }}"
                aria-controls="faq-content-{{ $index }}"
            >
                <span class="faq-accordion__question">{{ $item['question'] }}</span>
                <span class="faq-accordion__icon">
                    <i data-lucide="chevron-down" class="w-5 h-5"></i>
                </span>
            </button>
            <div 
                id="faq-content-{{ $index }}"
                class="faq-accordion__content"
                x-show="openIndex === {{ $index }}"
                x-collapse
                x-cloak
            >
                <p class="faq-accordion__answer">{{ $item['answer'] }}</p>
            </div>
        </div>
    @endforeach
</div>
