@extends('layouts.app')
@section('title', 'Manage Users')

@section('content')
<div class="container section">
    <!-- Header -->
    <div class="flex--between mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-secondary mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-primary">Admin</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Users</span>
            </div>
            <h1 class="text-2xl font-bold text-primary">Manage Users</h1>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary">
                <tr>
                    <th class="text-left p-4 font-semibold text-sm text-primary">User</th>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Email</th>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Role</th>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Joined</th>
                    <th class="text-right p-4 font-semibold text-sm text-primary">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t border-border hover:bg-secondary/50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar--sm">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
                                <span class="font-medium text-primary">{{ $user->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-secondary">{{ $user->email ?? '' }}</td>
                        <td class="p-4">
                            @if($user->is_admin ?? false)
                                <span class="badge badge--error">Admin</span>
                            @else
                                <span class="badge badge--default">User</span>
                            @endif
                        </td>
                        <td class="p-4 text-secondary">{{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('M j, Y') : 'Unknown' }}</td>
                        <td class="p-4 text-right">
                            @if(!($user->is_admin ?? false))
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="inline" x-data @submit.prevent="
                                    Swal.fire({
                                        title: 'Delete User?',
                                        text: 'This will also delete all their builds and data.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#EF4444',
                                        confirmButtonText: 'Yes, delete user',
                                        cancelButtonText: 'Cancel'
                                    }).then((result) => {
                                        if (result.isConfirmed) $el.submit();
                                    })
                                ">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--ghost btn--sm text-error">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-secondary">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
