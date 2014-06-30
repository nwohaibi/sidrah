<?php

// Start session for this captcha.
session_start();

require_once("inc/functions.inc.php");

$page = mysql_real_escape_string(@$_GET["page"]);

switch ($page)
{
	default: case "feedback":
		$session_name = "feedback";
	break;
	
	case "reset_password":
		$session_name = "reset_password";
	break;
}

// Generate a random number.
$random_number = sprintf("%05d", rand(0, 99999));
$md5_random_number = md5_salt($random_number);

// Set the session variable.
$_SESSION[$session_name] = $md5_random_number;

// Draw the image.
$image = imagecreatetruecolor(46, 20);
$backgrund_color = imagecolorallocate($image, 170, 170, 170);
$foreground_color = imagecolorallocate($image, 33, 33, 33);

// Fill the background.
imagefill($image, 0, 0, $backgrund_color);

// Draw the foreground text.
imagestring($image, 4, 3, 3, $random_number, $foreground_color);

// Make the background transparent
imagecolortransparent($image, $backgrund_color);

// Output the image.
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

