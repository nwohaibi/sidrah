<?php

require_once("inc/functions.inc.php");

$action = @$_GET["action"];
$mobile = @$_GET["mobile"];
$id = @$_GET["id"];

switch ($action)
{
	case "fixbymobile":
		$reset_mobile_by_mobile_query = mysql_query("UPDATE member SET mobile = '0' WHERE mobile = '$mobile'");
	break;
	
	case "fixbyid":
		$reset_mobile_by_id_query = mysql_query("UPDATE member SET mobile = '0' WHERE id = '$id'");
	break;

	default:
}

$get_similar_mobiles_query = mysql_query("
SELECT member.id, member.mobile, member.fullname, member.gender, member.age, t.mobiles
FROM member, (
	SELECT mobile, count(id) AS mobiles
	FROM member
	WHERE mobile != '0'
	GROUP BY mobile
) t
WHERE t.mobile = member.mobile
AND t.mobiles > 1
ORDER BY member.mobile
");

$similar_mobiles_count = mysql_num_rows($get_similar_mobiles_query);

echo "<!DOCTYPE HTML>";
echo "<html lang='ar' dir='rtl'><head><meta charset='utf-8' /></head><body>";

$mobile = "";

while ($member = mysql_fetch_array($get_similar_mobiles_query))
{
	if ($member["mobile"] != $mobile)
	{
		$mobile = $member["mobile"];
		echo "<h1>$mobile</h1> <a href='fix_mobiles.php?action=fixbymobile&mobile=$mobile'>[ResetByMobile]</a><br />";
	}
	
	echo "$member[fullname], $member[age] <a href='fix_mobiles.php?action=fixbyid&id=$member[id]'>[ResetById]</a><br />";
}

echo "</body></html>";
