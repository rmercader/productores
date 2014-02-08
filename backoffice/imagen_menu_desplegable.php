<?php

// Inicio Session
session_start();
// Includes
include('../app.config.php');
include('./admin.config.php');
$img = imagecreatefromgif(DIR_HTML.'img/lnk_fnd.gif');
$color = ImageColorAllocate($img, 0, 0, 0);
imagettftext($img, 4, 0, 3, 15, $color, DIR_HTML.'src/digitale'.'.ttf', preg_replace("/([a-zA-Z])/", "\\1 ", strtoupper($_GET['TEXTO'])));

header('Content-type: image/png');
imagepng($img);
imagedestroy($img);
?>