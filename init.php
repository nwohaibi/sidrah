<?php

require_once("inc/functions.inc.php");

// Set the database table.
$tables = array(
	"company",
	"feedback",
	"hobby",
	"married",
	"member",
	"member_hobby",
	"notification",
	"request",
	"tribe",
	"user"
);

// Start dropping tables.
foreach ($tables as $table)
{
	$drop_table = mysql_query("DROP TABLE $table");
}

// Create tables.
$contents = file_get_contents("init_sidrah.sql");

// Remove comments.
$contents = preg_replace("/--(.*)\n/", '', $contents);
//$contents = preg_replace('/\/\*(.*)\*\//', '', $contents);

// Replace manylines with one line.
$contents = preg_replace("/\n+/", "\n", $contents);

// Get the SQL queries.
$sql_queries = explode(";", $contents);

// Start executing each SQL statement.
foreach ($sql_queries as $sql_query)
{
	$sql_query = trim($sql_query);
	$execute_sql_query = mysql_query($sql_query);
}

// Redirect to init_members.
//header("location: init_members.php");

?>
<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="refresh" content="0; url=init_members.php" />
</head>
<body>
	<h2>1/4</h2>
	<b>Tables have been created,</b><br />
	Hold on a second...
</body>
</html>
