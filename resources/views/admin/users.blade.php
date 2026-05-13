@extends('layouts.admin')
@section('title', 'Users & Tiers')
@section('subtitle', 'Manage registered members and their access tiers.')

@section('actions')
<button class="os-btn os-btn-primary os-btn-sm"
    @click="Swal.fire('Provision','User provisioning panel coming soon.','info')">
    <i data-lucide="user-plus" style="width:13px;height:13px;"></i>
    Provision Member
</button>
@endsection

@section('content')

<div class="os-card" style="overflow:hidden;">

    {{-- TOOLBAR --}}
    <div class="os-toolbar">
        <div class="os-toolbar-left">
            <div class="os-input-wrap">
                <i data-lucide="search" class="os-input-icon" style="width:13px;height:13px;"></i>
                <input type="text" class="os-input" id="u-search"
                    placeholder="Search by name or email…"
                    style="width:260px;"
                    x-on:input="filterUsers($el.value)">
            </div>
        </div>
        <div class="os-toolbar-right">
            <button class="os-btn os-btn-secondary os-btn-sm">
                <i data-lucide="download" style="width:12px;height:12px;"></i>
                Export CSV
            </button>
            <span style="font-size:11.5px;color:var(--c-muted);font-weight:600;" id="u-count">{{ count($users) }} members</span>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="os-table-wrap">
        <table class="os-table" id="users-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Last Active</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $colors = ['#0066FF','#7F56D9','#0891B2','#12B76A','#F79009'];
                    $color  = $colors[abs(crc32($user->name ?? '')) % 5];
                @endphp
                <tr data-name="{{ strtolower($user->name ?? '') }}" data-email="{{ strtolower($user->email ?? '') }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="os-avatar" style="width:32px;height:32px;background:{{ $color }};font-size:12px;font-weight:700;">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:var(--c-text);">{{ $user->name }}</div>
                                <div style="font-size:11.5px;color:var(--c-muted);font-family:var(--font-mono);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if(!empty($user->is_admin) && $user->is_admin)
                            <span class="os-badge os-badge-purple">
                                <i data-lucide="shield" style="width:9px;height:9px;"></i>
                                System Admin
                            </span>
                        @else
                            <span class="os-badge os-badge-gray">Standard User</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:var(--c-text-2);">
                        {{ isset($user->updated_at) ? \Carbon\Carbon::parse($user->updated_at)->diffForHumans() : '—' }}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="os-dot os-dot-green"></div>
                            <span style="font-size:12px;font-weight:600;color:var(--c-success);">Active</span>
                        </div>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                            <button class="os-btn os-btn-secondary os-btn-icon os-btn-sm"
                                    title="Edit permissions"
                                    x-on:click="Swal.fire('Permissions','Role management coming soon.','info')">
                                <i data-lucide="shield-check" style="width:13px;height:13px;"></i>
                            </button>
                            <form action="{{ route('admin.users.delete', $user->id) }}" method="POST"
                                  x-data x-on:submit.prevent="
                                    Swal.fire({
                                        title: 'Remove Access?',
                                        text: 'Remove {{ addslashes($user->name) }}\'s access?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, remove'
                                    }).then(r => r.isConfirmed && $el.submit())
                                  ">
                                @csrf @method('DELETE')
                                <button type="submit" class="os-btn os-btn-danger os-btn-icon os-btn-sm" title="Revoke access">
                                    <i data-lucide="user-minus" style="width:13px;height:13px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="os-empty">
                            <div class="os-empty-icon"><i data-lucide="users" style="width:18px;height:18px;color:var(--c-muted);"></i></div>
                            <div class="os-empty-title">No members found</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="os-table-footer">
        <span id="u-footer">Showing {{ count($users) }} of {{ count($users) }} members</span>
        <div style="display:flex;gap:4px;">
            <button class="os-btn os-btn-secondary os-btn-sm" disabled>← Prev</button>
            <button class="os-btn os-btn-secondary os-btn-sm" disabled>Next →</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterUsers(q) {
    const rows = document.querySelectorAll('#users-table tbody tr[data-name]');
    const term = q.toLowerCase().trim();
    let n = 0;
    rows.forEach(r => {
        const hit = !term || r.dataset.name.includes(term) || r.dataset.email.includes(term);
        r.style.display = hit ? '' : 'none';
        if (hit) n++;
    });
    document.getElementById('u-count').textContent  = `${n} members`;
    document.getElementById('u-footer').textContent = `Showing ${n} of {{ count($users) }} members`;
}

function confirmDelete(form, msg) {
    Swal.fire({
        title: 'Are you sure?',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel',
    }).then(r => { if (r.isConfirmed) form.submit(); });
}
</script>
@endpush
@endsection
