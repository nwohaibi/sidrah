<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

$get_random_request_query = mysql_query("SELECT * FROM request ORDER BY RAND()");
$one_request = mysql_fetch_array($get_random_request_query);

// Get the description
$description = $one_request["description"];

// Line by line
$description = str_replace("\n\n", "\n", $description);
$lines = explode("\n", $description);

foreach ($lines as $line)
{
	preg_match("/(.*)\((.*)\)/", $line, $match);
	
	echo "<b>$match[1]</b> <input type='text' value='$match[2]' /><br />";
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf8" />
</head>
<body>
<?php echo $one_request["description"]; ?>
</body>
</html>
