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
    
</style>
@endpush

@section('content')
<div class="settings-page" 
     x-data="{ currentTab: 'profile' }"
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
                                    <div class="avatar-preview" x-on:click="document.getElementById('avatarInput').click()">
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
                                        <input type="file" name="avatar" id="avatarInput" accept="image/*" class="hidden" x-on:change="document.getElementById('avatarForm').submit()">
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
                            <button class="btn btn--danger btn--sm" x-on:click="confirmDelete()">
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
                            <p class="settings-card__subtitle">Manage your password and account access.</p>
                        </div>
                        <div class="settings-card__body">
                            
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
@endsection

