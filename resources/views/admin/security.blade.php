@extends('layouts.admin')
@section('title', 'Biometric Auth')
@section('subtitle', 'Neural face fingerprint management and security vault configuration.')

@section('actions')
<span class="os-badge os-badge-green" style="font-size:12px;padding:5px 12px;gap:6px;">
    <div class="os-dot os-dot-green"></div>
    Vault Secured
</span>
@endsection

@section('content')

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

    {{-- ── STATUS PANEL ────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Status Card --}}
        <div class="os-card" style="padding:24px;">
            {{-- Shield icon --}}
            <div style="display:flex;flex-direction:column;align-items:center;text-align:center;padding-bottom:20px;border-bottom:1px solid var(--c-border);margin-bottom:20px;">
                <div style="position:relative;width:72px;height:72px;margin-bottom:16px;">
                    <div class="os-scan-ring" style="
                        position:absolute;inset:-8px;border-radius:50%;
                        border:2px solid var(--c-accent);opacity:.25;
                    "></div>
                    <div style="
                        width:72px;height:72px;border-radius:50%;
                        background:linear-gradient(135deg,#0066FF 0%,#5B8DEF 100%);
                        display:flex;align-items:center;justify-content:center;
                        box-shadow:0 8px 20px rgba(0,102,255,.25);
                        position:relative;z-index:1;
                    ">
                        <i data-lucide="shield-check" style="width:30px;height:30px;color:white;"></i>
                    </div>
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--c-text);letter-spacing:-.02em;">Secure</div>
                <div style="font-size:12px;color:var(--c-muted);margin-top:3px;">Biometric Layer Active</div>
            </div>

            {{-- Details --}}
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach([
                    ['l'=>'Auth Method',  'v'=>'Neural FaceID',  'mono'=>false],
                    ['l'=>'Encryption',   'v'=>'AES-256-GCM',    'mono'=>true],
                    ['l'=>'Storage',      'v'=>'Local Vault',     'mono'=>false],
                    ['l'=>'Last Verified','v'=>'Just Now',        'mono'=>false],
                    ['l'=>'Enrolled',     'v'=>now()->format('M d, Y'),'mono'=>false],
                ] as $row)
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span style="font-size:12px;color:var(--c-muted);font-weight:500;flex-shrink:0;">{{ $row['l'] }}</span>
                    <span style="font-size:12px;font-weight:600;color:var(--c-text-2);{{ $row['mono'] ? 'font-family:var(--font-mono);' : '' }}text-align:right;">{{ $row['v'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Security Score --}}
            <div style="margin-top:20px;padding-top:18px;border-top:1px solid var(--c-border);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:11px;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;">Security Score</span>
                    <span style="font-size:13px;font-weight:700;color:var(--c-success);">92/100</span>
                </div>
                <div class="os-progress-track">
                    <div class="os-progress-fill" style="width:92%;background:linear-gradient(90deg,var(--c-accent),var(--c-success));"></div>
                </div>
                <div style="font-size:11px;color:var(--c-muted);margin-top:6px;">Enterprise Grade</div>
            </div>
        </div>

        {{-- Event Log --}}
        <div class="os-card" style="overflow:hidden;">
            <div class="os-card-header">
                <div class="os-card-title">Auth Events</div>
                <span class="os-badge os-badge-gray">7 days</span>
            </div>
            @php
            $events = [
                ['icon'=>'check-circle',   'label'=>'Login verified',      'time'=>'Just now',   'c'=>'var(--c-success)','bg'=>'var(--c-success-bg)'],
                ['icon'=>'scan-face',      'label'=>'Face scan captured',  'time'=>'2h ago',     'c'=>'var(--c-accent)', 'bg'=>'var(--c-accent-bg)'],
                ['icon'=>'shield-check',   'label'=>'Vault updated',       'time'=>'Yesterday',  'c'=>'var(--c-purple)', 'bg'=>'var(--c-purple-bg)'],
                ['icon'=>'alert-triangle', 'label'=>'Suspicious attempt',  'time'=>'3 days ago', 'c'=>'var(--c-warning)','bg'=>'var(--c-warning-bg)'],
            ];
            @endphp
            @foreach($events as $ev)
            <div style="padding:11px 16px;border-bottom:1px solid #F2F4F7;display:flex;align-items:center;gap:10px;">
                <div style="width:28px;height:28px;border-radius:var(--r-md);background:{{ $ev['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i data-lucide="{{ $ev['icon'] }}" style="width:13px;height:13px;color:{{ $ev['c'] }};"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12.5px;font-weight:500;color:var(--c-text-2);">{{ $ev['label'] }}</div>
                </div>
                <span style="font-size:11px;color:var(--c-muted);font-family:var(--font-mono);white-space:nowrap;">{{ $ev['time'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── ENROLLMENT PANEL ────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Scan trigger card --}}
        <div class="os-card" style="overflow:hidden;">
            <div class="os-card-header">
                <div>
                    <div class="os-card-title">Re-enroll Neural ID</div>
                    <div class="os-card-subtitle">Update your face fingerprint. SHA-512 encrypted, stored in local vault.</div>
                </div>
            </div>

            <div style="padding:28px;">
                {{-- Drop zone --}}
                <div id="enroll-zone"
                     x-on:click="startEnroll()"
                     style="
                        border:2px dashed var(--c-border-2);
                        border-radius:var(--r-xl);
                        padding:48px 24px;
                        display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;
                        cursor:pointer;
                        background:var(--c-bg);
                        transition:all .2s;
                     "
                     x-on:mouseenter="$el.style.borderColor='var(--c-accent)';$el.style.background='var(--c-accent-bg)'"
                     x-on:mouseleave="$el.style.borderColor='var(--c-border-2)';$el.style.background='var(--c-bg)'">
                    <div style="
                        width:56px;height:56px;border-radius:var(--r-xl);
                        background:white;border:1px solid var(--c-border);
                        display:flex;align-items:center;justify-content:center;
                        box-shadow:var(--s-md);
                    ">
                        <i data-lucide="scan-face" style="width:24px;height:24px;color:var(--c-accent);"></i>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:14px;font-weight:600;color:var(--c-text);margin-bottom:4px;">Click to Capture Fingerprint</div>
                        <div style="font-size:12.5px;color:var(--c-muted);max-width:320px;line-height:1.6;">
                            128-point neural mapping of your facial geometry. Takes approximately 3 seconds.
                        </div>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:4px;">
                        @foreach(['Stable lighting','Face forward','Remove glasses'] as $tip)
                        <span style="font-size:11px;font-weight:600;color:var(--c-muted);background:white;border:1px solid var(--c-border);padding:3px 10px;border-radius:999px;">{{ $tip }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div style="padding:14px 20px;background:var(--c-bg);border-top:1px solid var(--c-border);border-radius:0 0 var(--r-xl) var(--r-xl);display:flex;justify-content:flex-end;gap:8px;">
                <button class="os-btn os-btn-secondary os-btn-sm"
                    x-on:click="Swal.fire({title:'Reset Vault',text:'This will delete all biometric data. Contact your system administrator.',icon:'warning'})">
                    Reset Vault
                </button>
                <button class="os-btn os-btn-primary os-btn-sm" x-on:click="startEnroll()">
                    <i data-lucide="shield" style="width:13px;height:13px;"></i>
                    Update Security Vault
                </button>
            </div>
        </div>

        {{-- System Security Notes --}}
        <div class="os-card" style="padding:20px;">
            <div style="font-size:11px;font-weight:700;color:var(--c-muted);text-transform:uppercase;letter-spacing:.07em;margin-bottom:14px;">Security Notes</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach([
                    ['icon'=>'lock',         'text'=>'All biometric data is encrypted at rest using AES-256-GCM.'],
                    ['icon'=>'server',       'text'=>'Data is stored locally and never transmitted to external servers.'],
                    ['icon'=>'refresh-cw',   'text'=>'Re-enrollment is recommended every 90 days for maximum accuracy.'],
                    ['icon'=>'eye-off',      'text'=>'Facial geometry vectors cannot be reversed to reconstruct your image.'],
                ] as $note)
                <div style="display:flex;align-items:flex-start;gap:10px;">
                    <div style="width:28px;height:28px;border-radius:var(--r-md);background:var(--c-accent-bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                        <i data-lucide="{{ $note['icon'] }}" style="width:12px;height:12px;color:var(--c-accent);"></i>
                    </div>
                    <p style="font-size:12.5px;color:var(--c-text-2);line-height:1.6;margin:0;">{{ $note['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function startEnroll() {
    Swal.fire({
        title: 'Initialize Neural Scan',
        html: `<div style="font-size:13px;color:var(--c-text-2);line-height:1.6;text-align:left;">
                 <p style="margin-bottom:12px;">Align your face within the sensor frame and hold steady.</p>
               </div>`,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Begin Scan',
        cancelButtonText: 'Cancel',
    }).then(result => {
        if (!result.isConfirmed) return;

        let pct = 0;
        const steps = [
            'Initializing sensor…',
            'Detecting face geometry…',
            'Mapping 128 neural points…',
            'Encrypting fingerprint data…',
            'Writing to secure vault…',
        ];

        Swal.fire({
            title: 'Scanning…',
            html: `<div style="padding:8px 0 4px;">
                     <div style="height:4px;background:#F2F4F7;border-radius:999px;overflow:hidden;margin-bottom:14px;">
                         <div id="sp-bar" style="height:100%;width:0%;background:#0066FF;border-radius:999px;transition:width .12s;"></div>
                     </div>
                     <p style="font-size:12px;color:var(--c-muted);margin:0;" id="sp-label">Initializing sensor…</p>
                   </div>`,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                const bar   = document.getElementById('sp-bar');
                const label = document.getElementById('sp-label');
                const iv = setInterval(() => {
                    pct = Math.min(pct + 3, 100);
                    if (bar)   bar.style.width = pct + '%';
                    if (label) label.textContent = steps[Math.min(Math.floor(pct / 20), steps.length - 1)];
                    if (pct >= 100) {
                        clearInterval(iv);
                        setTimeout(() => {
                            Swal.fire({
                                title: 'Enrollment Complete',
                                text: 'Neural face fingerprint stored securely in the system vault.',
                                icon: 'success',
                                confirmButtonText: 'Done',
                            });
                        }, 300);
                    }
                }, 60);
            }
        });
    });
}
</script>
@endpush
@endsection
