<?php
$file = 'resources/views/builds/show.blade.php';
$content = file_get_contents($file);

$replacements = [
    'background: rgba(255, 255, 255, 0.7);' => 'background: color-mix(in srgb, var(--surface) 85%, transparent);',
    'backdrop-filter: blur(12px) saturate(180%);' => 'backdrop-filter: blur(24px);',
    '-webkit-backdrop-filter: blur(12px) saturate(180%);' => '-webkit-backdrop-filter: blur(24px);',
    'border: 1px solid rgba(255, 255, 255, 0.4);' => 'border: 1px solid var(--border-default);',
    'background: rgba(255, 255, 255, 0.9);' => 'background: var(--surface);',
    'box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);' => 'box-shadow: var(--shadow-lg);',
    'box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);' => 'box-shadow: var(--shadow-xl);',
    
    // Scenery panel
    'background: #ffffff;' => 'background: var(--surface);',
    'border: 1px solid #e2e8f0;' => 'border: 1px solid var(--border-default);',
    'box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);' => 'box-shadow: var(--shadow-lg);',
    'color: #94a3b8;' => 'color: var(--text-tertiary);',
    'color: #1e293b;' => 'color: var(--text-primary);',
    'background: #f1f5f9;' => 'background: var(--bg-secondary);',
    'background: #eff6ff;' => 'background: var(--accent-light);',
    'border-color: #93c5fd;' => 'border-color: var(--accent);',
    
    // SweetAlert2
    'background: rgba(255, 255, 255, 0.85);' => 'background: color-mix(in srgb, var(--surface) 85%, transparent);',
    'backdrop-filter: blur(20px) saturate(180%);' => 'backdrop-filter: blur(24px);',
    '-webkit-backdrop-filter: blur(20px) saturate(180%);' => '-webkit-backdrop-filter: blur(24px);',
    'color: #1a1a1a;' => 'color: var(--text-primary);',
    'color: #4b5563;' => 'color: var(--text-secondary);',
    
    // Night active button gradient removal -> use solid token colors
    'background: linear-gradient(135deg, #dbeafe, #eff6ff);' => 'background: var(--accent-muted);',
    'color: #1e40af;' => 'color: var(--accent-active);',
    'box-shadow: 0 0 10px rgba(59,130,246,0.25);' => 'box-shadow: var(--shadow-sm);',
    'background: linear-gradient(135deg, #bfdbfe, #dbeafe);' => 'background: var(--accent-light);',
];

foreach($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Updated floating-toolbar and scenery-panel CSS in show.blade.php\n";
