<?php

require_once("inc/functions.inc.php");

$user = user_information();

$id = mysql_real_escape_string(@$_GET["id"]);
$submit = mysql_real_escape_string(@$_POST["submit"]);
$name = mysql_real_escape_string(normalize_name(@$_POST["name"]));

// Check if the member does exist.
$member = get_member_id($id);

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;	
}

if ($member == false)
{
	echo error_message("لا يمكن العثور على العضو المطلوب.");
	return;
}

if (!empty($submit))
{
	if (empty($name))
	{
		echo error_message("الرجاء إدخال الاسم الجديد.");
		return;
	}
	
	if ($name == $member["name"])
	{
		echo error_message("لم يتم تغيير الاسم.");
		return;
	}

	// Start to change the name of the member.
	$update_name_query = mysql_query("UPDATE member SET name = '$name' WHERE id = '$member[id]'");
	
	// Update the fullname after that.
	update_fullname($member["id"]);
	
	// Update the user if any.
	// Check if the user does exist.
	$get_user_query = mysql_query("SELECT id FROM user WHERE member_id = '$member[id]'");
	
	// Found?
	if (mysql_num_rows($get_user_query) > 0)
	{
		// Get the user information.
		$user_info = mysql_fetch_array($get_user_query);
	
		// Update the username too.
		$update_username_query = mysql_query("UPDATE user SET username = '$name$member[id]' WHERE id = '$user_info[id]'");
	}
	
	echo success_message(
		"تم تحديث الاسم بنجاح.",
		"familytree.php?id=$member[id]"
	);
}
