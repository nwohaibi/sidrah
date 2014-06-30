<?php

require_once("inc/functions.inc.php");

// Get user information, and the given id.
$user = user_information();

$tribe_id = mysql_real_escape_string(@$_GET["tribe_id"]);
$id = mysql_real_escape_string(@$_GET["id"]);
$view = mysql_real_escape_string(@$_GET["view"]);

// Check if the tribe does exist.
$get_tribe_query = mysql_query("SELECT id, name FROM tribe WHERE id = '$tribe_id'");

if (mysql_num_rows($get_tribe_query) > 0)
{
	$tribe_info = mysql_fetch_array($get_tribe_query);
	$tribe_id = $tribe_info["id"];
}
else
{
	$tribe_id = main_tribe_id;
}

switch ($view)
{	
	default: case "spacetree":
		$view_template = "views/js/familytree_spacetree.js";
	break;
}

if ($user["group"] == "moderator")
{
	$user_info = get_user_id($user["id"]);
	$assigned_root_info = get_member_id($user_info["assigned_root_id"]);
	
	if ($assigned_root_info)
	{
		$related_fullname = $assigned_root_info["fullname"];
	}
}
else
{
	$related_fullname = "";
}

// Get familytree json.
//$familytree_json = get_member_children_json($tribe_id, -1, $user["group"], $related_fullname);

// Get member information.
$member = get_member_id($id);

if ($member == false)
{
	if ($tribe_id == main_tribe_id)
	{
		$id = 2;
	}
	else
	{
		// Get the root of this tribe.
		$get_tribe_root_query = mysql_query("SELECT id FROM member WHERE tribe_id = '$tribe_id' AND father_id = '-1' AND gender = '1'");
		$root_info = mysql_fetch_array($get_tribe_root_query);
		$id = $root_info["id"];
	}
}

// Get the header.
$header = website_header(
	"شجرة العائلة",
	"صفحة من أجل عرض شجرة العائلة.",
	array(
		"الزغيبي", "عائلة", "شجرة"
	)
);

// Get the content inside.
$content = template(
	"views/familytree.html",
	array(
		"id" => $id,
		"tribe_id" => $tribe_id
	)
);

// Get the footer.
$footer = website_footer();

echo $header;
echo $content;
echo $footer;

