@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <x-input
            label="Email"
            name="email"
            type="email"
            placeholder="you@company.com"
            :required="true"
            autocomplete="email"
        />

        <!-- Custom Password Field with Toggle -->
        <div class="input-group">
            <label class="input-label" for="password">Password</label>
            <div style="position: relative;">
                <input class="input" type="password" id="password" name="password"
                    placeholder="Enter your password" autocomplete="current-password" required
                    style="padding-right: 48px;">
                <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Show password" aria-controls="password" 
                    style="position: absolute; right: 2px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center; color: var(--text-tertiary); background: none; border: none; cursor: pointer; border-radius: var(--radius-sm);">
                    <i data-lucide="eye" id="pw-icon" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
        </div>

        <div class="flex--between" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-6);">
            <label class="flex items-center gap-2 text-sm cursor-pointer" style="display: flex; align-items: center; gap: 8px; font-size: var(--text-sm); color: var(--text-secondary);">
                <input type="checkbox" name="remember" style="width: 16px; height: 16px; border-radius: 4px; border: 1px solid var(--border-default); outline: none; cursor: pointer; accent-color: var(--accent);">
                Remember me
            </label>
            <a href="#" style="font-size: var(--text-sm); font-weight: 500; color: var(--accent); text-decoration: none;">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn--primary btn--lg w-full" style="justify-content: center;">
            Sign in
        </button>

    </form>

    <div class="auth-footer" style="text-align: center; margin-top: var(--space-8); padding-top: var(--space-6); border-top: 1px solid var(--border-default);">
        Don't have an account? <a href="{{ route('register') }}" style="color: var(--accent); font-weight: 600;">Create workspace</a>
    </div>
@endsection

@push('scripts')
<style>
.field__input:focus { border-color: var(--accent) !important; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
.pw-toggle:hover svg { color: var(--text-primary) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const pwInput = document.getElementById('password');
    const toggleBtn = document.getElementById('pw-toggle');

    toggleBtn?.addEventListener('click', function() {
        const isText = pwInput.type === 'text';
        pwInput.type = isText ? 'password' : 'text';
        const iconName = isText ? 'eye' : 'eye-off';
        this.innerHTML = `<i data-lucide="${iconName}" id="pw-icon" style="width: 16px; height: 16px;"></i>`;
        lucide.createIcons();
    });
});
</script>
@endpush
