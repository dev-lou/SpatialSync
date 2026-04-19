@extends('layouts.app')

@section('content')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container" style="padding-top: calc(var(--header-height) + var(--space-20)); padding-bottom: var(--space-20); text-align: center;">
    <div style="max-width: 600px; margin: 0 auto;" class="reveal">
        <div style="width: 80px; height: 80px; background: var(--success-light); color: var(--success); border-radius: 50%; display: grid; place-items: center; margin: 0 auto var(--space-8);">
            <i data-lucide="check" style="width: 40px; height: 40px;"></i>
        </div>
        
        <h1 style="font-family: var(--font-display); font-size: 4rem; font-weight: 900; line-height: 1; letter-spacing: -0.02em; margin-bottom: var(--space-6);">
            Welcome to <span>the Next Level.</span>
        </h1>
        
        <p style="font-size: 1.25rem; color: var(--text-secondary); margin-bottom: var(--space-10);">
            Your workspace has been upgraded. You now have unlimited builds, priority support, and team collaboration features enabled.
        </p>

        <div style="display: flex; gap: var(--space-4); justify-content: center;">
            <a href="{{ route('dashboard') }}" class="btn btn--primary btn--lg btn-glow">
                Go to Dashboard
            </a>
            <a href="{{ route('pricing') }}" class="btn btn--secondary btn--lg">
                View Plan Pricing
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Payment Successful!',
            text: 'Your account has been upgraded to PRO.',
            icon: 'success',
            background: 'var(--surface)',
            color: 'var(--text-primary)',
            confirmButtonColor: 'var(--accent)',
            confirmButtonText: 'Great!',
            backdrop: `
                rgba(0,0,123,0.1)
                left top
                no-repeat
            `
        });
    });
</script>

<style>
h1 span {
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
</style>
@endsection
