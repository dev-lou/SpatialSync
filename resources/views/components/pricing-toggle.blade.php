{{-- Pricing Toggle (Monthly/Annual) Component --}}
@props([
    'discount' => 20
])

<div {{ $attributes->merge(['class' => 'pricing-toggle']) }} x-data="{ annual: false }">
    <span class="pricing-toggle__label" :class="{ 'pricing-toggle__label--active': !annual }">Monthly</span>
    
    <button 
        type="button"
        class="pricing-toggle__switch"
        :class="{ 'pricing-toggle__switch--active': annual }"
        @click="annual = !annual; $dispatch('billing-change', { annual })"
        role="switch"
        :aria-checked="annual"
        aria-label="Toggle billing period"
    >
        <span class="pricing-toggle__thumb"></span>
    </button>
    
    <span class="pricing-toggle__label" :class="{ 'pricing-toggle__label--active': annual }">
        Annual
        <span class="pricing-toggle__badge">Save {{ $discount }}%</span>
    </span>
</div>
