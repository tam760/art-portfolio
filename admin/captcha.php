<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

ob_clean();

$width = 120;
$height = 40;
$codeLen = 4;

if (!function_exists('imagecreatetruecolor')) {
    header('Content-Type: image/png');
    $img = imagecreate(120, 40);
    imagefill($img, 0, 0, imagecolorallocate($img, 30, 48, 64));
    $textColor = imagecolorallocate($img, 255, 255, 255);
    imagestring($img, 2, 10, 15, 'GD NOT SUPPORTED', $textColor);
    imagepng($img);
    imagedestroy($img);
    exit;
}

$image = imagecreatetruecolor($width, $height);
$bgColor = imagecolorallocate($image, 30, 48, 64);
imagefill($image, 0, 0, $bgColor);

$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$code = '';
for ($i = 0; $i < $codeLen; $i++) {
    $code .= $chars[mt_rand(0, strlen($chars) - 1)];
}

$_SESSION['captcha_code'] = $code;

$colors = [
    imagecolorallocate($image, 212, 176, 128),
    imagecolorallocate($image, 196, 149, 106),
    imagecolorallocate($image, 176, 128, 80),
    imagecolorallocate($image, 200, 180, 150),
];

for ($i = 0; $i < $codeLen; $i++) {
    $char = $code[$i];
    $color = $colors[mt_rand(0, count($colors) - 1)];
    $fontSize = mt_rand(4, 5);
    $x = 10 + $i * 28 + mt_rand(-4, 4);
    $y = 14 + mt_rand(-3, 3);
    imagestring($image, $fontSize, $x, $y, $char, $color);
}

for ($i = 0; $i < 4; $i++) {
    $lineColor = imagecolorallocate($image, mt_rand(100, 150), mt_rand(100, 150), mt_rand(100, 150));
    imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
}

for ($i = 0; $i < 30; $i++) {
    $dotColor = imagecolorallocate($image, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200));
    imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $dotColor);
}

header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
imagepng($image);
imagedestroy($image);
exit;
