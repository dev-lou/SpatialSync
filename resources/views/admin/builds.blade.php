@extends('layouts.admin')
@section('title', 'All Builds')
@section('subtitle', 'Global repository of all user-generated architectural builds.')

@section('actions')
<button class="os-btn os-btn-secondary os-btn-sm"
    x-on:click="Swal.fire('Export','PDF report generation coming soon.','info')">
    <i data-lucide="file-bar-chart" style="width:13px;height:13px;"></i>
    Export Report
</button>
@endsection

@section('content')

<div class="os-card" style="overflow:hidden;">

    {{-- TOOLBAR --}}
    <div class="os-toolbar">
        <div class="os-toolbar-left">
            <div class="os-input-wrap">
                <i data-lucide="search" class="os-input-icon" style="width:13px;height:13px;"></i>
                <input type="text" class="os-input" id="b-search"
                    placeholder="Search builds…"
                    style="width:240px;"
                    x-on:input="filterBuilds($el.value)">
            </div>
            <select class="os-input" id="b-sort" style="width:160px;" x-on:change="sortBuilds($el.value)">
                <option value="">All Projects</option>
                <option value="name">Sort by Name</option>
                <option value="date">Sort by Date</option>
                <option value="floor">Sort by Floors</option>
            </select>
        </div>
        <div class="os-toolbar-right">
            <span style="font-size:11.5px;color:var(--c-muted);font-weight:600;" id="b-count">{{ count($builds) }} builds</span>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="os-table-wrap">
        <table class="os-table" id="builds-table">
            <thead>
                <tr>
                    <th>Build</th>
                    <th>Architect</th>
                    <th>Floors</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($builds as $build)
                <tr data-name="{{ strtolower($build->name ?? '') }}"
                    data-date="{{ $build->created_at ?? '' }}"
                    data-floor="{{ $build->current_floor ?? 0 }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:var(--r-md);background:var(--c-accent-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-lucide="layers" style="width:15px;height:15px;color:var(--c-accent);"></i>
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:var(--c-text);">{{ $build->name }}</div>
                                <div style="font-size:11px;color:var(--c-muted);font-family:var(--font-mono);">{{ substr($build->id, 0, 22) }}…</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:7px;">
                            <div class="os-avatar" style="width:24px;height:24px;background:#64748B;font-size:9px;font-weight:700;">A</div>
                            <span style="font-size:12px;color:var(--c-text-2);font-family:var(--font-mono);">{{ substr($build->created_by ?? '', 0, 8) }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;min-width:90px;">
                            <div class="os-progress-track" style="width:50px;">
                                <div class="os-progress-fill" style="width:{{ min((($build->current_floor ?? 1) / 10) * 100, 100) }}%;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;color:var(--c-text-2);">{{ $build->current_floor ?? 1 }}F</span>
                        </div>
                    </td>
                    <td style="font-size:12.5px;color:var(--c-text-2);">
                        {{ \Carbon\Carbon::parse($build->created_at)->format('M d, Y') }}
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:4px;">
                            <a href="{{ route('builds.show', $build->id) }}" target="_blank"
                               class="os-btn os-btn-secondary os-btn-icon os-btn-sm" title="Open in editor">
                                <i data-lucide="external-link" style="width:13px;height:13px;"></i>
                            </a>
                            <form action="{{ route('admin.builds.delete', $build->id) }}" method="POST"
                                  x-data x-on:submit.prevent="
                                    Swal.fire({
                                        title: 'Delete Build?',
                                        text: 'Permanently delete {{ addslashes($build->name) }}?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes, delete'
                                    }).then(r => r.isConfirmed && $el.submit())
                                  ">
                                @csrf @method('DELETE')
                                <button type="submit" class="os-btn os-btn-danger os-btn-icon os-btn-sm" title="Delete build">
                                    <i data-lucide="trash-2" style="width:13px;height:13px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <div class="os-empty">
                            <div class="os-empty-icon"><i data-lucide="layers" style="width:18px;height:18px;color:var(--c-muted);"></i></div>
                            <div class="os-empty-title">No builds yet</div>
                            <div class="os-empty-desc">Builds created by users will appear here.</div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="os-table-footer">
        <span id="b-footer">Showing {{ count($builds) }} builds</span>
    </div>
</div>

@push('scripts')
<script>
function filterBuilds(q) {
    const rows = document.querySelectorAll('#builds-table tbody tr[data-name]');
    const term = q.toLowerCase().trim();
    let n = 0;
    rows.forEach(r => {
        const hit = !term || (r.dataset.name || '').includes(term);
        r.style.display = hit ? '' : 'none';
        if (hit) n++;
    });
    document.getElementById('b-count').textContent  = `${n} builds`;
    document.getElementById('b-footer').textContent = `Showing ${n} builds`;
}

function sortBuilds(type) {
    if (!type) return;
    const tbody = document.querySelector('#builds-table tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-name]'));
    rows.sort((a, b) => {
        if (type === 'name')  return (a.dataset.name  || '').localeCompare(b.dataset.name  || '');
        if (type === 'date')  return new Date(b.dataset.date || 0) - new Date(a.dataset.date || 0);
        if (type === 'floor') return parseInt(b.dataset.floor || 0) - parseInt(a.dataset.floor || 0);
        return 0;
    });
    rows.forEach(r => tbody.appendChild(r));
}

function confirmDelete(form, msg) {
    Swal.fire({
        title: 'Delete Build',
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
    }).then(r => { if (r.isConfirmed) form.submit(); });
}
</script>
@endpush
@endsection
