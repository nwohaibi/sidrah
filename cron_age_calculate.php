<?php

// Runs every day (every 24 hours).
// Calculate the age of members in the database.

require_once("inc/functions.inc.php");

$hijri_today = greg_to_hijri(date("d"), date("m"), date("Y"));
$hijri_today_int = hijri_to_int($hijri_today["d"], $hijri_today["m"], $hijri_today["y"]);

// Get dead members not aged yet.
$get_dead_members = mysql_query("SELECT id, dob, dod FROM member WHERE dod != '0000-00-00' AND is_alive = '0' AND age = '0'");

if (mysql_num_rows($get_dead_members) > 0)
{	
	while ($dead_member = mysql_fetch_array($get_dead_members))
	{
		$dob_array = explode("-", $dead_member["dob"]);
		$dod_array = explode("-", $dead_member["dod"]);
		
		$hijri_birth_year = $dob_array[0];
		$hijri_birth_month = $dob_array[1];
		$hijri_birth_day = $dob_array[2];

		$hijri_death_year = $dod_array[0];
		$hijri_death_month = $dod_array[1];
		$hijri_death_day = $dod_array[2];
		
		$hijri_birth_int = hijri_to_int($hijri_birth_day, $hijri_birth_month, $hijri_birth_year);
		$hijri_death_int = hijri_to_int($hijri_death_day, $hijri_death_month, $hijri_death_year);

		$hijri_diff = hijri_diff($hijri_death_int, $hijri_birth_int);
		$age = $hijri_diff["years"];
		
		if ($age >= 0)
		{
			echo "$dead_member[id] => $age\n";
			update_member_age($dead_member["id"], $age);
		}
	}
}

// Get alive members to update their ages (everyday they are growing).
$get_alive_members = mysql_query("SELECT id, dob, dod FROM member WHERE dob != '0000-00-00' AND is_alive = '1'");

if (mysql_num_rows($get_alive_members) > 0)
{
	while ($alive_member = mysql_fetch_array($get_alive_members))
	{
		$dob_array = explode("-", $alive_member["dob"]);
		
		$hijri_past_year = $dob_array[0];
		$hijri_past_month = $dob_array[1];
		$hijri_past_day = $dob_array[2];
		
		$hijri_past_int = hijri_to_int($hijri_past_day, $hijri_past_month, $hijri_past_year);
		$hijri_diff = hijri_diff($hijri_today_int, $hijri_past_int);
		$age = $hijri_diff["years"];
		
		if ($age >= 0)
		{
			echo "$alive_member[id] => $age\n";
			update_member_age($alive_member["id"], $age);
		}
	}
}
