@extends('layouts.app')

@section('content')
<div class="container" style="padding-top: calc(var(--header-height) + var(--space-12)); padding-bottom: var(--space-20);">
    <div style="max-width: 800px; margin: 0 auto;">
        <div class="reveal" style="text-align: center; margin-bottom: var(--space-12);">
            <h1 style="font-family: var(--font-display); font-size: 3.5rem; font-weight: 900; line-height: 1; letter-spacing: -0.02em; margin-bottom: var(--space-4);">
                Let's scale <span>together.</span>
            </h1>
            <p style="font-size: 1.25rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto;">
                Custom integrations, dedicated support, and enterprise-grade security for your entire organization.
            </p>
        </div>

        <div class="card glow-card reveal" style="padding: var(--space-10); border-radius: 32px;">
            @if(session('success'))
                <div style="background: var(--success-light); color: var(--success); padding: var(--space-4); border-radius: var(--radius-md); margin-bottom: var(--space-8); display: flex; align-items: center; gap: var(--space-3);">
                    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('contact.sales') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-bottom: var(--space-6);">
                    <x-input label="First Name" name="first_name" placeholder="Alex" required />
                    <x-input label="Last Name" name="last_name" placeholder="Smith" required />
                </div>

                <div style="margin-bottom: var(--space-6);">
                    <x-input label="Work Email" name="email" type="email" placeholder="alex@company.com" required />
                </div>

                <div style="margin-bottom: var(--space-6);">
                    <x-input label="Company Name" name="company" placeholder="Construct Corp" required />
                </div>

                <div style="margin-bottom: var(--space-8);">
                    <label class="input-label" style="display: block; margin-bottom: var(--space-2);">Estimated Team Size</label>
                    <select class="input" style="appearance: auto;">
                        <option>10-50 employees</option>
                        <option>51-200 employees</option>
                        <option>201-500 employees</option>
                        <option>501+ employees</option>
                    </select>
                </div>

                <button type="submit" class="btn btn--primary btn--lg w-full btn-glow" style="justify-content: center; height: 60px;">
                    Send Inquiry
                </button>
            </form>
        </div>
    </div>
</div>

<style>
h1 span {
    background: linear-gradient(to right, #9333EA, #A855F7, #9333EA);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: text-shine 4s linear infinite;
}
@keyframes text-shine {
    to { background-position: 200% center; }
}
</style>
@endsection
