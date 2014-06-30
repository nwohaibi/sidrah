<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] != "admin" && $user["group"] != "moderator")
{
	redirect_to_login();
	return;
}

// Set the fullname if any.
if ($user["group"] == "admin")
{
	$related_fullname = "";
}
else
{
	// Get the related fullname.
	$user_info = get_user_id($user["id"]);
	$assigned_root_info = get_member_id($user_info["assigned_root_id"]);

	if ($assigned_root_info)
	{
		$related_fullname = $assigned_root_info["fullname"];
	}
}

// Get the content.
$get_inactive_users_query = mysql_query("SELECT user.id AS user_id, member.id AS member_id, member.gender AS gender, member.fullname AS fullname, member.mobile AS mobile, member.is_alive AS is_alive, (SELECT COUNT(id) FROM request WHERE affected_id = member.id AND status = 'pending') AS pending_requests FROM user, member WHERE user.member_id = member.id AND user.first_login = '1' AND member.mobile != '0' AND member.fullname LIKE '%$related_fullname' ORDER BY mobile DESC");
$inactive_users_count = mysql_num_rows($get_inactive_users_query);

if ($inactive_users_count == 0)
{
	$inactive_users = "<tr><td colspan='4'><i class='icon-exclamation-sign'></i> لا يوجد أعضاء غير فاعلين.</td></tr>";
}
else
{
	$inactive_users = "";

	while ($inactive_user = mysql_fetch_array($get_inactive_users_query))
	{
		if ($inactive_user["gender"] == 1)
		{
			$is_alive = rep_is_alive_male($inactive_user["is_alive"]);
			$img = "male.png";
		}
		else
		{
			$is_alive = rep_is_alive_female($inactive_user["is_alive"]);
			$img = "female.png";
		}
		
		$inactive_users .= "<tr><td class='hide-for-medium-down'><img src='views/img/$img' border='0' /></td><td><a href='familytree.php?id=$inactive_user[member_id]' target='_blank'>$inactive_user[fullname]</a></td><td class='hide-for-medium-down'>$is_alive</td><td class='hide-for-medium-down'>$inactive_user[mobile]</td><td class='hide-for-medium-down'>$inactive_user[pending_requests]</td><td><a class='small button' href='init_send_user_info.php?member_id=$inactive_user[member_id]' title='إرسال معلومات الدخول من خلال إرسال رسالة SMS'>إرسال</a></td></tr>";
	}
}

$content = template(
	"views/inactive_users.html",
	array(
		"inactive_users" => $inactive_users
	)
);

// Get the header.
$header = website_header(
	"أعضاء غير فاعلين ($inactive_users_count)",
	"صفحة من أجل عرض الأعضاء غير الفاعلين.",
	array(
		"عائلة", "الزغيبي", "أعضاء", "غير", "فاعلين"
	)
);

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;

