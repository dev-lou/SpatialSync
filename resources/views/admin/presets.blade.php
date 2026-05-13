@extends('layouts.admin')
@section('title', 'Asset Library')
@section('subtitle', 'Core 3D architectural primitives available to the build editor.')

@section('actions')
<button class="os-btn os-btn-primary os-btn-sm"
    x-on:click="Swal.fire('Ingest Asset','Asset ingestion panel is in development.','info')">
    <i data-lucide="plus" style="width:13px;height:13px;"></i>
    Ingest Asset
</button>
@endsection

@section('content')

{{-- FILTER TABS --}}
@php
    $categories = collect($presets)->pluck('type')->unique()->filter()->values()->prepend('all');
@endphp

<div class="os-tab-group" id="filter-tabs">
    @foreach($categories as $cat)
    <button class="os-tab {{ $loop->first ? 'active' : '' }}"
            data-cat="{{ $cat }}"
            x-on:click="filterAssets($el,'{{ $cat }}')">
        {{ $cat === 'all' ? 'All Assets' : ucfirst($cat) }}
    </button>
    @endforeach
</div>

{{-- STATS BAR --}}
<div style="display:flex;align-items:center;gap:6px;">
    <span style="font-size:12px;color:var(--c-muted);font-weight:600;" id="asset-count">{{ count($presets) }} assets</span>
</div>

{{-- ASSET GRID --}}
<div class="os-grid os-grid-auto os-gap-4" id="asset-grid">
    @forelse($presets as $preset)
    @php
        $palettes = [
            'wall'      => ['icon'=>'minus-square',  'c'=>'#0066FF','bg'=>'#EFF4FF'],
            'floor'     => ['icon'=>'layout',        'c'=>'#12B76A','bg'=>'#ECFDF3'],
            'roof'      => ['icon'=>'triangle',      'c'=>'#7F56D9','bg'=>'#F9F5FF'],
            'door'      => ['icon'=>'door-open',     'c'=>'#F79009','bg'=>'#FFFAEB'],
            'window'    => ['icon'=>'square',        'c'=>'#0891B2','bg'=>'#F0F9FF'],
            'stairs'    => ['icon'=>'arrow-up',      'c'=>'#DC6803','bg'=>'#FFF6ED'],
            'column'    => ['icon'=>'columns',       'c'=>'#5925DC','bg'=>'#F4F3FF'],
            'furniture' => ['icon'=>'sofa',          'c'=>'#C01048','bg'=>'#FFF1F3'],
        ];
        $p = $palettes[$preset->type] ?? ['icon'=>'box','c'=>'#475467','bg'=>'#F8F9FA'];
    @endphp
    <div class="os-card" style="overflow:hidden;display:flex;flex-direction:column;" data-cat="{{ $preset->type }}">

        {{-- Thumbnail --}}
        <div style="height:96px;background:{{ $p['bg'] }};display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;">
            {{-- Subtle dot grid --}}
            <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:.25;" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="g-{{ $preset->id ?? $loop->index }}" width="18" height="18" patternUnits="userSpaceOnUse">
                        <circle cx="9" cy="9" r="1" fill="{{ $p['c'] }}"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#g-{{ $preset->id ?? $loop->index }})"/>
            </svg>
            <div style="width:44px;height:44px;border-radius:var(--r-lg);background:white;display:flex;align-items:center;justify-content:center;box-shadow:var(--s-md);position:relative;z-index:1;">
                <i data-lucide="{{ $p['icon'] }}" style="width:20px;height:20px;color:{{ $p['c'] }};"></i>
            </div>
            {{-- Type badge top-left --}}
            <span class="os-badge" style="position:absolute;top:8px;left:8px;z-index:2;font-size:10px;font-weight:700;background:white;color:{{ $p['c'] }};border-color:{{ $p['bg'] }};">
                {{ $preset->type ?? 'unknown' }}
            </span>
            {{-- Toggle top-right --}}
            <label class="os-toggle-wrap" style="position:absolute;top:8px;right:8px;z-index:2;">
                <input type="checkbox" class="os-toggle-input" {{ !empty($preset->is_active) ? 'checked' : '' }}
                       x-on:change="toggleAsset('{{ $preset->id ?? '' }}', $el.checked)">
                <div class="os-toggle-track"></div>
            </label>
        </div>

        {{-- Body --}}
        <div style="padding:14px 16px;flex:1;display:flex;flex-direction:column;gap:10px;">
            <div>
                <div style="font-size:13px;font-weight:700;color:var(--c-text);letter-spacing:-.01em;">{{ $preset->name }}</div>
                <div style="font-size:11px;color:var(--c-muted);font-family:var(--font-mono);margin-top:1px;">{{ $preset->variant ?? 'standard' }}</div>
            </div>

            {{-- Dimensions --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                @foreach(['W'=>$preset->default_width ?? 0,'H'=>$preset->default_height ?? 0,'D'=>$preset->default_depth ?? 0] as $lbl=>$val)
                <div style="background:var(--c-bg);border:1px solid var(--c-border);border-radius:var(--r-md);padding:8px 6px;text-align:center;">
                    <div style="font-size:9px;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">{{ $lbl }}</div>
                    <div style="font-size:12.5px;font-weight:700;color:var(--c-text-2);margin-top:2px;font-family:var(--font-mono);">{{ $val }}<span style="font-size:9px;color:var(--c-muted);font-weight:400;">m</span></div>
                </div>
                @endforeach
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:6px;margin-top:auto;">
                <button class="os-btn os-btn-secondary os-btn-sm" style="flex:1;"
                        x-on:click="editAsset('{{ $preset->id ?? '' }}','{{ addslashes($preset->name) }}')">
                    <i data-lucide="edit-3" style="width:11px;height:11px;"></i>
                    Edit Specs
                </button>
                <button class="os-btn os-btn-secondary os-btn-icon os-btn-sm" title="Preview">
                    <i data-lucide="eye" style="width:12px;height:12px;"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="os-card" style="grid-column:1/-1;">
        <div class="os-empty">
            <div class="os-empty-icon"><i data-lucide="package" style="width:18px;height:18px;color:var(--c-muted);"></i></div>
            <div class="os-empty-title">No assets ingested yet</div>
            <div class="os-empty-desc">Click "Ingest Asset" to add 3D primitives to the library.</div>
        </div>
    </div>
    @endforelse
</div>

@push('scripts')
<script>
function filterAssets(btn, cat) {
    // Update tabs
    document.querySelectorAll('#filter-tabs .os-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    // Filter cards
    let n = 0;
    document.querySelectorAll('#asset-grid .os-card[data-cat]').forEach(card => {
        const show = cat === 'all' || card.dataset.cat === cat;
        card.style.display = show ? '' : 'none';
        if (show) n++;
    });
    document.getElementById('asset-count').textContent = `${n} assets`;
}

function toggleAsset(id, active) {
    // TODO: persist via AJAX
    console.log('Toggle asset', id, active);
}

function editAsset(id, name) {
    Swal.fire({
        title: 'Edit Asset',
        html: `<p style="font-size:13px;color:var(--c-text-2);margin-bottom:12px;">Editing: <strong>${name}</strong></p>
               <p style="font-size:12px;color:var(--c-muted);">Full spec editor coming in next release.</p>`,
        icon: 'info',
        confirmButtonText: 'Got it',
    });
}
</script>
@endpush
@endsection
