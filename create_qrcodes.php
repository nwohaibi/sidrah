<?php

set_time_limit(0);

require_once("inc/functions.inc.php");
require_once("classes/PHPQRCode.php");

$get_members_query = mysql_query("SELECT member.* FROM member, user WHERE member.id = user.member_id AND member.is_alive = 1 AND member.gender = 1 AND member.mobile != 0");

if (mysql_num_rows($get_members_query) > 0)
{
	while ($member = mysql_fetch_array($get_members_query))
	{
		$shorten_name = shorten_name($member["fullname"]);
		
		$information = "MECARD:N:$shorten_name;TEL:$member[mobile];EMAIL:$member[email];;";
		QRcode::png("$information", "views/qrcodes/$member[id].png", "H", 3, 2);
		
		echo "$member[id]<br />";
		flush();
	}
}

