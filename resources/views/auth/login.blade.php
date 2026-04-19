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
        <div class="field field-password" id="field-password" style="position: relative; margin-top: var(--space-4); margin-bottom: var(--space-4);">
            <label class="field__label" for="password" style="display: block; font-size: var(--text-sm); font-weight: 500; color: var(--text-primary); margin-bottom: var(--space-2);">Password</label>
            <div style="position: relative;">
                <input class="field__input" type="password" id="password" name="password"
                    placeholder="Enter your password" autocomplete="current-password" required
                    style="width: 100%; border: 1.5px solid var(--border-default); padding: 10px 14px; border-radius: var(--radius-md); background: var(--surface); color: var(--text-primary); font-size: var(--text-base); transition: all 0.2s ease;">
                <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Show password" aria-controls="password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center; color: var(--text-tertiary); background: none; border: none; cursor: pointer; border-radius: var(--radius-sm);">
                    <i data-lucide="eye" id="pw-icon" style="width: 16px; height: 16px;"></i>
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

        <div style="margin-top: var(--space-6); text-align: center; position: relative;">
            <div style="position: absolute; inset: 0; display: flex; align-items: center;">
                <div style="width: 100%; border-top: 1px solid var(--border-default);"></div>
            </div>
            <div style="position: relative; display: inline-block; padding: 0 var(--space-4); background: var(--bg); color: var(--text-tertiary); font-size: var(--text-sm);">Or continue with</div>
        </div>

        <button type="button" class="btn btn--outline btn--lg w-full" style="justify-content: center; margin-top: var(--space-4); background: var(--surface); color: var(--text-primary); border: 1px solid var(--border-default);">
            <i data-lucide="github" style="width: 18px; height: 18px; margin-right: 8px;"></i> GitHub
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
        
        // Re-inject the <i> tag with the new icon name
        const iconName = isText ? 'eye' : 'eye-off';
        this.innerHTML = `<i data-lucide="${iconName}" id="pw-icon" style="width: 16px; height: 16px;"></i>`;
        
        lucide.createIcons();
        this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
    });
});
</script>
@endpush
