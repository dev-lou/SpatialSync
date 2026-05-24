<?php

$file = 'resources/views/builds/show.blade.php';
$content = file_get_contents($file);

$replacements = [
    // Paint Mode Overlay active selection display
    'background: rgba(0,0,0,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid rgba(255,255,255,0.05);' => 'background: var(--bg-secondary); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--border-default);',

    // Paint selection preview border
    'border: 2px solid rgba(255,255,255,0.8);' => 'border: 2px solid var(--border-default);',

    // Bottom Tools Grid active background
    'background: rgba(var(--accent-rgb), 0.05);' => 'background: var(--accent-light);',
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

file_put_contents($file, $content);
echo "Updated show.blade.php inline styles\n";
