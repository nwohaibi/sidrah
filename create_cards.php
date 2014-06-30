<?php

set_time_limit(0);

require_once("inc/functions.inc.php");

// Set some background colors.
$background_colors = array(
	array(220, 20, 60), array(139, 95, 101), array(72, 94, 205), array(205, 145, 158),
	array(139, 99, 108), array(139, 71, 137), array(186, 85, 211), array(72, 94, 205),
	array(148, 0, 211), array(171, 130, 255), array(137, 104, 205), array(132, 112, 255),
	array(61, 89, 171), array(65, 105, 225), array(72, 118, 255), array(106, 90, 205), 
	array(58, 95, 205), array(100, 149, 237), array(162, 181, 205), array(110, 123, 139),
	array(30, 144, 255), array(24, 116, 205), array(70, 130, 180), array(92, 172, 238), array(0, 104, 139),
	array(0, 128, 128), array(3, 168, 158), array(128, 138, 135), array(0, 199, 140), array(139, 131, 120),
	array(69, 139, 116), array(60, 179, 113), array(67, 205, 128), array(131, 139, 131),
	array(48, 128, 20, ), array(205, 173, 0), array(218, 165, 32), array(48, 128, 20), array(142, 56, 142),
	array(72, 94, 205), array(240, 128, 128), array(142, 56, 142), array(240, 128, 128),
	array(205, 173, 0), array(218, 165, 32), array(72, 94, 205), array(105, 89, 205),
	array(220, 20, 60), array(139, 95, 101), array(72, 94, 205), array(205, 145, 158),
	array(139, 99, 108), array(139, 71, 137), array(186, 85, 211), array(72, 94, 205),
	array(148, 0, 211), array(171, 130, 255), array(137, 104, 205), array(132, 112, 255),
	array(61, 89, 171), array(65, 105, 225), array(72, 118, 255), array(106, 90, 205), 
	array(58, 95, 205), array(100, 149, 237), array(162, 181, 205), array(110, 123, 139),
	array(30, 144, 255), array(24, 116, 205), array(70, 130, 180), array(92, 172, 238), array(0, 104, 139),
	array(0, 128, 128), array(3, 168, 158), array(128, 138, 135), array(0, 199, 140), array(139, 131, 120),
	array(69, 139, 116), array(60, 179, 113), array(67, 205, 128), array(131, 139, 131),
	array(48, 128, 20, ), array(205, 173, 0), array(218, 165, 32), array(48, 128, 20), array(142, 56, 142),
	array(72, 94, 205), array(240, 128, 128), array(142, 56, 142), array(240, 128, 128),
	array(205, 173, 0), array(218, 165, 32), array(72, 94, 205), array(105, 89, 205),
);
//
$location_backgrounds = array();

// Get the locations from the database.
$get_locations_query = mysql_query("SELECT location FROM member GROUP BY location");

if (mysql_num_rows($get_locations_query) > 0)
{
	while ($location = mysql_fetch_array($get_locations_query))
	{
		$rand_key = array_rand($background_colors);
		$location_backgrounds[$location["location"]] = $background_colors[$rand_key];
		unset($background_colors[$rand_key]);
	}
}

//create_member_card($member_id, array(72, 94, 205));
$get_members_query = mysql_query("SELECT member.* FROM member, user WHERE member.id = user.member_id AND member.is_alive = 1 AND member.gender = 1 AND member.mobile != 0");

if (mysql_num_rows($get_members_query) > 0)
{
	while ($member = mysql_fetch_array($get_members_query))
	{
		$background_color_rgb = $location_backgrounds[$member["location"]];
		create_member_card($member["id"], $background_color_rgb);
		echo "$member[id]<br />";
		flush();
	}
}

// GZip all files.
exec("tar cvzf cards.tar.gz views/cards");

