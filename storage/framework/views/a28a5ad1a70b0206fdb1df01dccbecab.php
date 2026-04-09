<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Editor'); ?> — ConstructHub</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|jetbrains-mono:400,500">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="<?php echo e(asset('css/tokens.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/components.css')); ?>">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
            background: var(--bg-primary);
        }

        .editor-layout {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Top Bar - Minimal */
        .editor-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            height: 48px;
            flex-shrink: 0;
        }

        .editor-topbar__left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .editor-topbar__logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--text-primary);
            text-decoration: none;
        }

        .editor-topbar__logo svg {
            width: 24px;
            height: 24px;
            color: var(--accent);
        }

        .editor-topbar__divider {
            width: 1px;
            height: 24px;
            background: var(--border);
        }

        .editor-topbar__title {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .editor-topbar__center {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .editor-topbar__right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Canvas Area */
        .editor-canvas {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: #e2e8f0;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
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
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }

        /* Category Tabs */
        .editor-tabs {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 8px 16px;
            border-bottom: 1px solid var(--border);
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
            border-color: var(--border);
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
            top: 60px;
            right: 16px;
            width: 280px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            padding: 16px;
            display: none;
            z-index: 100;
            max-height: calc(100vh - 200px);
            overflow-y: auto;
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
            border: 1px solid var(--border);
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
            background: var(--accent-dark);
        }

        .btn--secondary {
            background: var(--bg-secondary);
            color: var(--text-primary);
            border: 1px solid var(--border);
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
            background: #1e293b;
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
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
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
            bottom: 180px;
            left: 16px;
            background: rgba(30, 41, 59, 0.9);
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
            background: #334155;
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
            border-bottom: 2px solid var(--border);
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
            border: 2px solid var(--border);
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
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php echo $__env->yieldContent('content'); ?>

    <!-- Initialize Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/layouts/editor.blade.php ENDPATH**/ ?>