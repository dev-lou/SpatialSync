@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: calc(var(--header-height) + var(--space-12)); padding-bottom: var(--space-20);">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div class="reveal" style="margin-bottom: var(--space-10);">
            <h1 style="font-family: var(--font-display); font-size: 3.5rem; font-weight: 900; line-height: 1; letter-spacing: -0.02em; margin-bottom: var(--space-4);">
                Complete your <span>upgrade</span>
            </h1>
            <p style="font-size: 1.25rem; color: var(--text-secondary); max-width: 600px;">
                You're one step away from unlocking the full power of ConstructHub.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: var(--space-12); align-items: start;" 
             x-data="{ 
                processing: false, 
                cardNumber: '4242 4242 4242 4242',
                formatCard() {
                    let v = this.cardNumber.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    let matches = v.match(/\d{4,16}/g);
                    let match = matches && matches[0] || '';
                    let parts = [];
                    for (let i = 0, len = match.length; i < len; i += 4) {
                        parts.push(match.substring(i, i + 4));
                    }
                    if (parts.length > 0) {
                        this.cardNumber = parts.join(' ');
                    } else {
                        this.cardNumber = v;
                    }
                }
             }">
            <!-- Checkout Form -->
            <div class="card glow-card reveal" style="padding: var(--space-10); border-radius: 32px;">
                <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form" @submit="processing = true">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan }}">

                    <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: var(--space-8); display: flex; align-items: center; gap: var(--space-3);">
                        <i data-lucide="credit-card" style="color: var(--accent);"></i>
                        Payment Information
                    </h2>

                    <div style="display: grid; gap: var(--space-6);">
                        <x-input 
                            label="Cardholder Name" 
                            name="card_name" 
                            value="{{ $auth_user_name ?? 'John Doe' }}" 
                            placeholder="John Doe" 
                            required 
                        />

                        <div style="position: relative;">
                            <label class="input-label">Card Number</label>
                            <div style="position: relative;">
                                <input 
                                    type="text" 
                                    class="input" 
                                    x-model="cardNumber"
                                    @input="formatCard"
                                    placeholder="0000 0000 0000 0000" 
                                    style="padding-left: 100px;"
                                >
                                <div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); display: flex; align-items: center; gap: 8px; pointer-events: none;">
                                    <div style="display: flex; gap: 4px;">
                                        <svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="24" height="16" rx="2" fill="#EB001B"/>
                                            <circle cx="15" cy="8" r="7" fill="#F79E1B" fill-opacity="0.8"/>
                                            <circle cx="9" cy="8" r="7" fill="#EB001B"/>
                                        </svg>
                                        <svg width="24" height="16" viewBox="0 0 24 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect width="24" height="16" rx="2" fill="#1A1F71"/>
                                            <path d="M11.5 11L12.5 5H14L13 11H11.5ZM17.5 5.5C17.2 5.2 16.7 5 16.1 5C15 5 14.2 5.6 14.1 6.6C14 7.5 14.8 8 15.4 8.3C16 8.6 16.2 8.8 16.2 9.1C16.2 9.5 15.7 9.7 15.3 9.7C14.7 9.7 14.4 9.5 14.1 9.3L13.8 10.3C14.1 10.5 14.7 10.7 15.2 10.7C16.4 10.7 17.2 10.1 17.3 9.2C17.4 8.7 17.1 8.3 16.4 8C15.9 7.7 15.7 7.5 15.7 7.3C15.7 7.1 16 6.9 16.4 6.9C16.8 6.9 17.1 7 17.3 7.1L17.5 6.1L17.5 5.5ZM21.1 5H19.7C19.3 5 19 5.2 18.9 5.5L16.5 11H18L18.3 10.2H20.1L20.3 11H21.6L21.1 5ZM18.7 9.2L19.2 7.8L19.5 8.7L19.7 9.2H18.7ZM9.5 5L8.1 11H9.6L11 5H9.5V5Z" fill="white"/>
                                        </svg>
                                    </div>
                                    <div style="width: 1px; height: 20px; background: var(--border-strong); margin-left: 4px;"></div>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
                            <x-input label="Expiry Date" name="expiry" value="12/28" placeholder="MM/YY" required />
                            <x-input label="CVC" name="cvc" value="123" placeholder="123" required />
                        </div>
                    </div>

                    <div style="margin-top: var(--space-10); padding-top: var(--space-8); border-top: 1px solid var(--border-default);">
                        <button type="submit" class="btn btn--primary btn--lg w-full btn-glow" :disabled="processing" style="justify-content: center; height: 64px; font-size: 1.125rem; gap: 12px;">
                            <template x-if="!processing">
                                <span>Upgrade to {{ ucfirst($plan) }} Plan</span>
                            </template>
                            <template x-if="processing">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation: spin 1s linear infinite;">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity: 0.25;"></circle>
                                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" style="opacity: 0.75;"></path>
                                    </svg>
                                    <span>Processing Payment...</span>
                                </div>
                            </template>
                        </button>
                        <p style="text-align: center; font-size: 0.875rem; color: var(--text-tertiary); margin-top: var(--space-4); display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i data-lucide="lock" style="width: 14px; height: 14px;"></i>
                            Secure checkout powered by simulated encryption
                        </p>
                    </div>
                </form>
            </div>

            <!-- Summary Sidebar -->
            <aside class="reveal" style="position: sticky; top: calc(var(--header-height) + var(--space-8));">
                <div class="card" style="background: var(--bg-secondary); border-style: dashed; padding: var(--space-8); border-radius: 24px;">
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: var(--space-6);">Order Summary</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: var(--space-4);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">{{ ucfirst($plan) }} Plan (Monthly)</span>
                            <span style="font-weight: 600;">${{ $amount }}.00</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">Tax (0%)</span>
                            <span style="font-weight: 600;">$0.00</span>
                        </div>
                        
                        <div style="margin-top: var(--space-4); padding-top: var(--space-4); border-top: 1px solid var(--border-strong); display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 1.125rem; font-weight: 800;">Total</span>
                            <span style="font-size: 1.5rem; font-weight: 900; color: var(--accent);">${{ $amount }}.00</span>
                        </div>
                    </div>

                    <div style="margin-top: var(--space-8);">
                        <h4 style="font-size: 0.875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-tertiary); margin-bottom: var(--space-4);">What's included:</h4>
                        <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: var(--space-3);">
                            <li style="display: flex; align-items: center; gap: var(--space-2); font-size: 0.875rem; color: var(--text-secondary);">
                                <i data-lucide="check" style="width: 16px; height: 16px; color: var(--success);"></i>
                                Unlimited Builds
                            </li>
                            <li style="display: flex; align-items: center; gap: var(--space-2); font-size: 0.875rem; color: var(--text-secondary);">
                                <i data-lucide="check" style="width: 16px; height: 16px; color: var(--success);"></i>
                                Priority Support
                            </li>
                            <li style="display: flex; align-items: center; gap: var(--space-2); font-size: 0.875rem; color: var(--text-secondary);">
                                <i data-lucide="check" style="width: 16px; height: 16px; color: var(--success);"></i>
                                Team Collaboration
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
.pricing-hero__title span {
    background: linear-gradient(to right, var(--accent), #9333EA, var(--accent));
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 4s linear infinite;
}
@keyframes text-shine {
    to { background-position: 200% center; }
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
@endsection
