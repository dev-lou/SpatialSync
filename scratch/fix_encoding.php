<?php
$files = [
    'public/js/build-editor.js',
    'resources/js/build-editor.js',
    'resources/views/builds/show.blade.php',
    'resources/views/layouts/editor.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('ΓÇö', '-', $content); 
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
