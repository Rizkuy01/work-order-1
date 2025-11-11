<?php
session_start();

// generate code
$captcha_code = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
$_SESSION['captcha_text'] = $captcha_code;

// buat gambar
$width = 100;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// warna
$bg_color    = imagecolorallocate($image, 245, 245, 245);
$text_color  = imagecolorallocate($image, 200, 0, 0);
$line_color  = imagecolorallocate($image, 100, 100, 100);
$pixel_color = imagecolorallocate($image, 150, 150, 150);

// latar belakang
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// garis acak
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand(0, $height), $width, rand(0, $height), $line_color);
}

// titik acak
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $pixel_color);
}

// teks captcha
$font = __DIR__ . '/../assets/fonts/arial.ttf';
$font_size = 18;
imagettftext($image, $font_size, rand(-10,10), 15, 30, $text_color, $font, $captcha_code);

// output
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
