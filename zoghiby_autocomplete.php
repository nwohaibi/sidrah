<?php

/**
 * In Allah We Trust.
 *
 * @author:	Hussam Al-sidrah.
 * @date:	9 Jul 2012.
 */

require_once("inc/functions.inc.php");

// Get user information.
$user = user_information();

// Get variables.
$name = mysql_real_escape_string(@$_GET["name"]);
$type = mysql_real_escape_string(@$_GET["type"]);
$suggested = trim(@$_GET["suggested"]);
$unique_id = mysql_real_escape_string(@$_GET["unique_id"]);
$return = mysql_real_escape_string(@$_GET["return"]);

// Type.
// mother, father, wife, husband.

$firstname = "";
$error = false;
$exact_array = array();
$almost_exact_array = array();
$childof_array = array();
$suggested_array = array();
$normalized_name = normalize_name($name);

if (!empty($suggested))
{
	$suggested_array = json_decode($suggested, true);
}

if (empty($normalized_name))
{
	echo "<div class='error'><i class='icon-question-sign'></i> الرجاء تعبئة الحقل.</div>";
	$error = true;
}
else
{
	$main_tribe_id = main_tribe_id;
	$names = explode(" ", $normalized_name);
	$firstname = $names[0];

	// If it was for family dean.
	if ($type == "dean-auto-complete")
	{
		$href = "#autocomplete-names-$unique_id";
		$search_for = escape_confusing("^" . implode(" ", $names) . ".*$");
		$get_related_names_query = mysql_query("SELECT id, fullname, photo, age, location, job_title FROM member WHERE fullname REGEXP '$search_for' AND tribe_id = '$main_tribe_id' AND is_alive = 1 AND gender = '1' AND age >= 45 ORDER BY fullname ASC LIMIT 8");
		
		if (mysql_num_rows($get_related_names_query) > 0)
		{
			echo "<ul class='ul_result'>";
			
			while ($result = mysql_fetch_array($get_related_names_query))
			{	
				$photo = rep_photo($result["photo"], 1, "avatar");
				$detailed_info = "$result[age] سنة - $result[location] - $result[job_title]";
				
				echo "<li class='li_result'><a title='$result[fullname]' href='$href' data-id='$result[id]' class='result'>$photo $result[fullname]<div class='dean_detailed'>$detailed_info</div><div class='clear'></div></a></li>";
			}
			
			echo "</ul>";
		}
		else
		{
			echo "<div class='error'><i class='icon-exclamation-sign'></i> لا يوجد نتائج.</div>";
		}
		
		return;
	}
	
	// If it was for box account.
	if ($type == "accounts-auto-complete")
	{
		$href = "#autocomplete-names-$unique_id";
		$search_for = escape_confusing("^" . implode(" ", $names) . ".*$");
		$get_related_names_query = mysql_query("SELECT box_account.id AS id, box_account.iban AS iban, member.fullname AS fullname FROM box_account, member, box_subscriber WHERE box_account.subscriber_id = box_subscriber.id AND box_subscriber.member_id = member.id AND member.tribe_id = '$main_tribe_id' AND member.is_alive = 1 AND member.gender = '1' AND (fullname REGEXP '$search_for' OR iban REGEXP '$search_for') ORDER BY fullname ASC LIMIT 8")or die();
		
		if (mysql_num_rows($get_related_names_query) > 0)
		{
			echo "<ul class='ul_result'>";
			
			while ($result = mysql_fetch_array($get_related_names_query))
			{	
				$detailed_info = "$result[iban]";
				
				echo "<li class='li_result'><a title='$result[fullname]' href='$href' data-id='$result[id]' class='result'>$result[fullname]<div>$detailed_info</div><div class='clear'></div></a></li>";
			}
			
			echo "</ul>";
		}
		else
		{
			echo "<div class='error'><i class='icon-exclamation-sign'></i> لا يوجد نتائج.</div>";
		}
		
		return;
	}

	// If it was just autocomplete.
	if ($type == "auto-complete")
	{
		$condition = "";
	
		if ($user["group"] == "visitor" || $user["group"] == "user")
		{
			$main_tribe_id = main_tribe_id;
			$condition = "AND gender = '1' AND tribe_id = '$main_tribe_id'";
		}

		$href = "#autocomplete-names-$unique_id";
		$search_for = escape_confusing("^" . implode(" ", $names) . ".*$");
		$get_related_names_query = mysql_query("SELECT id, fullname FROM member WHERE fullname REGEXP '$search_for' $condition ORDER BY fullname ASC LIMIT 8");
		
		if (mysql_num_rows($get_related_names_query) > 0)
		{
			echo "<ul class='ul_result'>";
			
			while ($result = mysql_fetch_array($get_related_names_query))
			{
				echo "<li class='li_result'><a title='$result[fullname]' href='$href' data-id='$result[id]' class='result'>$result[fullname]</a></li>";
			}
			
			echo "</ul>";
		}
		else
		{
			echo "<div class='error'><i class='icon-exclamation-sign'></i> لا يوجد نتائج.</div>";
		}
		
		return;
	}

	if (count($names) < 4)
	{
		echo "<div class='error'><i class='icon-question-sign'></i> الرجاء إدخال 4 أسماء على الأقل.</div>";
		$error = true;
	}
	else
	{
		$gender = null;

		switch ($type)
		{
			case "mother": case "wife":
				$gender = 0;
			break;
	
			case "father": case "husband":
				$gender = 1;
			break;
		}
		
		// Search exact.
		$exact_name = escape_confusing("^" . $normalized_name . "$");
		$get_exact_query = mysql_query("SELECT fullname FROM member WHERE fullname REGEXP '$exact_name' AND gender = '$gender'");
		
		if (mysql_num_rows($get_exact_query) > 0)
		{
			while ($ex = mysql_fetch_array($get_exact_query))
			{
				$exact_array []= $ex["fullname"];
			}
		}
		
		// Search almost exact.
		$family_name = $names[count($names)-1];
		unset($names[count($names)-1]);
		
		$almost_exact_name = escape_confusing("^" . implode(" ", $names) . ".*" . $family_name . "$");
		$get_almost_exact_query = mysql_query("SELECT id, fullname FROM member WHERE fullname REGEXP '$almost_exact_name' AND gender = '$gender'");

		if (mysql_num_rows($get_almost_exact_query) > 0)
		{
			while ($almost_ex = mysql_fetch_array($get_almost_exact_query))
			{
				if (!in_array($almost_ex["fullname"], $exact_array))
				{
					$almost_exact_array []= $almost_ex["fullname"];
				}
			}
		}
		
		// Search child of.
		unset($names[0]);
		
		$father_name = escape_confusing("^" . implode(" ", $names) . ".*" . $family_name . "$");
		$get_childof_query = mysql_query("SELECT fullname FROM member WHERE fullname REGEXP '$father_name' AND gender = '1'");
		
		if (mysql_num_rows($get_childof_query) > 0)
		{
			while ($childof = mysql_fetch_array($get_childof_query))
			{
				$iname = "$firstname $childof[fullname]";
				
				if (!in_array($iname, $exact_array) && !in_array($iname, $almost_exact_array))
				{
					$childof_array []= $childof["fullname"];
				}
			}
		}
	}
}

$href = "#autocomplete-names-$unique_id";

if (count($exact_array) > 0)
{
	echo "<div class='results_group'>اسم مطابق تماماً</div><ul class='ul_result'>";
	
	for($i=0; $i<count($exact_array); $i++)
	{
		echo "<li class='li_result'><a title='$exact_array[$i]' href='$href' class='result'>$exact_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if (count($almost_exact_array) > 0)
{
	echo "<div class='results_group'>أسماء مطابقة إلى حدٍ كبير</div><ul class='ul_result'>";
	
	for($i=0; $i<count($almost_exact_array); $i++)
	{
		echo "<li class='li_result'><a title='$almost_exact_array[$i]' href='$href' class='result'>$almost_exact_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if (count($childof_array) > 0)
{
	echo "<div class='results_group'>إضافة <b>$firstname</b> إلى</div><ul class='ul_result'>";
	
	for($i=0; $i<count($childof_array); $i++)
	{
		echo "<li class='li_result'><a title='$firstname $childof_array[$i]' href='$href' class='result'>$childof_array[$i]</a></li>";
	}
	
	echo "</ul>";
}

if ((count($exact_array) == 0) && (count($almost_exact_array) == 0) && (count($childof_array) == 0) && $error == false)
{
	echo "<div class='results_group'>إضافة اسم جديد</div>";
	echo "<ul class='ul_result'><li class='li_result'><a title='$normalized_name' href='$href' class='result'>$normalized_name</a></li></ul>";
}

if (count($suggested_array) > 0)
{
	switch ($type)
	{
		case "mother": case "wife":
			$wives_husbands = "زوجات الأب";
		break;
			case "father": case "husband":
			$wives_husbands = "أزواج الأم";
		break;
	}

	echo "<div class='results_group'>أسماء مقترحة ($wives_husbands)</div><ul class='ul_result'>";
	
	for($i=0; $i<count($suggested_array); $i++)
	{
		$name = $suggested_array[$i]["name"];
		echo "<li class='li_result'><a title='$name' href='$href' class='result'>$name</a></li>";
	}
	
	echo "</ul>";
}

