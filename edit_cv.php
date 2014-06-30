<?php

require_once("inc/functions.inc.php");

$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Get the id of the user.
$id = mysql_real_escape_string(@$_GET["id"]);

$member = get_member_id($id);

if ($member == false)
{
	echo error_message("لم يتم العثور على العضو المطلوب.");
	return;
}

$submit = mysql_real_escape_string(@$_POST["submit"]);

if (!empty($submit))
{
	$info = htmlspecialchars(trim(mysql_real_escape_string(@$_POST["info"])));
	$cv_sql = "";
	
	if (empty($info))
	{
		echo error_message("الرجاء إدخال معلومة واحدة على الأقل.");
		return;
	}
	
	if ($user["group"] == "visitor")
	{
		$cv_sql = "cv = concat(cv, '$info')";
	}
	else
	{
		$cv_sql = "cv = '$info'";
	}
	
	// Everything is alright.
	$update_cv_query = mysql_query("UPDATE member SET $cv_sql WHERE id = '$member[id]'");
			
	// Awesome.
	echo success_message(
				"تم إضافة المعلومة بنجاح.",
			"edit_cv.php?id=$member[id]"
	);
}
else
{
	$cv_info = ($user["group"] == "user") ? "" : $member["cv"];

	// Get the content.
	$content = template(
		"views/edit_cv.html",
		array(
			"id" => $member["id"],
			"fullname" => $member["fullname"],
			"cv" => parse_string_or_list($member["cv"]),
			"cv_info" => $cv_info,
		)
	);
	
	// Get the header.
	$header = website_header(
"تعديل السيرة الذاتيّة - $member[fullname]",
"صفحة من أجل تعديل السيرة الذاتيّة - $member[fullname]",
			array(
	"عائلة", "الزغيبي", "السيرة", "الذاتية"
			)
	);
	
	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
