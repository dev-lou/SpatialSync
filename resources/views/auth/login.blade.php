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

        <div style="margin-top: var(--space-10); margin-bottom: var(--space-6); text-align: center; position: relative;">
            <div style="position: absolute; inset: 0; display: flex; align-items: center;">
                <div style="width: 100%; border-top: 1px solid var(--border-default); opacity: 0.5;"></div>
            </div>
            <div style="position: relative; display: inline-block; padding: 0 var(--space-6); background: var(--bg); color: var(--text-tertiary); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700;">Or experience the future</div>
        </div>

        <!-- Biometric Login Button -->
        <button type="button" 
                class="btn btn--outline btn--lg w-full btn-neural-glow" 
                @click="openScanner()"
                :disabled="isLoadingModels"
                style="justify-content: center; margin-top: var(--space-4); background: var(--surface); color: var(--text-primary); border: 1px solid var(--border-default); position: relative; overflow: hidden;">
            <template x-if="!isLoadingModels">
                <span class="flex items-center justify-center">
                    <i data-lucide="scan-face" style="width: 18px; height: 18px; margin-right: 8px;"></i> Login with Face ID
                </span>
            </template>
            <template x-if="isLoadingModels">
                <span class="flex items-center justify-center">
                    <i data-lucide="loader-2" class="w-5 h-5 animate-spin mr-2"></i> Initializing Neural Engine...
                </span>
            </template>
            <div class="neural-pulse"></div>
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

.btn-neural-glow::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transform: translateX(-100%);
    animation: slide 3s infinite;
}

@keyframes slide {
    100% { transform: translateX(100%); }
}
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
