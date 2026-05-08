@extends('layouts.auth')
@section('title', 'Create Account')
@section('subtitle', 'Join the future of architectural collaboration.')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="auth-form" id="register-form">
        @csrf

        <x-input
            label="Full Name"
            name="name"
            type="text"
            placeholder="John Smith"
            :required="true"
            autocomplete="name"
        />

        <x-input
            label="Email"
            name="email"
            type="email"
            placeholder="you@company.com"
            :required="true"
            autocomplete="email"
        />

        <!-- Custom Password Strength Field -->
        <div class="field field-password" id="field-password" style="position: relative; margin-top: var(--space-4);">
            <label class="field__label" for="password" style="display: block; font-size: var(--text-sm); font-weight: 500; color: var(--text-primary); margin-bottom: var(--space-2);">Password</label>
            <div style="position: relative;">
                <input class="field__input" type="password" id="password" name="password"
                    placeholder="Create a strong password" autocomplete="new-password" required
                    aria-describedby="pw-strength-desc"
                    style="width: 100%; border: 1.5px solid var(--border-default); padding: 10px 14px; border-radius: var(--radius-md); background: var(--surface); color: var(--text-primary); font-size: var(--text-base); transition: all 0.2s ease;">
                <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Show password" aria-controls="password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center; color: var(--text-tertiary); background: none; border: none; cursor: pointer; border-radius: var(--radius-sm);">
                    <i data-lucide="eye" id="pw-icon" style="width: 16px; height: 16px;"></i>
                </button>
            </div>

            <!-- Strength meter -->
            <div class="pw-strength" id="pw-strength" aria-hidden="true" style="display: flex; align-items: center; gap: var(--space-3); margin-top: var(--space-3);">
                <div class="pw-strength__bar" style="flex: 1; height: 4px; background: var(--bg-secondary); border-radius: var(--radius-full); overflow: hidden;">
                    <div class="pw-strength__fill" id="pw-fill" style="height: 100%; border-radius: var(--radius-full); transition: width 0.3s ease-out, background 0.3s; width: 0;"></div>
                </div>
                <span class="pw-strength__label" id="pw-label" style="font-size: var(--text-xs); font-weight: 600; min-width: 44px;"></span>
            </div>
            <span class="sr-only" id="pw-strength-desc" aria-live="polite" style="display: none;"></span>

            <!-- Requirements checklist -->
            <ul class="pw-requirements" aria-label="Password requirements" style="list-style: none; display: flex; flex-direction: column; gap: var(--space-1); margin-top: var(--space-3); padding-left: 0;">
                <li class="pw-req" data-req="length" style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary); transition: color 0.2s ease;"><i data-lucide="circle" style="width: 12px; height: 12px;"></i> At least 8 characters</li>
                <li class="pw-req" data-req="upper" style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary); transition: color 0.2s ease;"><i data-lucide="circle" style="width: 12px; height: 12px;"></i> One uppercase letter</li>
                <li class="pw-req" data-req="number" style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary); transition: color 0.2s ease;"><i data-lucide="circle" style="width: 12px; height: 12px;"></i> One number</li>
                <li class="pw-req" data-req="special" style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--text-xs); color: var(--text-tertiary); transition: color 0.2s ease;"><i data-lucide="circle" style="width: 12px; height: 12px;"></i> One special character</li>
            </ul>
        </div>

        <!-- Confirm password -->
        <div class="field" id="field-confirm" style="margin-top: var(--space-4); margin-bottom: var(--space-6);">
            <label class="field__label" for="confirm-password" style="display: block; font-size: var(--text-sm); font-weight: 500; color: var(--text-primary); margin-bottom: var(--space-2);">Confirm password</label>
            <div style="position: relative;">
                <input class="field__input" type="password" id="confirm-password"
                    name="password_confirmation" placeholder="Confirm your password" autocomplete="new-password" required
                    aria-describedby="confirm-desc" style="width: 100%; border: 1.5px solid var(--border-default); padding: 10px 14px; border-radius: var(--radius-md); background: var(--surface); color: var(--text-primary); font-size: var(--text-base); transition: all 0.2s ease;">
                <button type="button" class="pw-toggle" id="pw-toggle-confirm" aria-label="Show password" aria-controls="confirm-password" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display: grid; place-items: center; color: var(--text-tertiary); background: none; border: none; cursor: pointer; border-radius: var(--radius-sm);">
                    <i data-lucide="eye" id="pw-icon-confirm" style="width: 16px; height: 16px;"></i>
                </button>
            </div>
            <span class="field__error" role="alert" id="confirm-desc" style="display: block; color: var(--error); font-size: var(--text-xs); margin-top: var(--space-1);"></span>
        </div>

        <button type="submit" class="btn btn--primary btn--lg w-full" style="justify-content: center; margin-top: var(--space-2);">
            Create account
        </button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}" style="color: var(--accent); font-weight: 600;">Sign in to workspace</a>
    </div>
@endsection

@push('scripts')
<style>
/* JS toggled classes for strength meter */
.pw-strength__fill.weak { width: 25% !important; background: var(--error) !important; }
.pw-strength__fill.fair { width: 50% !important; background: var(--warning) !important; }
.pw-strength__fill.good { width: 75% !important; background: #22C55E !important; }
.pw-strength__fill.strong { width: 100% !important; background: var(--success) !important; }

.pw-strength__label.weak { color: var(--error) !important; }
.pw-strength__label.fair { color: var(--warning) !important; }
.pw-strength__label.good { color: #16A34A !important; }
.pw-strength__label.strong { color: var(--success) !important; }

.pw-req.met { color: var(--success) !important; }
.pw-req.met svg { color: var(--success) !important; }
.field__input:focus { border-color: var(--accent) !important; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
.pw-toggle:hover svg { color: var(--text-primary) !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Password strength logic
    const pwInput   = document.getElementById('password');
    const pwFill    = document.getElementById('pw-fill');
    const pwLabel   = document.getElementById('pw-label');
    const pwDesc    = document.getElementById('pw-strength-desc');
    const confirmEl = document.getElementById('confirm-password');

    // Show/hide toggles
    function setupToggle(btnId, inputId, iconId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        
        btn?.addEventListener('click', function() {
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            
            // Re-inject the <i> tag with the new icon name so Lucide can re-process it
            const iconName = isText ? 'eye' : 'eye-off';
            btn.innerHTML = `<i data-lucide="${iconName}" id="${iconId}" style="width: 16px; height: 16px;"></i>`;
            
            lucide.createIcons();
            this.setAttribute('aria-label', isText ? 'Show password' : 'Hide password');
        });
    }

    setupToggle('pw-toggle', 'password', 'pw-icon');
    setupToggle('pw-toggle-confirm', 'confirm-password', 'pw-icon-confirm');

    const rules = {
        length:  v => v.length >= 8,
        upper:   v => /[A-Z]/.test(v),
        number:  v => /\d/.test(v),
        special: v => /[!@#$%^&*(),.?":{}|<>]/.test(v),
    };

    function getStrength(v) {
        const score = Object.values(rules).filter(fn => fn(v)).length;
        if (score <= 1) return { level: 'weak',   label: 'Weak' };
        if (score === 2) return { level: 'fair',  label: 'Fair' };
        if (score === 3) return { level: 'good',  label: 'Good' };
        return { level: 'strong', label: 'Strong' };
    }

    pwInput?.addEventListener('input', () => {
        const val = pwInput.value;
        Object.entries(rules).forEach(([key, fn]) => {
            const reqEl = document.querySelector(`[data-req="${key}"]`);
            const iconEl = reqEl?.querySelector('i');
            const isMet = fn(val);
            
            reqEl?.classList.toggle('met', isMet);
            if (iconEl) {
                iconEl.setAttribute('data-lucide', isMet ? 'check-circle-2' : 'circle');
            }
        });
        lucide.createIcons();

        if (!val) { 
            pwFill.style.width = '0'; 
            pwFill.className = 'pw-strength__fill';
            pwLabel.textContent = ''; return; 
        }
        
        const { level, label } = getStrength(val);
        pwFill.className = `pw-strength__fill ${level}`;
        pwLabel.className = `pw-strength__label ${level}`;
        pwLabel.textContent = label;
        pwDesc.textContent = `Password strength: ${label}`;
    });

    confirmEl?.addEventListener('input', () => {
        const field = document.getElementById('field-confirm');
        const errorEl = document.getElementById('confirm-desc');
        const match = confirmEl.value === pwInput.value;
        if(confirmEl.value) {
            errorEl.textContent = match ? '' : 'Passwords do not match';
        } else {
            errorEl.textContent = '';
        }
    });
});
</script>
@endpush
