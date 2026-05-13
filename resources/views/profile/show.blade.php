@extends('layouts.app')

@section('title', 'Account Settings')

@push('styles')
<style>
    .settings-page {
        padding: var(--space-12) 0;
        background: var(--bg);
        min-height: calc(100vh - var(--header-height));
    }

    .settings-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }

    @media (min-width: 1024px) {
        .settings-grid {
            grid-template-columns: 280px 1fr;
        }
    }

    /* Sidebar Navigation */
    .settings-nav {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }

    .settings-nav__item {
        display: flex;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        border-radius: var(--radius-lg);
        color: var(--text-secondary);
        text-decoration: none;
        font-weight: 500;
        transition: all var(--transition-micro);
        cursor: pointer;
        border: none;
        background: transparent;
        text-align: left;
        width: 100%;
        font-family: inherit;
        font-size: 1rem;
    }

    .settings-nav__item:hover {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    .settings-nav__item.active {
        background: var(--accent-light);
        color: var(--accent);
    }

    /* Settings Card */
    .settings-card {
        background: var(--surface);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-2xl);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        margin-bottom: var(--space-8);
    }

    .settings-card__header {
        padding: var(--space-6) var(--space-8);
        border-bottom: 1px solid var(--border-default);
    }

    .settings-card__title {
        font-size: var(--text-lg);
        font-weight: 600;
        color: var(--text-primary);
    }

    .settings-card__subtitle {
        font-size: var(--text-sm);
        color: var(--text-tertiary);
        margin-top: var(--space-1);
    }

    .settings-card__body {
        padding: var(--space-8);
    }

    .password-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-toggle {
        position: absolute;
        right: var(--space-4);
        background: transparent;
        border: none;
        color: var(--text-tertiary);
        cursor: pointer;
        padding: var(--space-1);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .password-toggle:hover {
        color: var(--text-primary);
    }

    .form-input--with-icon {
        padding-right: var(--space-12) !important;
    }

    /* Form Styles */
    .settings-form {
        max-width: 600px;
        display: flex;
        flex-direction: column;
        gap: var(--space-6);
    }

    /* Plan Badge */
    .plan-badge {
        display: inline-flex;
        align-items: center;
        gap: var(--space-2);
        padding: var(--space-2) var(--space-4);
        border-radius: var(--radius-full);
        font-weight: 700;
        font-size: var(--text-xs);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .plan-badge--free { background: var(--bg-tertiary); color: var(--text-secondary); }
    .plan-badge--pro { background: rgba(0, 102, 255, 0.1); color: var(--accent); }
    .plan-badge--enterprise { background: rgba(147, 51, 234, 0.1); color: #9333EA; }

    /* Danger Zone */
    .danger-zone {
        margin-top: var(--space-12);
        border: 1px solid rgba(239, 68, 68, 0.2);
        background: rgba(239, 68, 68, 0.02);
    }

    /* Avatar Upload */
    .avatar-upload {
        display: flex;
        align-items: center;
        gap: var(--space-6);
        margin-bottom: var(--space-6);
    }

    .avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--bg-tertiary);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
        border: 2px solid var(--border-default);
        flex-shrink: 0;
    }

    .avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-preview__initials {
        font-size: var(--text-2xl);
        font-weight: 700;
        color: var(--text-secondary);
    }

    .avatar-upload__overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
        color: white;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity var(--transition-micro);
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .avatar-preview:hover .avatar-upload__overlay {
        opacity: 1;
    }

    .avatar-upload__actions {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }
    
    /* Biometric Card */
    .biometric-card {
        display: flex;
        align-items: flex-start;
        gap: var(--space-4);
        padding: var(--space-6);
        background: var(--bg-secondary);
        border: 1px solid var(--border-default);
        border-radius: var(--radius-xl);
    }
    .biometric-icon {
        width: 48px;
        height: 48px;
        background: var(--surface);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--accent);
        box-shadow: var(--shadow-sm);
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="settings-page" 
     x-data="{ currentTab: 'profile', faceIdSetup: {{ $user->has_biometrics ? 'true' : 'false' }} }"
     @face-id-updated.window="faceIdSetup = $event.detail.status; $nextTick(() => { if(window.lucide) lucide.createIcons(); })">
    <div class="container">
        <div class="settings-grid">
            {{-- Sidebar --}}
            <aside>
                <div class="settings-nav">
                    <button type="button" 
                            class="settings-nav__item" 
                            :class="{ 'active': currentTab === 'profile' }"
                            @click="currentTab = 'profile'">
                        <i data-lucide="user" class="w-5 h-5"></i>
                        Profile Information
                    </button>
                    <button type="button" 
                            class="settings-nav__item" 
                            :class="{ 'active': currentTab === 'security' }"
                            @click="currentTab = 'security'">
                        <i data-lucide="shield" class="w-5 h-5"></i>
                        Security
                    </button>
                    <button type="button" 
                            class="settings-nav__item" 
                            :class="{ 'active': currentTab === 'subscription' }"
                            @click="currentTab = 'subscription'">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                        Subscription
                    </button>
                </div>
            </aside>

            {{-- Main Content --}}
            <div class="settings-content">
                
                {{-- 1. PROFILE TAB --}}
                <div x-show="currentTab === 'profile'" x-cloak x-transition.opacity.duration.300ms>
                    <div class="settings-card reveal">
                        <div class="settings-card__header">
                            <h2 class="settings-card__title">Profile Information</h2>
                            <p class="settings-card__subtitle">Update your account's profile information and avatar.</p>
                        </div>
                        <div class="settings-card__body">
                            
                            {{-- Avatar Upload Form --}}
                            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                @csrf
                                <div class="avatar-upload">
                                    <div class="avatar-preview" onclick="document.getElementById('avatarInput').click()">
                                        @if($user->avatar_url)
                                            <img src="{{ $user->avatar_url }}" alt="Profile Avatar">
                                        @else
                                            <div class="avatar-preview__initials">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="avatar-upload__overlay">
                                            <i data-lucide="camera" class="w-4 h-4 mb-1"></i>
                                            Change
                                        </div>
                                    </div>
                                    <div class="avatar-upload__actions">
                                        <h3 class="text-sm font-bold">Profile Picture</h3>
                                        <p class="text-xs text-tertiary">JPG, PNG or GIF. Max size 5MB.</p>
                                        <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden" onchange="document.getElementById('avatarForm').submit()">
                                    </div>
                                </div>
                            </form>

                            <hr style="border:0; border-top: 1px solid var(--border-default); margin: var(--space-6) 0;">

                            {{-- Info Update Form --}}
                            <form action="{{ route('profile.update') }}" method="POST" class="settings-form">
                                @csrf
                                @method('PUT')

                                <div class="form-group">
                                    <label class="form-label">Display Name</label>
                                    <input type="text" name="name" class="form-input" value="{{ $user->name }}" required>
                                    <p class="form-helper">This is how your name will appear to collaborators.</p>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-input" value="{{ $user->email }}" disabled>
                                    <p class="form-helper">Email cannot be changed currently. Contact support if needed.</p>
                                </div>

                                <div style="display:flex; justify-content: flex-end;">
                                    <button type="submit" class="btn btn--primary">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Danger Zone --}}
                    <div class="settings-card danger-zone reveal">
                        <div class="settings-card__header">
                            <h2 class="settings-card__title" style="color: var(--error);">Danger Zone</h2>
                            <p class="settings-card__subtitle">Permanent actions for your account.</p>
                        </div>
                        <div class="settings-card__body" style="display:flex; align-items:center; justify-content:space-between;">
                            <div>
                                <h4 class="font-bold text-sm">Delete Account</h4>
                                <p class="text-xs text-tertiary">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                            </div>
                            <button class="btn btn--danger btn--sm" onclick="confirmDelete()">
                                Delete Account
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. SECURITY TAB --}}
                <div x-show="currentTab === 'security'" x-cloak x-transition.opacity.duration.300ms>
                    <div class="settings-card reveal">
                        <div class="settings-card__header">
                            <h2 class="settings-card__title">Security Settings</h2>
                            <p class="settings-card__subtitle">Manage your password and biometric authentication methods.</p>
                        </div>
                        <div class="settings-card__body">
                            
                            <h3 class="text-base font-bold mb-4">Biometric Authentication</h3>
                            
                            <div class="biometric-card" style="align-items: center;">
                                <div class="biometric-icon">
                                    <i data-lucide="scan-face" class="w-6 h-6"></i>
                                </div>
                                <div style="flex:1;">
                                    <h4 class="font-bold text-sm">Face ID Login</h4>
                                    <p class="text-sm text-secondary mb-0">Enable Neural Face Login to access your account securely without a password.</p>
                                </div>
                                <div style="flex-shrink: 0; text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                    <template x-if="!faceIdSetup">
                                        <button type="button" class="btn btn--secondary btn--sm" onclick="window.dispatchEvent(new CustomEvent('open-scanner'))">
                                            Setup Face ID
                                        </button>
                                    </template>
                                    <template x-if="faceIdSetup">
                                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                                            <div style="display:flex; align-items:center; gap: 4px; color: var(--success); font-weight:600; font-size:0.75rem;">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i> Face ID Enabled
                                            </div>
                                             <div style="display: flex; align-items: center; gap: 8px;">
                                                <button type="button" class="btn btn--secondary btn--sm" onclick="window.dispatchEvent(new CustomEvent('open-scanner'))">
                                                    <i data-lucide="refresh-cw" class="w-3 h-3"></i> Rescan
                                                </button>
                                                <button type="button" class="btn btn--sm" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);" onclick="window.dispatchEvent(new CustomEvent('remove-biometrics'))">
                                                    <i data-lucide="trash-2" class="w-3 h-3"></i> Remove
                                                </button>
                                             </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <hr style="border:0; border-top: 1px solid var(--border-default); margin: var(--space-8) 0;">

                            <h3 class="text-base font-bold mb-4">Change Password</h3>
                            <form action="{{ route('profile.password') }}" method="POST" class="settings-form">
                                @csrf
                                @method('PUT')

                                <div class="form-group" x-data="{ show: false }">
                                    <label class="form-label">Current Password</label>
                                    <div class="password-wrapper">
                                        <input :type="show ? 'text' : 'password'" name="current_password" class="form-input form-input--with-icon" required>
                                        <button type="button" class="password-toggle" @click="show = !show" :title="show ? 'Hide Password' : 'Show Password'">
                                            <i data-lucide="eye" class="w-4 h-4" x-show="!show"></i>
                                            <i data-lucide="eye-off" class="w-4 h-4" x-show="show"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group" x-data="{ show: false }">
                                    <label class="form-label">New Password</label>
                                    <div class="password-wrapper">
                                        <input :type="show ? 'text' : 'password'" name="password" class="form-input form-input--with-icon" required minlength="8">
                                        <button type="button" class="password-toggle" @click="show = !show" :title="show ? 'Hide Password' : 'Show Password'">
                                            <i data-lucide="eye" class="w-4 h-4" x-show="!show"></i>
                                            <i data-lucide="eye-off" class="w-4 h-4" x-show="show"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-group" x-data="{ show: false }">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input :type="show ? 'text' : 'password'" name="password_confirmation" class="form-input form-input--with-icon" required minlength="8">
                                        <button type="button" class="password-toggle" @click="show = !show" :title="show ? 'Hide Password' : 'Show Password'">
                                            <i data-lucide="eye" class="w-4 h-4" x-show="!show"></i>
                                            <i data-lucide="eye-off" class="w-4 h-4" x-show="show"></i>
                                        </button>
                                    </div>
                                </div>

                                <div style="display:flex; justify-content: flex-end;">
                                    <button type="submit" class="btn btn--primary">
                                        Update Password
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                {{-- 3. SUBSCRIPTION TAB --}}
                <div x-show="currentTab === 'subscription'" x-cloak x-transition.opacity.duration.300ms>
                    <div class="settings-card reveal">
                        <div class="settings-card__header">
                            <h2 class="settings-card__title">Subscription Plan</h2>
                            <p class="settings-card__subtitle">Manage your subscription and billing details.</p>
                        </div>
                        <div class="settings-card__body">
                            
                            <div style="padding: var(--space-6); background: var(--bg-secondary); border-radius: var(--radius-xl); border: 1px solid var(--border-default); margin-bottom: var(--space-6);">
                                <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom: var(--space-6);">
                                    <div>
                                        <p class="text-sm text-tertiary font-bold uppercase tracking-wider mb-1">Current Plan</p>
                                        <div style="display:flex; align-items:center; gap: var(--space-3);">
                                            <h3 class="text-2xl font-bold capitalize">{{ $user->plan }}</h3>
                                            <div class="plan-badge plan-badge--{{ $user->plan }}">Active</div>
                                        </div>
                                    </div>
                                    @if($user->plan === 'free')
                                        <a href="{{ route('pricing') }}" class="btn btn--primary btn--glow">
                                            Upgrade Plan
                                        </a>
                                    @else
                                        <a href="{{ route('pricing') }}" class="btn btn--secondary">
                                            Change Plan
                                        </a>
                                    @endif
                                </div>

                                <div style="border-top: 1px solid var(--border-default); padding-top: var(--space-4);">
                                    <p class="text-sm font-bold mb-3">Your Plan Benefits:</p>
                                    <ul style="list-style:none; padding:0; margin:0; display:grid; gap:var(--space-2);">
                                        @if($user->plan === 'free')
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> 3 Active Projects
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Standard 3D Components
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> 500MB Storage
                                            </li>
                                        @elseif($user->plan === 'pro')
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Unlimited Projects
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Premium Materials & Textures
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> 10GB Storage
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Live Collaboration (Up to 5)
                                            </li>
                                        @else
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Everything in Pro
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Unlimited Storage
                                            </li>
                                            <li style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary);">
                                                <i data-lucide="check" class="w-4 h-4" style="color:var(--success);"></i> Custom Subdomain
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // Simulated Account Deletion
    function confirmDelete() {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action is permanent and cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete my account',
            background: 'var(--surface)',
            color: 'var(--text-primary)'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Error', 'Deletion is disabled for demo purposes.', 'error');
            }
        });
    }
</script>

<!-- Global Biometric Identity Scanner for Setup -->
<div x-data="faceEnrollment" @open-scanner.window="openScanner()" @remove-biometrics.window="removeBiometrics()">
    <template x-teleport="body">
        <div x-show="scannerOpen" 
             style="position: fixed !important; top: 0 !important; left: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 2147483647 !important; background: rgba(0, 0, 0, 0.85) !important; backdrop-filter: blur(12px) !important; -webkit-backdrop-filter: blur(12px) !important; display: flex; align-items: center; justify-content: center;"
             x-transition:enter="transition ease-out duration-400"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-cloak>
        
        <div class="card" 
             style="position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) !important; width: 95% !important; max-width: 960px !important; background: #0f172a !important; padding: 0 !important; overflow: hidden !important; border-radius: 40px !important; box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5) !important; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
            
            <div style="padding: 2.5rem 3.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1); display: flex; justify-content: space-between; align-items: center; background: #0f172a;">
                <div style="display: flex; align-items: center; gap: 24px;">
                    <div style="width: 56px; height: 56px; background: var(--accent); border-radius: 16px; display: grid; place-items: center; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);">
                        <i data-lucide="scan-face" style="width: 32px; height: 32px; color: #fff;"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.85rem; font-weight: 950; color: #fff; letter-spacing: -0.04em;">Setup Neural Face ID</h3>
                        <p style="margin: 0; font-size: 0.875rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.15em; font-weight: 700;">Enroll your biometric signature</p>
                    </div>
                </div>
                <button @click="closeScanner()" style="background: rgba(255,255,255,0.1); border: none; cursor: pointer; color: #fff; width: 56px; height: 56px; border-radius: 50%; display: grid; place-items: center; transition: background-color 0.3s;">
                    <i data-lucide="x" style="width: 28px; height: 28px;"></i>
                </button>
            </div>

            <div style="position: relative; aspect-ratio: 16/10; background: #020617; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                
                <template x-if="isLoadingModels">
                    <div style="position: absolute; inset: 0; z-index: 50; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #020617; color: #fff;">
                        <i data-lucide="loader-2" class="w-12 h-12 animate-spin text-accent mb-4" style="color: var(--accent);"></i>
                        <h4 style="font-weight: 700; letter-spacing: 0.1em;">INITIALIZING NEURAL ENGINE</h4>
                    </div>
                </template>

                <video id="enroll-video" autoplay muted playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); filter: brightness(1.15) contrast(1.1) saturate(1.1);"></video>
                <canvas id="enroll-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 15; pointer-events: none; transform: scaleX(-1);"></canvas>
                
                <!-- Neural Targeting HUD -->
                <div style="position: absolute; inset: 60px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 40px; pointer-events: none; z-index: 20;">
                    <div style="position: absolute; top: 0; left: 0; width: 80px; height: 80px; border-top: 5px solid var(--accent); border-left: 5px solid var(--accent); border-radius: 30px 0 0 0; box-shadow: -10px -10px 25px rgba(59, 130, 246, 0.1);"></div>
                    <div style="position: absolute; top: 0; right: 0; width: 80px; height: 80px; border-top: 5px solid var(--accent); border-right: 5px solid var(--accent); border-radius: 0 30px 0 0; box-shadow: 10px -10px 25px rgba(59, 130, 246, 0.1);"></div>
                    <div style="position: absolute; bottom: 0; left: 0; width: 80px; height: 80px; border-bottom: 5px solid var(--accent); border-left: 5px solid var(--accent); border-radius: 0 0 0 30px; box-shadow: -10px 10px 25px rgba(59, 130, 246, 0.1);"></div>
                    <div style="position: absolute; bottom: 0; right: 0; width: 80px; height: 80px; border-bottom: 5px solid var(--accent); border-right: 5px solid var(--accent); border-radius: 0 0 30px 0; box-shadow: 10px 10px 25px rgba(59, 130, 246, 0.1);"></div>
                </div>

                <div style="position: absolute; inset: 0; border: 10px solid transparent; transition: border-color 0.6s; pointer-events: none; z-index: 21;"
                     :style="faceDetected ? 'border-color: #22c55e;' : 'border-color: rgba(59, 130, 246, 0.2);'">
                    
                    <div style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(25px); padding: 1rem 2rem; border-radius: 20px; color: #1e293b; font-size: 1rem; font-weight: 800; display: flex; align-items: center; gap: 1rem; border: 1px solid rgba(0, 0, 0, 0.1); box-shadow: 0 15px 35px rgba(0,0,0,0.1);">
                        <div style="width: 12px; height: 12px; border-radius: 50%;" :style="faceDetected ? 'background: #22c55e; box-shadow: 0 0 20px #22c55e;' : 'background: #ef4444;'"></div>
                        <span x-text="faceDetected ? (isSaving ? 'Saving signature...' : 'Hold still. Capturing signature...') : 'Position face in frame'"></span>
                    </div>
                </div>

                <!-- Error Overlay -->
                <div x-show="errorMessage" 
                     style="position: absolute; inset: 0; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(25px); display: flex; flex-direction: column; align-items: center; justify-content: center; color: #1e293b; z-index: 200;"
                     x-transition:enter="transition ease-in-out duration-500"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-cloak>
                    <div style="text-align: center; padding-top: 3rem;">
                        <div style="width: 100px; height: 100px; background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 1.5rem; box-shadow: 0 20px 60px rgba(239, 68, 68, 0.1);">
                            <i data-lucide="triangle-alert" style="width: 48px; height: 48px; color: #ef4444;"></i>
                        </div>
                        <h4 style="font-weight: 950; font-size: 2rem; margin: 0; color: #1e293b;">SCAN FAILED</h4>
                        <p style="font-size: 1rem; color: #ef4444; margin-top: 0.5rem; font-weight: 600;" x-text="errorMessage"></p>
                        <button type="button" @click="retryScan()" style="margin-top: 2rem; background: #ef4444; color: #fff; border: none; padding: 12px 24px; border-radius: 99px; font-weight: 700; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='#ef4444'">Try Again</button>
                    </div>
                </div>

                <!-- Success Overlay -->
                <div x-show="saveSuccess" 
                     style="position: absolute; inset: 0; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(25px); display: flex; flex-direction: column; align-items: center; justify-content: center; color: #1e293b; z-index: 200;"
                     x-transition:enter="transition ease-in-out duration-500"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-cloak>
                    <div style="text-align: center; padding-top: 4rem;">
                        <div style="width: 120px; height: 120px; background: #22c55e; border-radius: 50%; display: grid; place-items: center; margin: 0 auto 1.5rem; box-shadow: 0 20px 60px rgba(34, 197, 94, 0.2);">
                            <i data-lucide="shield-check" style="width: 60px; height: 60px; color: #fff;"></i>
                        </div>
                        <h4 style="font-weight: 950; font-size: 2.5rem; margin: 0; color: #1e293b;">SECURED</h4>
                        <p style="font-size: 1rem; color: #22c55e; margin-top: 0.5rem; font-weight: 700;">Face ID Successfully Enrolled</p>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('faceEnrollment', () => ({
            scannerOpen: false,
            faceDetected: false,
            isSaving: false,
            isLoadingModels: false,
            saveSuccess: false,
            errorMessage: null,
            video: null,
            modelsLoaded: false,
            detectionActive: false,

            async openScanner() {
                if (this.isLoadingModels) return;
                
                this.isLoadingModels = true;
                this.scannerOpen = true;
                this.saveSuccess = false;
                this.errorMessage = null;
                
                try {
                    await this.loadModels();
                    this.isLoadingModels = false;
                    this.startCamera();
                } catch (err) {
                    console.error("Face API Initialization Error:", err);
                    this.isLoadingModels = false;
                    this.errorMessage = 'Failed to load neural models. Check network.';
                }
            },

            init() {
                this.$watch('saveSuccess', value => {
                    if (value) {
                        this.$nextTick(() => { if(window.lucide) lucide.createIcons(); });
                    }
                });
                this.$watch('errorMessage', value => {
                    if (value) {
                        this.$nextTick(() => { if(window.lucide) lucide.createIcons(); });
                    }
                });
            },

            retryScan() {
                this.errorMessage = null;
                if (!this.video || !this.video.srcObject) {
                    this.startCamera();
                } else {
                    this.detectionActive = true;
                    this.startDetection();
                }
            },

            async loadModels() {
                if (this.modelsLoaded) return;
                const MODEL_URL = 'https://justadudewhohacks.github.io/face-api.js/models';
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL)
                ]);
                this.modelsLoaded = true;
            },

            async startCamera() {
                this.$nextTick(async () => {
                    this.video = document.getElementById('enroll-video');
                    if (!this.video) {
                        setTimeout(() => this.startCamera(), 100);
                        return;
                    }
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                        this.video.srcObject = stream;
                        await this.video.play();
                        this.detectionActive = true;
                        this.startDetection();
                    } catch (err) {
                        this.errorMessage = 'Face recognition requires camera access.';
                    }
                });
            },

            startDetection() {
                const canvas = document.getElementById('enroll-canvas');
                let missCount = 0;
                const MISS_GRACE = 12;
                let lastDescriptor = null;
                let stableFrames = 0;
                const REQUIRED_STABLE_FRAMES = 10; // Slightly reduced for faster enrollment

                const detectorOptions = new faceapi.TinyFaceDetectorOptions({
                    inputSize: 416, // Increased resolution for better detection in low light
                    scoreThreshold: 0.3 // More forgiving threshold
                });

                const detectFrame = async () => {
                    if (!this.scannerOpen || !this.detectionActive || this.isSaving || this.saveSuccess) {
                        if (canvas) canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
                        return;
                    }
                    
                    if (!this.video || this.video.readyState < 2) {
                        setTimeout(detectFrame, 100);
                        return;
                    }

                    const displaySize = { width: this.video.clientWidth, height: this.video.clientHeight };
                    if (displaySize.width === 0) {
                        setTimeout(detectFrame, 100);
                        return;
                    }
                    
                    faceapi.matchDimensions(canvas, displaySize);

                    try {
                        const detection = await faceapi.detectSingleFace(this.video, detectorOptions)
                                                       .withFaceLandmarks()
                                                       .withFaceDescriptor();

                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        if (detection) {
                            missCount = 0;
                            this.faceDetected = true;
                            lastDescriptor = detection.descriptor;
                            stableFrames++;

                            const resized = faceapi.resizeResults(detection, displaySize);
                            
                            // Only draw the neural landmarks mesh, removing the generic bounding box 
                            // This fixes the backward score text and looks more premium.
                            faceapi.draw.drawFaceLandmarks(canvas, resized);

                            if (stableFrames >= REQUIRED_STABLE_FRAMES && !this.isSaving) {
                                this.saveBiometrics(lastDescriptor);
                            }
                        } else {
                            missCount++;
                            if (missCount >= MISS_GRACE) {
                                this.faceDetected = false;
                                stableFrames = 0;
                            }
                        }
                    } catch (e) {
                        console.error("Detection error:", e);
                    }

                    setTimeout(detectFrame, 100);
                };

                detectFrame();
            },

            async saveBiometrics(descriptor) {
                this.isSaving = true;
                
                try {
                    const response = await fetch("{{ route('profile.biometrics.save') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ descriptor: Array.from(descriptor) })
                    });

                    const result = await response.json();

                    if (response.ok) {
                        this.saveSuccess = true;
                        
                        // Dispatch event to update the main settings UI
                        window.dispatchEvent(new CustomEvent('face-id-updated', { detail: { status: true } }));

                        setTimeout(() => {
                            this.closeScanner();
                        }, 2500);
                    } else {
                        this.errorMessage = result.message || 'Failed to save biometric data';
                        this.isSaving = false;
                        this.faceDetected = false;
                    }
                } catch (err) {
                    this.isSaving = false;
                    this.errorMessage = 'Network error occurred.';
                }
            },

            closeScanner() {
                this.detectionActive = false;
                if (this.video && this.video.srcObject) {
                    this.video.srcObject.getTracks().forEach(track => track.stop());
                }
                this.scannerOpen = false;
                this.isSaving = false;
                this.saveSuccess = false;
            },

            async removeBiometrics() {
                const result = await Swal.fire({
                    title: 'Remove Face ID?',
                    text: 'This will delete your biometric fingerprint. You will need to use your password to log in.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, remove it',
                    background: '#fff',
                    color: '#1e293b'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await fetch("{{ route('profile.biometrics.delete') }}", {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            Swal.fire({
                                title: 'Removed',
                                text: 'Biometric data has been deleted.',
                                icon: 'success',
                                background: '#fff',
                                color: '#1e293b'
                            });
                            // Dispatch event to update the main settings UI
                            window.dispatchEvent(new CustomEvent('face-id-updated', { detail: { status: false } }));
                        } else {
                            throw new Error('Failed to remove biometric data');
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'Error',
                            text: err.message,
                            icon: 'error',
                            background: '#fff',
                            color: '#1e293b'
                        });
                    }
                }
            }
        }));
    });
</script>
@endsection

