<?php

require_once("inc/functions.inc.php");

$user = user_information();

// Check if the user is a visitor.
if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$member_id = $user["member_id"];

$submit = mysql_real_escape_string(@$_POST["submit"]);
$member = get_member_id($member_id);

if ($member == false)
{
	echo error_message("لم يتم العثور على صفحة العضو.");
	return;
}

if (!empty($submit))
{
	// Get variables.
	$username = $user["username"];
	$new_username = trim(mysql_real_escape_string(@$_POST["new_username"]));
	$name = $member["name"];
	
	if (check_user_availability($username, $new_username, $name) === false)
	{
		echo error_message("اسم المستخدم المُدخل غير مُتاح، الرجاء اختيار اسم مستخدم آخر.");
		return;
	}
	else
	{
		// Update the identicon.
		identicon($member["id"]);

		// Update the username.
		$update_username_query = mysql_query("UPDATE user SET username = '$new_username' WHERE id = '$user[id]'");
		
		// Logout after all,
		echo success_message(
			"تم تغيير اسم المستخدم بنجاح، قم بتسجيل الدخول مرة أخرى.",
			"logout.php"
		);
	}
}
else
{
	// Get the header.
	$header = website_header(
		"تغيير اسم المستخدم",
		"صفحة من أجل تغيير اسم المستخدم.",
		array(
			"الزغيبي", "عائلة", "شجرة", "تغيير", "اسم", "المستخدم"
		)
	);

	$content = template(
		"views/change_username.html",
		array(
			"username" => $user["username"],
			"name" => $member["name"],
			"user_min_length" => user_min_length,
			"user_max_length" => user_max_length
		)
	);
	
	// Get the footer.
	$footer = website_footer();

	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
