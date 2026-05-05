<?php
$im = imagecreatefrompng('public/images/og-image.png');
$bg = imagecreatetruecolor(imagesx($im), imagesy($im));
imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
imagealphablending($bg, TRUE);
imagecopy($bg, $im, 0, 0, 0, 0, imagesx($im), imagesy($im));
imagejpeg($bg, 'public/images/og-image.jpg', 90);
imagedestroy($im);
imagedestroy($bg);
echo "Converted to JPG. Size: " . filesize('public/images/og-image.jpg');
