<?php

require_once("inc/functions.inc.php");

$get_male_members = mysql_query("SELECT id, father_id, name, nickname, mobile, is_alive FROM member WHERE gender = '1'");

while ($male_member = mysql_fetch_array($get_male_members))
{
	echo "array(\n\t'id' => '$male_member[id]',\n\t'father_id' => '$male_member[father_id]',\n\t'name' => '$male_member[name]',\n\t'nickname' => '$male_member[nickname]',\n\t'mobile' => '$male_member[mobile]',\n\t'is_alive' => '$male_member[is_alive]'\n),\n";
}

/*
$get_fullnames = mysql_query("SELECT COUNT(id) as c, fullname FROM member GROUP BY fullname ORDER BY c DESC");

while ($member = mysql_fetch_array($get_fullnames))
{
	echo $member["fullname"];
	echo " ";
	echo $member["c"];
	echo "<br />";
}
*/
