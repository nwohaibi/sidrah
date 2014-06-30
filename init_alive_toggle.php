<?php

require_once("inc/functions.inc.php");

$user = user_information();
$id = mysql_real_escape_string(@$_GET["id"]);

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

$is_alive_toggle = ($member["is_alive"] == 1) ? 0 : 1;
$update_is_alive_query = mysql_query("UPDATE member SET is_alive = '$is_alive_toggle' WHERE id = '$member[id]'");
	
echo success_message(
		"تم تحديث النبض بنجاح.",
		"familytree.php?id=$member[id]"
);
