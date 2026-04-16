@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="container section">
    <!-- Header -->
    <div class="flex--between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-primary">Admin Dashboard</h1>
            <p class="text-secondary">Manage your ConstructHub platform</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn--ghost">
            <i data-lucide="external-link" class="w-4 h-4"></i>
            View Site
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid--4 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-accent-light flex items-center justify-center text-accent">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $stats['users'] }}</div>
                    <div class="text-sm text-secondary">Total Users</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-success-light flex items-center justify-center text-success">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $stats['builds'] }}</div>
                    <div class="text-sm text-secondary">Builds</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-warning-light flex items-center justify-center text-warning">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $stats['activeToday'] }}</div>
                    <div class="text-sm text-secondary">Active Today</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-info-light flex items-center justify-center text-info">
                    <i data-lucide="message-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-3xl font-bold text-primary">{{ $stats['messages'] }}</div>
                    <div class="text-sm text-secondary">Messages</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid--2 gap-6">
        <div class="card">
            <h2 class="text-lg font-semibold text-primary mb-4">Quick Actions</h2>
            <div class="flex flex-col gap-3">
                <a href="{{ route('admin.users') }}" class="btn btn--secondary w-full justify-start">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Manage Users
                </a>
                <a href="{{ route('admin.builds') }}" class="btn btn--secondary w-full justify-start">
                    <i data-lucide="layout" class="w-4 h-4"></i>
                    Manage Builds
                </a>
            </div>
        </div>

        <div class="card">
            <h2 class="text-lg font-semibold text-primary mb-4">Recent Activity</h2>
            @forelse($recentActivity->take(5) as $activity)
                <div class="flex items-center gap-3 py-3 border-b border-border last:border-0">
                    <div class="avatar avatar--sm">{{ strtoupper(substr($activity->user_name ?? 'U', 0, 1)) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-primary truncate">
                            <span class="font-medium">{{ $activity->user_name ?? 'User' }}</span> sent a message
                        </p>
                        <p class="text-xs text-tertiary">{{ $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->diffForHumans() : 'recently' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-secondary text-sm">No recent activity</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
