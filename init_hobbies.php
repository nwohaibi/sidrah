<?php

require_once("inc/functions.inc.php");

$hobbies = array(
	array("القراءة", 98),
	array("الطبح", 70),
	array("الحساب", 35),
	array("التصميم", 44),
	array("الإدارة", 20),
	array("الكوميديا", 10),
	array("الشعر", 5),
	array("التجميع", 34),
	array("الزراعة", 28),
	array("الكتابة", 86),
	array("التاريخ", 77),
	array("كرة القدم", 20),
	array("الصيد", 10),
	array("الأحاجي", 5),
);

foreach ($hobbies as $hobby)
{
	$name = $hobby[0];
	$rank = $hobby[1];

	// Check if the hobby already exists.
	$get_hobby_query = mysql_query("SELECT id FROM hobby WHERE name = '$name'");
	
	if (mysql_num_rows($get_hobby_query) == 0)
	{
		$insert_hobby = mysql_query("INSERT INTO hobby (name, rank) VALUES ('$name', '$rank')");
	}
}
