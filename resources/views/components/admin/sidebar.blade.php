@php
    $route = request()->route()->getName();
    $groups = [
        'Overview' => [
            ['r' => 'admin.dashboard', 'icon' => 'layout-dashboard', 'label' => 'Mission Control'],
        ],
        'Management' => [
            ['r' => 'admin.users',    'icon' => 'users',         'label' => 'Users & Tiers'],
            ['r' => 'admin.builds',   'icon' => 'layers',        'label' => 'All Builds'],
            ['r' => 'admin.presets',  'icon' => 'package',       'label' => 'Asset Library'],
        ],
        'Security' => [
            ['r' => 'admin.security', 'icon' => 'shield-check',  'label' => 'Biometric Auth'],
        ],
    ];
@endphp

<aside class="os-sidebar">

    {{-- Brand --}}
    <div class="os-brand">
        <div class="os-brand-mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                <polyline points="2 17 12 22 22 17"/>
                <polyline points="2 12 12 17 22 12"/>
            </svg>
        </div>
        <div>
            <div class="os-brand-name">SpatialSync</div>
            <span class="os-brand-tag">Admin OS</span>
        </div>
    </div>

    {{-- Navigation --}}
    <div class="os-nav">
        @foreach($groups as $group => $items)
            <div style="padding: 14px 6px 4px; font-size: 10px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: .09em;">{{ $group }}</div>
            @foreach($items as $item)
            <a href="{{ route($item['r']) }}"
               class="os-nav-link {{ $route === $item['r'] ? 'active' : '' }}">
                <i data-lucide="{{ $item['icon'] }}" class="nav-icon"></i>
                {{ $item['label'] }}
            </a>
            @endforeach
        @endforeach
    </div>

    {{-- Footer --}}
    <div class="os-sidebar-footer">
        <a href="{{ route('dashboard') }}" class="os-nav-link">
            <i data-lucide="arrow-left" class="nav-icon"></i>
            User View
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="os-nav-link danger" style="width:100%;">
                <i data-lucide="log-out" class="nav-icon"></i>
                Sign Out
            </button>
        </form>
    </div>
</aside>
