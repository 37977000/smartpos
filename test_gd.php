<?php
$image = imagecreatetruecolor(100, 100);
$bg = imagecolorallocate($image, 255, 0, 0);
imagefill($image, 0, 0, $bg);
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>
