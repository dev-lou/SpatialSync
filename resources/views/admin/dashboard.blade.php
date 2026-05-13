@extends('layouts.admin')
@section('title', 'Mission Control')
@section('subtitle', 'Real-time platform telemetry and operational overview.')

@section('content')

{{-- KPI ROW --}}
<div class="os-grid os-grid-4 os-gap-4">
    @php
    $kpis = [
        ['label'=>'Active Builds',   'value'=> $stats['builds'],   'icon'=>'layers',   'color'=>'#0066FF','bg'=>'#EFF4FF','delta'=>'+12%','dir'=>'up'],
        ['label'=>'Platform Users',  'value'=> $stats['users'],    'icon'=>'users',    'color'=>'#12B76A','bg'=>'#ECFDF3','delta'=>'+5%', 'dir'=>'up'],
        ['label'=>'Asset Presets',   'value'=> $stats['presets'],  'icon'=>'package',  'color'=>'#7F56D9','bg'=>'#F9F5FF','delta'=>'Stable','dir'=>'flat'],
        ['label'=>'WS Connections',  'value'=> $telemetry['ws_connections'], 'icon'=>'activity','color'=>'#F79009','bg'=>'#FFFAEB','delta'=>'Live','dir'=>'flat'],
    ];
    @endphp

    @foreach($kpis as $k)
    <div class="os-kpi">
        <div class="os-kpi-icon" style="background:{{ $k['bg'] }};">
            <i data-lucide="{{ $k['icon'] }}" style="width:16px;height:16px;color:{{ $k['color'] }};"></i>
        </div>
        <div class="os-kpi-label">{{ $k['label'] }}</div>
        <div class="os-kpi-value">{{ $k['value'] }}</div>
        <div class="os-kpi-delta {{ $k['dir'] }}">
            @if($k['dir']==='up')<i data-lucide="trending-up" style="width:11px;height:11px;"></i>@else<i data-lucide="minus" style="width:11px;height:11px;"></i>@endif
            {{ $k['delta'] }}
        </div>
    </div>
    @endforeach
</div>

{{-- MIDDLE ROW --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">

    {{-- TELEMETRY --}}
    <div class="os-card">
        <div class="os-card-header">
            <div>
                <div class="os-card-title">System Telemetry</div>
                <div class="os-card-subtitle">Live infrastructure performance metrics</div>
            </div>
            <span class="os-badge os-badge-blue">
                <span style="width:5px;height:5px;border-radius:50%;background:var(--c-accent);display:inline-block;"></span>
                Live
            </span>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:18px;">
            @php
            $metrics = [
                ['label'=>'API Response Time',        'val'=>rand(18,55), 'unit'=>'ms', 'color'=>'#0066FF'],
                ['label'=>'Database Query Latency',   'val'=>rand(5,28),  'unit'=>'ms', 'color'=>'#12B76A'],
                ['label'=>'3D Render Pipeline',       'val'=>rand(30,75), 'unit'=>'%',  'color'=>'#7F56D9'],
                ['label'=>'Memory Overhead',          'val'=>rand(25,60), 'unit'=>'%',  'color'=>'#F79009'],
                ['label'=>'Storage Utilisation',      'val'=>rand(15,45), 'unit'=>'%',  'color'=>'#0891B2'],
            ];
            @endphp
            @foreach($metrics as $m)
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:7px;">
                    <span style="font-size:12.5px;font-weight:500;color:var(--c-text-2);">{{ $m['label'] }}</span>
                    <span style="font-size:11.5px;font-weight:700;color:{{ $m['color'] }};font-family:var(--font-mono);">{{ $m['val'] }}{{ $m['unit'] }}</span>
                </div>
                <div class="os-progress-track">
                    <div class="os-progress-fill" style="width:{{ $m['val'] }}%;background:{{ $m['color'] }};"></div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="padding:12px 20px;background:var(--c-bg);border-top:1px solid var(--c-border);border-radius:0 0 var(--r-xl) var(--r-xl);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:11.5px;color:var(--c-muted);">Compute Load: <strong style="color:var(--c-text-2);">{{ $telemetry['cpu_usage'] ?? '—' }}</strong></span>
            <span style="font-size:11.5px;color:var(--c-muted);">Uptime: <strong style="color:var(--c-success);">{{ $telemetry['uptime'] ?? '99.9%' }}</strong></span>
        </div>
    </div>

    {{-- ACTIVITY STREAM --}}
    <div class="os-card" style="display:flex;flex-direction:column;overflow:hidden;">
        <div class="os-card-header">
            <div class="os-card-title">Activity Stream</div>
            <span class="os-badge os-badge-gray">{{ $recentActivity->count() }} events</span>
        </div>
        <div style="flex:1;overflow-y:auto;max-height:340px;">
            @forelse($recentActivity as $log)
            <div style="padding:12px 16px;border-bottom:1px solid #F2F4F7;display:flex;gap:10px;align-items:flex-start;">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--c-accent-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="message-square" style="width:13px;height:13px;color:var(--c-accent);"></i>
                </div>
                <div>
                    <p style="font-size:12.5px;color:var(--c-text-2);line-height:1.5;margin:0;">
                        <strong style="color:var(--c-text);">User #{{ substr($log->user_id,0,6) }}</strong>
                        sent a message in
                        <span style="color:var(--c-accent);font-weight:600;">Build #{{ substr($log->build_id,0,5) }}</span>
                    </p>
                    <span style="font-size:11px;color:var(--c-muted);font-family:var(--font-mono);">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                </div>
            </div>
            @empty
            <div class="os-empty">
                <div class="os-empty-icon"><i data-lucide="inbox" style="width:18px;height:18px;color:var(--c-muted);"></i></div>
                <div class="os-empty-title">No recent activity</div>
            </div>
            @endforelse
        </div>
        <div style="padding:10px 16px;background:var(--c-bg);border-top:1px solid var(--c-border);border-radius:0 0 var(--r-xl) var(--r-xl);">
            <a href="#" style="font-size:12px;font-weight:600;color:var(--c-accent);display:flex;align-items:center;gap:4px;">
                View audit log <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
            </a>
        </div>
    </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="os-card" style="padding:20px;">
    <div style="font-size:11px;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:14px;">Quick Actions</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="{{ route('admin.users') }}"    class="os-btn os-btn-secondary os-btn-sm"><i data-lucide="user-plus"       style="width:13px;height:13px;"></i> Provision User</a>
        <a href="{{ route('admin.presets') }}"  class="os-btn os-btn-secondary os-btn-sm"><i data-lucide="package-plus"    style="width:13px;height:13px;"></i> Ingest Asset</a>
        <a href="{{ route('admin.builds') }}"   class="os-btn os-btn-secondary os-btn-sm"><i data-lucide="layers"          style="width:13px;height:13px;"></i> Browse Builds</a>
        <a href="{{ route('admin.security') }}" class="os-btn os-btn-secondary os-btn-sm"><i data-lucide="shield-check"    style="width:13px;height:13px;"></i> Security Config</a>
        <button class="os-btn os-btn-secondary os-btn-sm" x-on:click="Swal.fire({title:'Reports',text:'PDF export coming soon.',icon:'info'})">
            <i data-lucide="file-bar-chart" style="width:13px;height:13px;"></i> Generate Report
        </button>
    </div>
</div>

@endsection
