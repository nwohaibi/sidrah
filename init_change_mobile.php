<?php

require_once("inc/functions.inc.php");

$user = user_information();

$id = mysql_real_escape_string(@$_GET["id"]);
$submit = mysql_real_escape_string(@$_POST["submit"]);
$mobile = mysql_real_escape_string(arabic_number(@$_POST["mobile"]));

// Check if the member does exist.
$member = get_member_id($id);

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

if (!empty($submit))
{
	$update_mobile_query = mysql_query("UPDATE member SET mobile = '$mobile' WHERE id = '$member[id]'");
	
	echo success_message(
			"تم تحديث الجوّال بنجاح.",
			"familytree.php?id=$member[id]"
	);
}
