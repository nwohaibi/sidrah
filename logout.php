<?php

require_once("inc/functions.inc.php");

$cookie_time = time()-21600;

// Logout
setcookie("sidrah_username", "", $cookie_time);
setcookie("sidrah_password", "", $cookie_time);

echo success_message(
	"تمت عملية تسجيل الخروج بنجاح.",
	"index.php"
);
