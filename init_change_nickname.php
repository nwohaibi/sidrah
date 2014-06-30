<?php

require_once("inc/functions.inc.php");

$user = user_information();

$id = mysql_real_escape_string(@$_GET["id"]);
$submit = mysql_real_escape_string(@$_POST["submit"]);
$nickname = mysql_real_escape_string(@$_POST["nickname"]);

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
	if ($nickname == $member["nickname"])
	{
		echo error_message("لم يتم تغيير اللقب.");
		return;
	}

	// Start to change the nickname of the member.
	$update_nickname_query = mysql_query("UPDATE member SET nickname = '$nickname' WHERE id = '$member[id]'");

	echo success_message(
		"تم تحديث اللقب بنجاح.",
		"familytree.php?id=$member[id]"
	);
}
