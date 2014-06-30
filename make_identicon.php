<?php

require_once("inc/functions.inc.php");

$get_users_query = mysql_query("SELECT member_id FROM user");

if (mysql_num_rows($get_users_query) > 0)
{
	while ($user = mysql_fetch_array($get_users_query))
	{
		identicon($user["member_id"]);
		echo "$user[member_id]<br />";
		flush();
	}
}
