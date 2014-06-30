<?php

require_once("inc/functions.inc.php");

$iban = "SA0380000000000000000000";
$get_suggested_subsribers_query = mysql_query("SELECT * FROM member WHERE is_alive = 1 AND age >= 21");

while ($suggested = mysql_fetch_array($get_suggested_subsribers_query))
{
	$now = time();
	$insert_subscriber_query = mysql_query("INSERT INTO box_subscriber (member_id, created) VALUES ('$suggested[id]', '$now')");
	$subscriber_id = mysql_insert_id();
	$insert_account_query = mysql_query("INSERT INTO box_account (subscriber_id, iban, created) VALUES ('$subscriber_id', '$iban', '$now')");
}
