<?php

$file = 'resources/views/layouts/editor.blade.php';
$content = file_get_contents($file);

$replacements = [
    'var(--bg-primary)' => 'var(--bg)',
    'var(--border)' => 'var(--border-default)',
    'var(--accent-dark)' => 'var(--accent-hover)',
    '0 8px 32px rgba(0,0,0,0.12)' => 'var(--shadow-lg)',
    '0 4px 16px rgba(0,0,0,0.1)' => 'var(--shadow-md)',
    '0 4px 12px rgba(0, 0, 0, 0.15)' => 'var(--shadow-md)',
    '#1e293b' => 'var(--bg-secondary)',
    'rgba(30, 41, 59, 0.9)' => 'var(--surface)',
    '#334155' => 'var(--border-strong)',
    'background: #e2e8f0;' => 'background: transparent;', // Canvas Area
    'box-shadow: 20px 0 50px rgba(0, 0, 0, 0.08), 
                        1px 0 0 rgba(255, 255, 255, 0.2) inset;' => 'box-shadow: var(--shadow-2xl);',
    'backdrop-filter: blur(25px) saturate(200%);' => 'backdrop-filter: blur(24px);',
    '-webkit-backdrop-filter: blur(25px) saturate(200%);' => '-webkit-backdrop-filter: blur(24px);',
    // Update topbar to glassmorphism
    "        .editor-topbar {\n            display: flex;\n            align-items: center;\n            justify-content: space-between;\n            padding: 8px 16px;\n            background: var(--surface);\n            border-bottom: 1px solid var(--border-default);\n            height: 48px;\n            flex-shrink: 0;\n        }" => "        .editor-topbar {\n            display: flex;\n            align-items: center;\n            justify-content: space-between;\n            padding: 8px 16px;\n            background: color-mix(in srgb, var(--surface) 85%, transparent);\n            backdrop-filter: blur(24px);\n            -webkit-backdrop-filter: blur(24px);\n            border-bottom: 1px solid var(--border-default);\n            height: 56px;\n            flex-shrink: 0;\n            position: relative;\n            z-index: 100;\n        }",
    // Floating properties panel update
    "        .properties-panel {\n            position: fixed;\n            top: 60px;\n            right: 16px;\n            width: 280px;\n            background: var(--surface);\n            border: 1px solid var(--border-default);\n            border-radius: 12px;\n            box-shadow: var(--shadow-lg);\n            padding: 16px;\n            display: none;\n            z-index: 100;\n            max-height: calc(100vh - 200px);\n            overflow-y: auto;\n        }" => "        .properties-panel {\n            position: fixed;\n            top: 70px;\n            right: 24px;\n            width: 300px;\n            background: color-mix(in srgb, var(--surface) 95%, transparent);\n            backdrop-filter: blur(24px);\n            -webkit-backdrop-filter: blur(24px);\n            border: 1px solid var(--border-default);\n            border-radius: var(--radius-xl);\n            box-shadow: var(--shadow-xl);\n            padding: 24px;\n            display: none;\n            z-index: 100;\n            max-height: calc(100vh - 200px);\n            overflow-y: auto;\n            transition: var(--transition-spring);\n        }",
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Updated editor CSS tokens\n";
