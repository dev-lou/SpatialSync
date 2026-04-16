@extends('layouts.app')
@section('title', 'Manage Builds')

@section('content')
<div class="container section">
    <!-- Header -->
    <div class="flex--between mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-secondary mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-primary">Admin</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Builds</span>
            </div>
            <h1 class="text-2xl font-bold text-primary">Manage Builds</h1>
        </div>
    </div>

    <!-- Builds Table -->
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead class="bg-secondary">
                <tr>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Name</th>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Owner</th>
                    <th class="text-left p-4 font-semibold text-sm text-primary">Created</th>
                    <th class="text-right p-4 font-semibold text-sm text-primary">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($builds as $build)
                    <tr class="border-t border-border hover:bg-secondary/50">
                        <td class="p-4">
                            <a href="{{ route('builds.show', $build->id) }}" class="font-medium text-primary hover:text-accent">
                                {{ $build->name ?? 'Untitled' }}
                            </a>
                        </td>
                        <td class="p-4 text-secondary">{{ $build->created_by ?? 'Unknown' }}</td>
                        <td class="p-4 text-secondary">{{ $build->created_at ? \Carbon\Carbon::parse($build->created_at)->format('M j, Y') : 'Unknown' }}</td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.builds.delete', $build->id) }}" method="POST" class="inline" x-data @submit.prevent="
                                Swal.fire({
                                    title: 'Delete Build?',
                                    text: 'This action cannot be undone.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#EF4444',
                                    confirmButtonText: 'Yes, delete it',
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-secondary">No builds found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
