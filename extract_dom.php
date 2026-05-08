<?php
$html = file_get_contents('resources/views/home.blade.php');
$dom = new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$nodes = $xpath->query('//*[contains(@class, "bento__header")]');
if ($nodes->length > 0) {
    $node = $nodes->item(0);
    $path = '';
    while ($node && $node->nodeName !== '#document') {
        $class = $node->hasAttribute('class') ? '.' . str_replace(' ', '.', $node->getAttribute('class')) : '';
        $path = $node->nodeName . $class . ' > ' . $path;
        $node = $node->parentNode;
    }
    echo $path;
} else {
    echo 'Not found';
}
?>
