<?php

require_once("inc/functions.inc.php");

$user = user_information();
$member_id = mysql_real_escape_string(@$_GET["member_id"]);

// Check if the member does exist.
$member = get_member_id($member_id);

if ($user["group"] != "admin" && $user["group"] != "moderator")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;	
}

if ($member == false)
{
	echo error_message("لا يمكن العثور على العضو المطلوب.");
	return;
}

// Check if the mobile is not enterd.
if ($member["mobile"] == "0")
{
	echo error_message("الرجاء إدخال رقم الجوّال الخاص بالعضو.");
	return;
}

// Update receiving messages for the user.
$update_receive_message_query = mysql_query("UPDATE user SET sms_received = '0' WHERE member_id = '$member[id]'");

// Then, create the user accordingly.
create_user($member["id"], $member["name"]);

echo success_message(
		"تم إرسال معلومات الدخول بنجاح.",
		"familytree.php?id=$member[id]"
);
