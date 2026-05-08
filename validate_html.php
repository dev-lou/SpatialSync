<?php
$html = file_get_contents('resources/views/home.blade.php'); 
$dom = new DOMDocument(); 
libxml_use_internal_errors(true); 
$dom->loadHTML($html); 
foreach (libxml_get_errors() as $error) { 
    echo 'Line ' . $error->line . ': ' . $error->message . "\n"; 
} 
libxml_clear_errors();
