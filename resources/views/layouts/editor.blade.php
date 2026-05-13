<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='20' fill='%230066FF'/><path d='M50 20 L80 38 L80 62 L50 80 L20 62 L20 38 Z' fill='none' stroke='white' stroke-width='5'/><path d='M50 20 L50 80 M20 38 L80 62 M80 38 L20 62' stroke='white' stroke-width='3' opacity='0.5'/></svg>">
    <title>@yield('title', 'Editor') — SpatialSync</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|jetbrains-mono:400,500">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Supabase JS for Realtime -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const _SwalBase = Swal;
        window.Swal = _SwalBase.mixin({
            background: '#ffffff',
            color: '#1a1a2e',
            confirmButtonColor: '#0066FF',
            cancelButtonColor: '#64748b',
            iconColor: '#0066FF',
            customClass: {
                popup: 'swal-global-popup',
                title: 'swal-global-title',
                htmlContainer: 'swal-global-body'
            }
        });
    </script>
    <style>
        .swal-global-popup {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
            border-radius: 24px !important;
            padding: 2rem !important;
            border: 1px solid rgba(0,0,0,0.06) !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12) !important;
        }
        .swal-global-title { font-weight: 800 !important; letter-spacing: -0.02em !important; }
        .swal-global-body { font-size: 1rem !important; line-height: 1.6 !important; }
    </style>

    <!-- jsPDF for Blueprint Export -->
    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

    <!-- Blueprint Exporter -->
    <script src="{{ asset('js/blueprint-exporter.js') }}"></script>

    <!-- Editor Styles -->
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg);
        }

        .editor-layout {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Top Bar - Minimal */
        .editor-topbar {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) auto minmax(200px, 1fr);
            align-items: center;
            gap: 16px;
            padding: 0 16px;
            background: color-mix(in srgb, var(--surface) 85%, transparent);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border-default);
            height: 56px;
            flex-shrink: 0;
            position: relative;
            z-index: 1010;
        }

        .editor-topbar__left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .editor-topbar__title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .editor-topbar__center {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            min-width: 0;
        }

        .editor-topbar__right {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            min-width: 0;
        }

        @media (max-width: 1400px) {
            .editor-topbar__title {
                display: none;
            }
            .hidden-md {
                display: none !important;
            }
        }

        /* Canvas Area */
        .editor-canvas {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: transparent;
        }

        #editor-canvas {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* Placement Mode Indicator */
        .placement-indicator {
            position: absolute;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(59, 130, 246, 0.95);
            color: white;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            pointer-events: none;
            z-index: 10;
        }

        .placement-indicator svg {
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Bottom Toolbar - Bloxburg Style */
        .editor-bottom {
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border-top: 1px solid var(--border-default);
            flex-shrink: 0;
        }

        /* Category Tabs */
        .editor-tabs {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 16px;
            border-bottom: 1px solid var(--border-default);
            overflow-x: auto;
        }

        .editor-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .editor-tab:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .editor-tab.active {
            background: var(--accent);
            color: white;
        }

        .editor-tab svg {
            width: 16px;
            height: 16px;
        }

        /* Parts Grid */
        .editor-parts {
            display: flex;
            gap: 8px;
            padding: 12px 16px;
            overflow-x: auto;
            min-height: 88px;
        }

        .part-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 12px 16px;
            min-width: 80px;
            background: var(--bg-secondary);
            border: 2px solid transparent;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .part-card:hover {
            background: var(--bg-tertiary);
            border-color: var(--border-default);
            transform: translateY(-2px);
        }

        .part-card.active {
            background: var(--accent-light);
            border-color: var(--accent);
        }

        .part-card__icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--surface);
            border-radius: 8px;
            color: var(--text-secondary);
        }

        .part-card.active .part-card__icon {
            background: var(--accent);
            color: white;
        }

        .part-card__icon svg {
            width: 18px;
            height: 18px;
        }

        .part-card__name {
            font-size: 11px;
            font-weight: 500;
            color: var(--text-secondary);
            text-align: center;
        }

        .part-card.active .part-card__name {
            color: var(--accent);
        }

        /* Properties Panel - Floating */
        .properties-panel {
            position: fixed;
            top: 70px;
            right: 24px;
            width: 300px;
            background: color-mix(in srgb, var(--surface) 95%, transparent);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-default);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: 24px;
            display: none;
            z-index: 100;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            transition: var(--transition-spring);
        }

        .properties-panel.visible {
            display: block;
        }

        .properties-panel h3 {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: 12px;
        }

        .property-section {
            margin-bottom: 16px;
        }

        .property-section label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: 8px;
        }

        .property-value {
            font-size: 14px;
            color: var(--text-primary);
            text-transform: capitalize;
        }

        .property-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .property-row span {
            font-size: 12px;
            color: var(--text-tertiary);
            width: 20px;
        }

        .property-row input[type="number"] {
            flex: 1;
            padding: 6px 10px;
            border: 1px solid var(--border-default);
            border-radius: 6px;
            font-size: 13px;
            background: var(--bg-secondary);
        }

        .property-row input[type="range"] {
            width: 100%;
        }

        .property-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn svg {
            width: 16px;
            height: 16px;
        }

        .btn--primary {
            background: var(--accent);
            color: white;
        }

        .btn--primary:hover {
            background: var(--accent-hover);
        }

        .btn--secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border-default);
        }

        .btn--secondary:hover {
            background: var(--bg-tertiary);
        }

        .btn--danger {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn--danger:hover {
            background: #fecaca;
        }

        .btn--ghost {
            background: transparent;
            color: var(--text-secondary);
        }

        .btn--ghost:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .btn--sm {
            padding: 6px 10px;
            font-size: 12px;
        }

        .btn--sm svg {
            width: 14px;
            height: 14px;
        }

        /* Floor Selector */
        .floor-selector {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--bg-secondary);
            padding: 4px;
            border-radius: 8px;
        }

        .floor-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 28px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            background: transparent;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .floor-btn:hover {
            background: var(--surface);
            color: var(--text-primary);
        }

        .floor-btn.active {
            background: var(--accent);
            color: white;
        }

        .floor-btn--add {
            color: var(--accent);
        }

        /* Debug Bar */
        .debug-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 16px;
            background: var(--bg-secondary);
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
        }

        .debug-bar span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .debug-bar .status-ok {
            color: #4ade80;
        }

        .debug-bar .status-error {
            color: #f87171;
        }

        /* Toast Container */
        .toast-container {
            position: fixed;
            bottom: 180px;
            right: 16px;
            z-index: 1001;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .toast {
            padding: 12px 16px;
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 8px;
            box-shadow: var(--shadow-md);
            font-size: 13px;
            animation: slideIn 0.3s ease;
        }

        .toast--success {
            border-left: 4px solid #22c55e;
        }

        .toast--error {
            border-left: 4px solid #ef4444;
        }

        .toast--info {
            border-left: 4px solid #3b82f6;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Keyboard Hints */
        .keyboard-hint {
            position: fixed;
            bottom: 300px;
            right: 16px;
            background: var(--surface);
            color: #e2e8f0;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1.6;
            z-index: 100;
        }

        .keyboard-hint kbd {
            display: inline-block;
            padding: 2px 6px;
            background: var(--border-strong);
            border-radius: 4px;
            font-size: 10px;
            margin-right: 4px;
        }

        /* Bounds Warning (Out of Grid) */
        .bounds-warning {
            position: absolute;
            bottom: 180px;
            left: 50%;
            transform: translateX(-50%);
            display: none;
            align-items: center;
            gap: 12px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.4);
            z-index: 1000;
            animation: bounceIn 0.3s ease-out;
        }

        .bounds-warning i {
            flex-shrink: 0;
        }

        .bounds-warning button {
            margin-left: 8px;
            padding: 6px 12px;
            background: white;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .bounds-warning button:hover {
            background: #fef2f2;
            transform: scale(1.05);
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: translateX(-50%) scale(0.8);
            }
            50% {
                transform: translateX(-50%) scale(1.05);
            }
            100% {
                opacity: 1;
                transform: translateX(-50%) scale(1);
            }
        }

        /* Property Panel Tabs */
        .property-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            border-bottom: 2px solid var(--border-default);
        }

        .property-tab {
            flex: 1;
            padding: 8px 16px;
            background: transparent;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: -2px;
        }

        .property-tab:hover {
            color: var(--text-primary);
        }

        .property-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }

        .property-tab-content {
            display: none;
        }

        .property-tab-content.active {
            display: block;
        }

        /* Material Buttons */
        .material-btn {
            padding: 12px;
            background: var(--bg-secondary);
            border: 2px solid var(--border-default);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .material-btn:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent);
        }

        .material-btn.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        /* Collaboration Sidebar */
        .sidebar {
            position: fixed;
            top: 48px;
            left: -350px;
            width: 350px;
            height: calc(100vh - 48px);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid var(--border-default);
            z-index: 1000;
            transition: left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-2xl);
        }

        .sidebar--open {
            left: 0;
        }

        .sidebar__header {
            padding: 16px;
            border-bottom: 1px solid var(--border-default);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar__tabs {
            display: flex;
            padding: 4px;
            background: var(--bg-secondary);
            border-radius: 12px;
            margin: 12px 16px;
        }

        .sidebar__tab {
            flex: 1;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .sidebar__tab.active {
            background: var(--surface);
            color: var(--accent);
            box-shadow: var(--shadow-sm);
        }

        .sidebar__content {
            flex: 1;
            overflow-y: auto;
            padding: 0 16px 24px;
        }

        .sidebar-section {
            margin-bottom: 24px;
        }

        .sidebar-section__title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Search & Members */
        .search-box {
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 12px 10px 36px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: 10px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .search-box input:focus {
            background: var(--surface);
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px var(--accent-light);
        }

        .search-box svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            color: var(--text-tertiary);
            pointer-events: none;
            z-index: 2;
        }

        .search-results {
            margin-top: 8px;
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .search-result {
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: background 0.2s;
        }

        .search-result:not(:last-child) {
            border-bottom: 1px solid var(--border-default);
        }

        .search-result:hover {
            background: var(--bg-secondary);
        }

        .member-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .member-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .member-role {
            font-size: 11px;
            color: var(--text-tertiary);
            text-transform: capitalize;
        }

        /* Ultra-Slim Pill Toasts */
        .offset-toast-container {
            top: 60px !important; /* Push container strictly below navbar */
            padding: 0 !important;
        }

        .compact-toast.swal2-popup {
            width: auto !important;
            min-width: 100px !important;
            padding: 4px 10px !important;
            margin: 0 !important;
            border-radius: 99px !important;
            background: color-mix(in srgb, var(--surface) 95%, transparent) !important;
            backdrop-filter: blur(12px) !important;
            border: 1px solid var(--border-default) !important;
            box-shadow: var(--shadow-md) !important;
            color: var(--text-primary) !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 6px !important;
        }

        .compact-toast.swal2-popup .swal2-title {
            font-size: 11px !important;
            font-weight: 500 !important;
            color: var(--text-primary) !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            white-space: nowrap !important;
        }

        /* Fully hide the bulky native icon containers for compact mode */
        .compact-toast.swal2-popup .swal2-icon {
            display: none !important;
        }

        .compact-toast.swal2-popup .swal2-timer-progress-bar {
            background: #6366f1 !important;
            height: 2px !important;
        }

        /* Chat */
        .chat-messages {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 8px 0;
        }

        .message {
            max-width: 85%;
            padding: 10px 12px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.4;
            position: relative;
        }

        .message--other {
            align-self: flex-start;
            background: var(--bg-secondary);
            color: var(--text-primary);
            border-bottom-left-radius: 4px;
        }

        .message--mine {
            align-self: flex-end;
            background: var(--accent);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message__user {
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 4px;
            opacity: 0.8;
        }

        .sidebar__footer {
            padding: 16px;
            border-top: 1px solid var(--border-default);
            display: flex;
            gap: 8px;
        }

        .chat-input {
            flex: 1;
            padding: 10px 12px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-default);
            border-radius: 10px;
            font-size: 13px;
        }

        /* Content Push Logic */
        .editor-layout {
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .editor-layout.sidebar-open {
            margin-left: 350px;
        }

        @media (max-width: 768px) {
            .editor-layout.sidebar-open {
                margin-left: 0;
            }
            .sidebar {
                width: 100%;
                left: -100%;
            }
            .editor-topbar {
                grid-template-columns: 1fr auto auto !important;
                gap: 6px !important;
                padding: 0 8px !important;
            }
            .editor-topbar__left {
                min-width: 0 !important;
            }
            .editor-topbar__title {
                display: none !important;
            }
            .editor-topbar__center .navbar-shortcuts,
            .editor-topbar__center .nav-shortcut-divider,
            .editor-topbar__right .export-dropdown,
            .editor-topbar__right .editor-topbar__divider {
                display: none !important;
            }
            .floor-btn--arrow {
                width: 28px !important;
                height: 24px !important;
            }
            .floor-display {
                font-size: 11px !important;
            }
            .properties-panel {
                width: 100% !important;
                right: 0 !important;
                left: 0 !important;
                top: auto !important;
                bottom: 0 !important;
                max-height: 50vh !important;
                border-radius: var(--radius-xl) var(--radius-xl) 0 0 !important;
                z-index: 1100 !important;
            }
            .keyboard-hint {
                display: none !important;
            }
            .editor-tab span {
                display: none !important;
            }
            .editor-tab {
                padding: 8px !important;
            }
            .editor-bottom {
                min-height: auto !important;
            }
            .editor-parts {
                padding: 8px !important;
                min-height: 64px !important;
            }
        }

        /* ── Dropdown Divider ── */
        .dropdown-divider {
            height: 1px;
            background: var(--border-default);
            margin: 4px 8px;
        }

        /* ── Blueprint Modal UI ── */
        .blueprint-opt-btn {
            padding: 7px 16px;
            border-radius: 8px;
            border: 1.5px solid var(--border-default);
            background: var(--surface);
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.15s, background 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .blueprint-opt-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .blueprint-opt-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }
        .blueprint-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
            padding: 10px 14px;
            background: var(--bg-secondary);
            border-radius: 10px;
            border: 1px solid var(--border-default);
            user-select: none;
            transition: background 0.15s;
        }
        .blueprint-toggle:hover { background: var(--bg-tertiary); }
        .toggle-track {
            width: 40px; height: 22px;
            background: var(--border-default);
            border-radius: 99px;
            position: relative;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .toggle-track.active { background: var(--accent); }
        .toggle-thumb {
            position: absolute;
            top: 3px; left: 3px;
            width: 16px; height: 16px;
            border-radius: 50%;
            background: #fff;
            transition: transform 0.2s cubic-bezier(0.4,0,0.2,1);
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
        }
        .toggle-track.active .toggle-thumb { transform: translateX(18px); }

        /* ── Export Dropdown ── */
        .export-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 220px;
            background: var(--surface);
            border: 1px solid var(--border-default);
            border-radius: 14px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.08);
            padding: 6px;
            z-index: 9999;
            overflow: visible;
        }

        .dropdown-header {
            padding: 6px 10px 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 9px 10px;
            border-radius: 8px;
            border: none;
            background: transparent;
            cursor: pointer;
            text-decoration: none;
            text-align: left;
            transition: background 0.12s;
        }

        .dropdown-item:hover {
            background: var(--bg-secondary);
        }

        .dropdown-item__icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--text-secondary);
        }

        .dropdown-item__icon svg { width: 16px; height: 16px; }

        .dropdown-item__content { flex: 1; min-width: 0; }

        .dropdown-item__title {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
        }

        .dropdown-item__desc {
            font-size: 11px;
            color: var(--text-tertiary);
            white-space: nowrap;
            margin-top: 1px;
        }
    </style>

    @stack('styles')
</head>
<body>
    @yield('content')

    <!-- Initialize Icons & Toasts -->
    <script>
        function showSweetToast(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                const iconNames = { success: 'check-circle', error: 'alert-circle', info: 'info' };
                const iconName = iconNames[type] || 'info';
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        container: 'offset-toast-container',
                        popup: 'compact-toast'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                        // Force Lucide re-render for the icon inside the title
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                });
                
                // Embed Lucide icon directly into the title for total size control
                Toast.fire({ 
                    title: `<i data-lucide="${iconName}" style="width: 14px; height: 14px;"></i> ${message}`
                });
            }
        }

        window.isConfirmingReload = false;

        async function confirmReload() {
            if (typeof Swal !== 'undefined') {
                window.isConfirmingReload = true;
                const result = await Swal.fire({
                    title: 'Reload site?',
                    text: 'Changes you made may not be saved.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Reload',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        container: 'swal-premium',
                        confirmButton: 'swal-confirm-btn',
                        cancelButton: 'swal-cancel-btn'
                    }
                });

                if (result.isConfirmed) {
                    if (typeof editor !== 'undefined') editor.hasUnsavedChanges = false;
                    window.onbeforeunload = null; 
                    location.reload();
                } else {
                    window.isConfirmingReload = false;
                }
            } else {
                location.reload();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Handle Laravel Session Flashes
            @if(session('success'))
                showSweetToast("{{ session('success') }}", 'success');
            @endif
            @if(session('error'))
                showSweetToast("{{ session('error') }}", 'error');
            @endif
            @if(session('info'))
                showSweetToast("{{ session('info') }}", 'info');
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
