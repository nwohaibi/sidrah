<?php

require_once("inc/functions.inc.php");

$now = time();
$date = date("Y-m-d");
$day = date("d");

// Get behaviors in this day.
$string = "SELECT * FROM box_behavior bxb WHERE ('$date' BETWEEN from_date AND to_date) AND dayofmonth = '$day' AND (SELECT id FROM box_transaction WHERE account_id = bxb.account_id AND triggered_by = 'schedule' AND (FROM_UNIXTIME(created, '%Y-%m-%d') = '$date' OR FROM_UNIXTIME(executed_at, '%Y-%m-%d') = '$date')) IS NULL";
$get_behavior_query = mysql_query($string)or die(mysql_error());

if (mysql_num_rows($get_behavior_query) > 0)
{	
	while ($behavior = mysql_fetch_array($get_behavior_query))
	{
		// Add the transaction.
		add_transaction($behavior["account_id"], $behavior["amount"], "deposit", $behavior["for_id"], $behavior["details"], "schedule");
	}
}
