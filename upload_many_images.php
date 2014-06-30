<?php

require_once("inc/functions.inc.php");

$check = @$_POST["check"];
$image = @$_POST["image"];

if (!empty($check) && count($check) > 0)
{
	foreach ($check as $member_id => $v)
	{
		echo "$member_id<br />";
		copyimage($image[$member_id], $member_id);
	}
}

function copyimage($url, $member_id)
{
	$ext = substr($url, strrpos($url, "."));
	$ext = str_replace(".", "", $ext);

	if ($ext == "jpeg" || $ext == "jpg")
		$src = imagecreatefromjpeg($url);
	else if ($ext == "png")
		$src = imagecreatefrompng($url);
	
	$new = imagecreatetruecolor(64, 64);
	
	imagecopyresampled($new, $src, 0, 0, 0, 0, 73, 73, 64, 64);
	
	@unlink("views/pics/$member_id.png");
	imagepng($new, "views/pics/$member_id.png");
	
	imagedestroy($src);
	imagedestroy($new);
}
