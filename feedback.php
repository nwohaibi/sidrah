<?php

// Start session for this captcha.
session_start();

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

// Submit
$submit = mysql_real_escape_string(@$_POST["submit"]);

if (!empty($submit))
{
	$type = trim(mysql_real_escape_string(@$_POST["type"]));
	$page = trim(mysql_real_escape_string(@$_POST["page"]));
	$content = trim(mysql_real_escape_string(@$_POST["content"]));
	$captcha = trim(mysql_real_escape_string(@$_POST["captcha"]));
	
	if (empty($type) || empty($page) || empty($content) || empty($captcha))
	{
		echo error_message("الرجاء إدخال الحقول المطلوبة.");
		return;
	}
	
	$md5_captcha = md5_salt($captcha);
	
	// Check if the captcha is missed up.
	if ($_SESSION["feedback"] != $md5_captcha)
	{
		echo error_message("الرجاء إدخال رمز التحقّق بشكل صحيح.");
		return;
	}
	
	$user_agent = mysql_real_escape_string(@$_SERVER["HTTP_USER_AGENT"]);
	$http_referer = mysql_real_escape_string(@$_SERVER["HTTP_REFERER"]);
	$now = time();
	
	if ($user["group"] != "visitor")
	{
		$user_agent .= "; $user[username];";
	}
	
	// Insert a new feedback.
	$insert_feedback_query = mysql_query("INSERT INTO feedback (type, page, content, user_agent, http_referer, created) VALUES ('$type', '$page', '$content', '$user_agent', '$http_referer', '$now')");
	
	echo success_message(
		"شكراً لك على إخبارنا برأيك حول الموقع.",
		"index.php"
	);
	
	return;
}
else
{
	// Get page.
	$page = mysql_real_escape_string(@$_GET["page"]);

	// Get the header.
	$header = website_header(
		"أخبرنا برأيك",
		"صفحة من أجل الحصول على آراء الزوّار حول الموقع.",
		array(
			"عائلة", "الزغيبي", "شجرة", "أخبرنا", "برأيك"
		)
	);

	// Get the content.
	$content = template(
		"views/feedback.html",
		array(
			"page" => $page
		)
	);

	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
