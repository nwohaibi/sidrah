<?php

// UPDATE_PROFILE_MALE.PHP
// Update male profile.
//
// Author:	Hussam Al-Zoghiby.
// Date:	12 Jul 2012.

require_once("inc/functions.inc.php");

// --------------------------------------------------
// CONFIGURATIONS
// --------------------------------------------------
$cfg_max_names = 4;

// --------------------------------------------------
// HTTP VARIABLES
// --------------------------------------------------
$submit = mysql_real_escape_string(@$_POST["submit"]);

$page = mysql_real_escape_string(@$_GET["page"]);
$id = mysql_real_escape_string(@$_GET["id"]);
$qkey = mysql_real_escape_string(trim(@$_GET["qkey"]));

$user = user_information();
$member_id = null;
$affected_id = null;

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

if (empty($id))
{
	echo error_message("لم يتم العثور على العضو المطلوب.");
	return;
}

$member = get_member_id($id, "gender = '1'");

if ($member == false)
{
	echo error_message("لم يتم العثور على العضو المطلوب.");
	return;
}

$affected_id = $member["id"];

// Check if the user is able to update member's profile.
// Check if the user is admin
$is_admin = ($user["group"] == "admin");

// Check if the user is seeing his/her profile.
$is_me = ($member["id"] == $user["member_id"]);

// Check if the moderator is accepted (if any).
$is_accepted_moderator = is_accepted_moderator($member["id"]);

// Check if the user is relative to the member.
$is_relative_user = is_relative_user($member["id"]);

if (!$is_admin && !$is_me && !$is_accepted_moderator && !$is_relative_user)
{
	echo error_message("تم رفض الوصول إلى هذه الصفحة.");
	return;
}

// Set created by.
$created_by = $user["id"];

// Get father information.
$father = get_member_id($member["father_id"]);

if ($member["fullname"] == "")
{
	// Update the fullname.
	update_fullname($member["id"]);
	
	// Get the member information again.
	$get_member_query = mysql_query("SELECT * FROM member WHERE id = '$member[id]'");
	$member = mysql_fetch_array($get_member_query);
}

if (!empty($submit))
{
	// --------------------------------------------------
	// Get is alive information.
	// --------------------------------------------------
	$is_alive = mysql_real_escape_string(trim(@$_POST["is_alive"]));

	// --------------------------------------------------
	// Get mother information.
	// --------------------------------------------------
	$mother_name = mysql_real_escape_string(trim(@$_POST["mother_name"]));
	$mom_marital_status = mysql_real_escape_string(trim(@$_POST["mom_marital_status"]));
	$mom_is_alive = mysql_real_escape_string(trim(@$_POST["mom_is_alive"]));

	// --------------------------------------------------
	// Get contact information.
	// --------------------------------------------------
	$mobile = mysql_real_escape_string(trim(arabic_number(@$_POST["mobile"])));
	$location = mysql_real_escape_string(trim(@$_POST["location"]));

	// --------------------------------------------------
	// Get historical information.
	// --------------------------------------------------
	$dob_d = lz(mysql_real_escape_string(trim(@$_POST["dob_d"])));
	$dob_m = lz(mysql_real_escape_string(trim(@$_POST["dob_m"])));
	$dob_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["dob_y"]))), 4);
	
	$dod_d = lz(mysql_real_escape_string(trim(@$_POST["dod_d"])));
	$dod_m = lz(mysql_real_escape_string(trim(@$_POST["dod_m"])));
	$dod_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["dod_y"]))), 4);

	$dob = "$dob_y-$dob_m-$dob_d";
	$dod = "$dod_y-$dod_m-$dod_d";

	$pob = mysql_real_escape_string(trim(@$_POST["pob"]));

	// --------------------------------------------------
	// Get educational information.
	// --------------------------------------------------
	$education = mysql_real_escape_string(trim(@$_POST["education"]));
	$major = mysql_real_escape_string(trim(@$_POST["major"]));
	
	// --------------------------------------------------
	// Get career information
	// --------------------------------------------------
	$company = mysql_real_escape_string(trim(@$_POST["company"]));
	$job_title = mysql_real_escape_string(trim(@$_POST["job_title"]));
	
	// --------------------------------------------------
	// Get marital status information.
	// --------------------------------------------------
	$marital_status = mysql_real_escape_string(trim(@$_POST["marital_status"]));

	// --------------------------------------------------
	// Get wives information.
	// --------------------------------------------------	
	$wife = @$_POST["wife"];
	$wife_marital_status = @$_POST["wife_marital_status"];
	$wife_is_alive = @$_POST["wife_is_alive"];
	
	// --------------------------------------------------
	// Get sons information.
	// --------------------------------------------------
	$son = @$_POST["son"];
	$son_mom = @$_POST["son_mom"];
	$son_is_alive = @$_POST["son_is_alive"];
	$son_mobile = @$_POST["son_mobile"];
	$son_dob_d = @$_POST["son_dob_d"];
	$son_dob_m = @$_POST["son_dob_m"];
	$son_dob_y = @$_POST["son_dob_y"];
	
	// --------------------------------------------------
	// Get daughters information.
	// --------------------------------------------------
	$daughter = @$_POST["daughter"];
	$daughter_mom = @$_POST["daughter_mom"];
	$daughter_is_alive = @$_POST["daughter_is_alive"];
	$daughter_mobile = @$_POST["daughter_mobile"];
	$daughter_dob_d = @$_POST["daughter_dob_d"];
	$daughter_dob_m = @$_POST["daughter_dob_m"];
	$daughter_dob_y = @$_POST["daughter_dob_y"];
	$daughter_marital_status = @$_POST["daughter_marital_status"];
	$daughter_husband_name = @$_POST["daughter_husband_name"];
	
	// --------------------------------------------------
	// SOME CHECKS	
	// --------------------------------------------------
	
	// Check if the mother name is missing.
	if (empty($mother_name))
	{
		echo error_message("الرجاء إدخال اسم الأم.");
		return;
	}
	
	if(empty($mobile) && $is_alive == 1)
	{
		echo error_message("الرجاء إدخال رقم الجوّال.");
		return;
	}
	
	// Check if the location is missing.
	if (empty($location))
	{
		echo error_message("الرجاء إدخال مكان الإقامة الحاليّ.");
		return;
	}
	
	// Check if the user did not specify the dates.
	if ((empty($dob_d) || intval($dob_d) == 0) || (empty($dob_m) || intval($dob_m) == 0) || (empty($dob_y) || intval($dob_y) == 0))
	{
		echo error_message("الرجاء إدخال تاريخ الميلاد.");
		return;
	}
	
	// Check if the place of the birth is missing.
	if (empty($pob))
	{
		echo error_message("الرجاء إدخال مكان الميلاد.");
		return;
	}

	$valid_sons = array();
	$valid_daughters = array();
	$valid_wives = array();
	
	// Start walking for sons.
	foreach ($son as $key => $value)
	{
		$son[$key] = mysql_real_escape_string(trim($son[$key]));
		$son[$key] = normalize_name($son[$key]);
		
		if (!empty($son[$key]))
		{
			$valid_sons[$key] = $son[$key];
		}
	}

	// Start walking for daughters.
	foreach ($daughter as $key => $value)
	{
		$daughter[$key] = mysql_real_escape_string(trim($daughter[$key]));
		$daughter[$key] = normalize_name($daughter[$key]);
		
		if ($daughter_marital_status[$key] != 1)
		{
			$daughter_husband_name[$key] =  mysql_real_escape_string(trim($daughter_husband_name[$key]));
			
			if (empty($daughter_husband_name[$key]))
			{
				echo error_message("الرجاء إدخال اسم زوج البنت.");
				return;
			}
		}
		
		if (!empty($daughter[$key]))
		{
			$valid_daughters[$key] = $daughter[$key];
		}
	}
	
	// Start walking up-on the wives.
	foreach ($wife as $key => $value)
	{
		$wife[$key] = mysql_real_escape_string(trim($wife[$key]));
		
		if (!empty($wife[$key]))
		{
			$valid_wives[$key] = $wife[$key];
		}
	}
	
	// Check if there are children but no wives.
	if ((count($valid_sons) + count($valid_daughters) > 0) && (count($valid_wives) == 0))
	{
		echo error_message("الرجاء إضافة زوجة واحدة على الأقل.");
		return;
	}
	
	// Set some variables.
	$title_array = array();
	$description_array = array();
	
	$title_array []= $member["fullname"];

	// --------------------------------------------------
	// MOTHER
	// --------------------------------------------------

	// Get the married status of mother and father.
	$enum_married = get_enum_married($father["is_alive"], $mom_is_alive, $father["marital_status"], $mom_marital_status);

	$php = "\$member_id = $id;\n";
	$php .= "\$mother_id = add_member('$mother_name', 0);\n";
	$php .= "\$enum_married = get_enum_married($father[is_alive], $mom_is_alive, $father[marital_status], $mom_marital_status);\n";
	$php .= "add_married($father[id], \$mother_id, \$enum_married);\n";
	$php .= "update_member_is_alive(\$mother_id, $mom_is_alive);\n\n";
	
	// Update mother id to the member.
	$php .= "mysql_query(\"UPDATE member SET mother_id = '\$mother_id' WHERE id = '$member[id]'\");\n\n";

	$description_array[] = "تحديث الأم ($mother_name)\n";

	// --------------------------------------------------
	// WVIES
	// --------------------------------------------------
	if (count($valid_wives) > 0)
	{
		foreach ($valid_wives as $wife_key => $wife_value)
		{	
			$php .= "\$wife[$wife_key] = add_member('$wife_value', 0);\n";
			$php .= "\$enum_married = get_enum_married($is_alive, $wife_is_alive[$wife_key], $marital_status, $wife_marital_status[$wife_key]);\n";
			$php .= "add_married($member[id], \$wife[$wife_key], \$enum_married);\n";
			$php .= "update_member_is_alive(\$wife[$wife_key], $wife_is_alive[$wife_key]);\n\n";
			
			$description_array[] = "إضافة أو تحديث الزوجة ($wife_value)\n";
		}
		
		$title_array []= sprintf("تحديث الزوجات (%d)", count($valid_wives));
	}
	
	// --------------------------------------------------
	// SONS
	// --------------------------------------------------
	if (count($valid_sons) > 0)
	{
		foreach ($valid_sons as $son_key => $son_value)
		{
			$son_dob_y[$son_key] = arabic_number($son_dob_y[$son_key]);
			$son_mobile[$son_key] = arabic_number($son_mobile[$son_key]);
			
			$son_dob = sprintf("%04d-%02d-%02d", $son_dob_y[$son_key], $son_dob_m[$son_key], $son_dob_d[$son_key]);

			$php .= "add_child('male', $member[tribe_id], '$son[$son_key]', $member[id], \$wife[$son_mom[$son_key]], 1, $son_is_alive[$son_key], '$son_mobile[$son_key]', '$son_dob', '$location');\n\n";
			$description_array[] = "إضافة أو تحديث الابن ($son_value)\n";
		}
		
		$title_array []= sprintf("تحديث الأبناء (%d)", count($valid_sons));
	}
	
	// --------------------------------------------------
	// DAUGHTERS
	// --------------------------------------------------
	if (count($valid_daughters) > 0)
	{
		foreach ($valid_daughters as $daughter_key => $daughter_value)
		{
			$daughter_dob_y[$daughter_key] = arabic_number($daughter_dob_y[$daughter_key]);
			$daughter_mobile[$daughter_key] = arabic_number($daughter_mobile[$daughter_key]);
			
			$daughter_dob = sprintf("%04d-%02d-%02d", $daughter_dob_y[$daughter_key], $daughter_dob_m[$daughter_key], $daughter_dob_d[$daughter_key]);
			
			$php .= "\$temp_daughter_id = add_child('male', $member[tribe_id], '$daughter[$daughter_key]', $member[id], \$wife[$daughter_mom[$daughter_key]], 0, $daughter_is_alive[$daughter_key], '$daughter_mobile[$daughter_key]', '$daughter_dob', '$location', '$daughter_marital_status[$daughter_key]', 0);\n";
			$description_array[] = "إضافة أو تحديث البنت ($daughter_value)\n";
			
			if ($daughter_marital_status[$daughter_key] != 1)
			{
				$d_husband_name = $daughter_husband_name[$daughter_key];

				$php .= "\$temp_daughter_husband_id = add_member('$d_husband_name', 1);\n";
				$php .= "\$temp_husband_info = get_member_id(\$temp_daughter_husband_id);\n";
				$php .= "\$temp_enum_married = get_enum_married(\$temp_husband_info['is_alive'], $daughter_is_alive[$daughter_key], \$temp_husband_info['marital_status'], $daughter_marital_status[$daughter_key]);\n";
				$php .= "add_married(\$temp_daughter_husband_id, \$temp_daughter_id, \$temp_enum_married);\n";
				$php .= "update_member_is_alive(\$temp_daughter_id, $daughter_is_alive[$daughter_key]);\n";
				
				$description_array[] = "إضافة أو تحديث زوج البنت ($daughter_value): $d_husband_name\n";
			}
			
			$php .= "\n";
		}
		
		$title_array []= sprintf("تحديث البنات (%d)", count($valid_daughters));
	}

	// --------------------------------------------------
	// REQUIRED FIEDLS
	// --------------------------------------------------
	$sql_required = array();

	// Update is alive, the date of birth, and the required information.
	if ($member["is_alive"] != $is_alive)
	{
		$sql_required []= "is_alive = '$is_alive'";
		$title_array []= sprintf("تحديث النبض إلى (%s)", rep_is_alive_male($is_alive));
	}

	if ($member["mobile"] != $mobile)
	{
		$sql_required []= "mobile = '$mobile'";
		$description_array []= "تحديث الجوّال إلى ($mobile)\n";
	}
	
	if ($member["location"] != $location)
	{
		$sql_required []= "location = '$location'";
		$description_array []= "تحديث مكان الإقامة الحاليّ إلى ($location)\n";
	}
	
	if ($member["dob"] != $dob)
	{
		$sql_required []= "dob = '$dob'";
		$description_array []= "تحديث تاريخ الميلاد إلى ($dob)\n";
	}
	
	if ($member["pob"] != $pob)
	{
		$sql_required []= "pob = '$pob'";
		$description_array []= "تحديث مكان الميلاد إلى ($pob)\n";
	}

	if ($member["dod"] != $dod)
	{
		$sql_required []= "dod = '$dod'";
		$description_array []= "تحديث تاريخ الوفاة إلى ($dod)\n";
	}

	if ($education != $member["education"])
	{
		$sql_required []= "education = '$education'";
		$description_array []= sprintf("تحديث التعليم إلى (%s)\n", rep_education($education));
	}

	if ($major != $member["major"])
	{
		$sql_required []= "major = '$major'";
		$description_array []= "تحديث التخصّص إلى ($major)\n";
	}
		
	$cid = get_company_id($company);
	
	if ($cid != $member["company_id"])
	{
		$sql_required []= "company_id = '$cid'";
		$description_array []= "تحديث جهة العمل إلى ($company)\n";
	}
	
	if ($job_title != $member["job_title"])
	{
		$sql_required []= "job_title = '$job_title'";
		$description_array []= "تحديث المسمّى الوظيفي إلى ($job_title)\n";
	}

	if ($member["marital_status"] != $marital_status)
	{
		$sql_required []= "marital_status = '$marital_status'";
		$title_array []= "تحديث حالة إجتماعية";
	}

	if (count($sql_required) > 0)
	{
		$sql_required_string = implode(", ", $sql_required);
		$php .= "\$required_updates = \"$sql_required_string\";\n";
	}
	
	// --------------------------------------------------
	// UPDATE REQUIRED FIELDS
	// --------------------------------------------------
	$php .= "\nif (isset(\$required_updates)){\n";
	$php .= "mysql_query(\"UPDATE member SET \$required_updates WHERE id = '$member[id]'\");\n";
	$php .= "}\n";

	// --------------------------------------------------
	// REQUEST TO BE EXECUTED
	// --------------------------------------------------
	
	// Check if the key does exist.
	$get_request_query = mysql_query("SELECT * FROM request WHERE affected_id = '$id' AND random_key = '$qkey'");

	// Set the assigned_to value.
	$assign_request = assign_request($member["fullname"]);
	$assigned_to = ($assign_request) ? $assign_request : "";
	
	// Generate random key for security purposes.
	$random_key = generate_key(6, true, true, true);
	$php = addslashes($php);
	
	// Insert a new request
	$now = time();
	$title = implode(", ", $title_array);
	$description = implode("\n", $description_array);

	if (mysql_num_rows($get_request_query) == 0)
	{
		$insert_request = mysql_query("INSERT INTO request (random_key, title, description, phpscript, affected_id, created_by, assigned_to, created) VALUES ('$random_key', '$title', '$description', '$php', '$affected_id', '$created_by', '$assigned_to', '$now')");
	}
	else
	{
		$request_info = mysql_fetch_array($get_request_query);
		$random_key = $request_info["random_key"];
		$update_request = mysql_query("UPDATE request SET title = '$title', description = '$description', phpscript = '$php', created = '$now' WHERE random_key = '$random_key'");
	}

	// Check if the user is admin
	$is_admin = ($user["group"] == "admin");
	
	// Check if the moderator is accepted (if any).
	$is_accepted_moderator = is_accepted_moderator($member["id"]);
	
	// Check if the user is an admin, or accepted moderator.
	if ($is_admin || $is_accepted_moderator)
	{
		execute_request($random_key, $user["id"]);
	}

	// Update first time value.
	if ($member["id"] == $user["member_id"] && $user["first_login"] == 1)
	{
		$update_first_login_query = mysql_query("UPDATE user SET first_login = '0' WHERE id = '$user[id]'");
		$redirect = "update_optional.php?id=$affected_id";
	}
	else
	{
		$redirect ="familytree.php?id=$affected_id";
	}
	
	if ($user["group"] == "user")
	{
		$ok_message = "تم استلام طلبك، و ستتم معالجته و إبلاغك خلال مدّة لا تتجاوز 24 ساعة.";
		notify("request_receive", $user["id"], $ok_message, "update_profile_male.php?id=$affected_id");
	}
	else
	{
		$ok_message = "تم حفظ التحديثات بنجاح.";
	}
	
	echo success_message(
		$ok_message,
		$redirect
	);
}
else
{
	// Check if the key does exist.
	$get_request_query = mysql_query("SELECT * FROM request WHERE affected_id = '$id' AND random_key = '$qkey'");
	
	// --------------------------------------------------
	// COMMON
	// --------------------------------------------------
	$suggested_mothers = get_mothers($member["id"], "list");
	
	$wife_index = -1;
	$son_index = -1;
	$daughter_index = -1;
	
	if (mysql_num_rows($get_request_query) > 0)
	{
		$request = mysql_fetch_array($get_request_query);
		$phpscript = $request["phpscript"];
		$description = $request["description"];
		
		// --------------------------------------------------
		// BASIC INFORMATION
		// --------------------------------------------------
		$already_wives = array();
		
		// Search inside the script for required updates.
		preg_match('/\$required_updates = "(.*)"/isU', $phpscript, $required_updates_array);
		$required_updates = $required_updates_array[1];
		
		// Mobile.
		preg_match("/mobile = '(.*)'/isU", $required_updates, $mobile_array);
		$mobile = (count($mobile_array) > 0) ? $mobile_array[1] : $member["mobile"];
		
		// Location.
		preg_match("/location = '(.*)'/isU", $required_updates, $location_array);
		$location = (count($location_array) > 0) ? $location_array[1] : $member["location"];
		
		// Place of birth.
		preg_match("/pob = '(.*)'/isU", $required_updates, $pob_array);
		$pob = (count($pob_array) > 0) ? $pob_array[1] : $member["pob"];
		
		// Education.
		preg_match("/education = '(.*)'/isU", $required_updates, $education_array);
		$education = (count($education_array) > 0) ? $education_array[1] : $member["education"];
		
		// Major.
		preg_match("/major = '(.*)'/isU", $required_updates, $major_array);
		$major = (count($major_array) > 0) ? $major_array[1] : $member["major"];
		
		// Job title.
		preg_match("/job_title = '(.*)'/isU", $required_updates, $job_title_array);
		$job_title = (count($job_title_array) > 0) ? $job_title_array[1] : $member["job_title"];
		
		// Is alive.
		preg_match("/is_alive = '(.*)'/isU", $required_updates, $is_alive_array);
		$is_alive = (count($is_alive_array) > 0) ? $is_alive_array[1] : $member["is_alive"];
		
		// Marital status.
		preg_match("/marital_status = '(.*)'/isU", $required_updates, $marital_status_array);
		$marital_status = (count($marital_status_array) > 0) ? $marital_status_array[1] : $member["marital_status"];
		
		// Get CID (Company ID).
		preg_match("/company_id = '(.*)'/isU", $required_updates, $cid_array);
		$cid = (count($cid_array) > 0) ? $cid_array[1] : $member["company_id"];
		$company_name = get_company_name($cid);
		
		// Get DOB (Date of Birth).
		preg_match("/dob = '(.*)'/isU", $required_updates, $dob_array);
		$dob = (count($dob_array) > 0) ? $dob_array[1] : $member["dob"];
		list($dob_y, $dob_m, $dob_d) = sscanf($dob, "%d-%d-%d");
		
		// Get DOD (Date of Death).
		preg_match("/dod = '(.*)'/isU", $required_updates, $dod_array);
		$dod = (count($dod_array) > 0) ? $dod_array[1] : $member["dod"];
		list($dod_y, $dod_m, $dod_d) = sscanf($dod, "%d-%d-%d");
		
		// --------------------------------------------------
		// JS ON LOAD
		// --------------------------------------------------
		$js_on_load = "";
	
		// Update marital status.
		$js_on_load .= sprintf("update_marital_status(%d); ", $marital_status);
		$js_on_load .= sprintf("update_is_alive(%d); ", $is_alive);
		
		$wives_as_array = get_wives($member["id"]);
		$only_wives_names = array();
		
		if (count($wives_as_array) > 0)
		{
			foreach ($wives_as_array as $i => $v)
			{
				$only_wives_names[$v["id"]] = $v["fullname"];
			}
		}
		
		// --------------------------------------------------
		// MOTHER
		// --------------------------------------------------
		
		// Search inside the script for mother name.
		preg_match('/\$mother_id = add_member\(\'(.*)\', 0\);\\n\$enum_married = get_enum_married\((\d), (\d), (\d), (\d)\);/isU', $phpscript, $mother_info);
		
		// Get mother information.
		if(count($mother_info) > 0)
		{
			$mother_name = $mother_info[1];
			$mother_is_alive = $mother_info[3];
			$mother_marital_status = $mother_info[5];
			
			$js_on_load .= "update_mother_ms($mother_marital_status); ";
			$js_on_load .= "update_mother_is_alive($mother_is_alive); ";
		}
		else
		{
			$mother_name = "";
		}
		
		// --------------------------------------------------
		// WIVES
		// --------------------------------------------------
		$wives_html = "";

		// Search for the wives inside the script.
		preg_match_all('/\$wife\[(-?\d{1,8})\] = add_member\(\'(.*)\', 0\);\\n\$enum_married = get_enum_married\((\d), (\d), (\d), (\d)\);/isU', $phpscript, $wives_array);
		
		$wives_ids = $wives_array[1];
		$wives_names = $wives_array[2];
		$wives_is_alives = $wives_array[4];
		$wives_marital_statuses = $wives_array[6];

		if (count($wives_ids) > 0)
		{	
			$wives_html = "<h5 class='subheader'>تحديث الزوجات (" . count($wives_ids) . ")</h5><div id='update_wives' class='row'><div class='large-12 columns'>";

			foreach ($wives_ids as $key => $id)
			{
				$wife_name = $wives_names[$key];
			
				// Check if the wife does exist in the already wives, then remove it.
				$index = array_search($wife_name, $only_wives_names);
				
				if ($index !== false)
				{
					unset($only_wives_names[$index]);
				}
			
				$wives_html .= "<div class='row'>";
				$wives_html .= "<div class='large-8 columns'><input class='zoghiby-wife-autocomplete' type='text' name='wife[$id]' size='30' placeholder='[اسم الزوجة] [الأب] [الجد] [العائلة]' value='$wife_name' /></div>";
				$wives_html .= "<div class='large-2 small-6 columns'><select id='wife_marital_status_$id' name='wife_marital_status[$id]'><option value='2'>زوجة</option><option value='3'>طليقة</option></select></div>";
				$wives_html .= "<div class='large-2 small-6 columns'><select id='wife_is_alive_$id' name='wife_is_alive[$id]'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></div>";
				$wives_html .= "</div>";
				
				$js_on_load .= sprintf("update_wife_marital_status(%d, %d); ", $id, $wives_marital_statuses[$key]);
				$js_on_load .= sprintf("update_wife_is_alive(%d, %d); ", $id, $wives_is_alives[$key]);
				
				$wife_index--;
			}

			$wives_html .= "</div></div>";
		}
		
		// Start to set already wives.
		$_temp = array();
		
		if (count($only_wives_names) > 0)
		{
			foreach ($only_wives_names as $k => $v)
			{
				$_temp []= "{id: $k, name: '$v'}";
			}
		}
		
		$already_wives = implode(", ", $_temp);
		
		// --------------------------------------------------
		// SONS
		// --------------------------------------------------
		$sons_html = "";
		
		// Search for sons inside the script.
		preg_match_all('/add_child\((\d{1,8}), \'(.*)\', (\d{1,8}), \$wife\[(-?\d{1,8})\], \d, (\d), \'(.*)\', \'(\d{4})\-(\d{2})\-(\d{2})\'\);/isU', $phpscript, $sons_array);
	
		$sons_names = $sons_array[2];
		$sons_mother_ids = $sons_array[4];
		$sons_is_alives = $sons_array[5];
		$sons_mobiles = $sons_array[6];
		$sons_dob_ys = $sons_array[7];
		$sons_dob_ms = $sons_array[8];
		$sons_dob_ds = $sons_array[9];
		
		if (count($sons_names) > 0)
		{
			$updated_sons_count = count($sons_names);
		
			$sons_html = "<h4><i class='icon-refresh'></i> تحديث الأبناء ($updated_sons_count)</h4><div id='update_sons'><table class='onechild'>";
			$sons_html .= "<thead><tr><th>الاسم</th><th>الأم</th><th>النبض</th><th>الجوّال</th><th>تاريخ الميلاد</th></tr></thead><tbdoy>";

			foreach ($sons_names as $k => $son_name)
			{
				$son_is_alive = $sons_is_alives[$k];
				$son_mobile = $sons_mobiles[$k];
				
				$son_dob_y = $sons_dob_ys[$k];
				$son_dob_m = (int)$sons_dob_ms[$k];
				$son_dob_d = (int)$sons_dob_ds[$k];
				
				$son_mother_id = $sons_mother_ids[$k];
			
				$sons_html .= "<tr><td class='childname'><input type='text' placeholder='[الاسم الأول]' name='son[$son_index]' class='childname childnamein' value='$son_name'></td><td><select class='moms' name='son_mom[$son_index]' id='son_mom_id_$son_index'></select></td>";
				$sons_html .= "<td><select name='son_is_alive[$son_index]' id='son_is_alive_$son_index'><option value='1'>حيّ يرزق</option><option value='0'>متوفّى</option></select></td>";
				$sons_html .= "<td><input type='text' placeholder='رقم الجوّال' name='son_mobile[$son_index]' value='$son_mobile' size='8' /></td>";
				$sons_html .= "<td><select name='son_dob_d[$son_index]' id='son_dob_d_$son_index'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select>";
				$sons_html .= " <select name='son_dob_m[$son_index]' id='son_dob_m_$son_index'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select>";
				$sons_html .= " <input type='text' placeholder='0000' name='son_dob_y[$son_index]' size='2' value='$son_dob_y'/></td></tr>\n";
				
				// JS
				$js_on_load .= sprintf("update_son_mom_id(%d, %d); ", $son_index, $son_mother_id);
				$js_on_load .= sprintf("update_son_is_alive(%d, %d); ", $son_index, $son_is_alive);
				$js_on_load .= sprintf("update_son_dob_d(%d, %d); ", $son_index, $son_dob_d);
				$js_on_load .= sprintf("update_son_dob_m(%d, %d); ", $son_index, $son_dob_m);
				
				$son_index--;
			}

			$sons_html .= "</tbody></table></div>";
		}
		
		// --------------------------------------------------
		// DAUGHTERS
		// --------------------------------------------------
		$daughters_html = "";
		$daughters = array();
		
		// Married one.
		preg_match_all('/\\$temp_daughter_id = add_child\((\d{1,8}), \'(.*)\', (\d{1,8}), \$wife\[(-?\d{1,8})\], \d, (\d), \'(.*)\', \'(\d{4})\-(\d{2})\-(\d{2})\', \'(\d)\', \d\);\\n\\$temp_daughter_husband_id = add_member\(\'(.*)\', \d\);/isU', $phpscript, $married_daughters_array);
		
		// Start to walk up-on the daughters.
		$married_daughters_names = $married_daughters_array[2];
		
		if (count($married_daughters_names) > 0)
		{
			$married_daughters_mother_ids = $married_daughters_array[4];
			$married_daughters_is_alives = $married_daughters_array[5];
			$married_daughters_mobiles = $married_daughters_array[6];
			
			$married_daughters_dob_ys = $married_daughters_array[7];
			$married_daughters_dob_ms = $married_daughters_array[8];
			$married_daughters_dob_ds = $married_daughters_array[9];
			
			$married_daughters_marital_status = $married_daughters_array[10];
			$married_daughters_husband_names = $married_daughters_array[11];
		
			foreach ($married_daughters_names as $key => $daughter_name)
			{
				$daughters []= array(
					"name" => $daughter_name,
					"mother_id" => $married_daughters_mother_ids[$key],
					"is_alive" => $married_daughters_is_alives[$key],
					"mobile" => $married_daughters_mobiles[$key],
					"dob_y" => $married_daughters_dob_ys[$key],
					"dob_m" => (int) $married_daughters_dob_ms[$key],
					"dob_d" => (int) $married_daughters_dob_ds[$key],
					"marital_status" => $married_daughters_marital_status[$key],
					"husband_name" => $married_daughters_husband_names[$key]
				);
				
				// Remove the found daughter.
				$phpscript = str_replace($married_daughters_array[0][$key], "", $phpscript); 
			}
		}
		
		// Non married one.
		preg_match_all('/\\$temp_daughter_id = add_child\((\d{1,8}), \'(.*)\', (\d{1,8}), \$wife\[(-?\d{1,8})\], \d, (\d), \'(.*)\', \'(\d{4})\-(\d{2})\-(\d{2})\', \'(\d)\', \d\);/isU', $phpscript, $non_married_daughters_array);
		
		// Start to walk up-on the daughters.
		$non_married_daughters_names = $non_married_daughters_array[2];
		
		if (count($non_married_daughters_names) > 0)
		{
			$non_married_daughters_mother_ids = $non_married_daughters_array[4];
			$non_married_daughters_is_alives = $non_married_daughters_array[5];
			$non_married_daughters_mobiles = $non_married_daughters_array[6];
			
			$non_married_daughters_dob_ys = $non_married_daughters_array[7];
			$non_married_daughters_dob_ms = $non_married_daughters_array[8];
			$non_married_daughters_dob_ds = $non_married_daughters_array[9];
			
			$non_married_daughters_marital_status = $non_married_daughters_array[10];
		
			foreach ($non_married_daughters_names as $key => $daughter_name)
			{
				$daughters []= array(
					"name" => $daughter_name,
					"mother_id" => $non_married_daughters_mother_ids[$key],
					"is_alive" => $non_married_daughters_is_alives[$key],
					"mobile" => $non_married_daughters_mobiles[$key],
					"dob_y" => $non_married_daughters_dob_ys[$key],
					"dob_m" => (int) $non_married_daughters_dob_ms[$key],
					"dob_d" => (int) $non_married_daughters_dob_ds[$key],
					"marital_status" => $non_married_daughters_marital_status[$key],
					"husband_name" => ""
				);
			}
		}
		
		if (count($daughters) > 0)
		{
			$updated_daughters_count = count($daughters);
	
			$daughters_html = "<h4><i class='icon-refresh'></i> تحديث البنات ($updated_daughters_count)</h4><div id='updated_daughters'><table class='onechild'>";
			$daughters_html .= "<thead><tr><th>الاسم</th><th>الأم</th><th>النبض</th><th>الجوّال</th><th>تاريخ الميلاد</th><th>الحالة الإجتماعية</th><th>الزوج (إن وجد)</th></tr></thead><tbdoy>";
	
			foreach ($daughters as $daughter)
			{
				$daughters_html .= "<tr><td class='childname'><input class='childname childnamein' type='text' name='daughter[$daughter_index]' placeholder='[الاسم الأول]' value='$daughter[name]' /></td><td><select class='moms' name='daughter_mom[$daughter_index]' id='daughter_mom_id_$daughter_index'></select></td>";
				$daughters_html .= "<td><select name='daughter_is_alive[$daughter_index]' id='daughter_is_alive_$daughter_index'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></td><td><input type='text' placeholder='رقم الجوّال' value='$daughter[mobile]' name='daughter_mobile[$daughter_index]' size='8' /></td>";
				$daughters_html .= "<td><select name='daughter_dob_d[$daughter_index]' id='daughter_dob_d_$daughter_index'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select>";
				$daughters_html .= " <select name='daughter_dob_m[$daughter_index]' id='daughter_dob_m_$daughter_index'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select>";
				$daughters_html .= " <input type='text' placeholder='0000' name='daughter_dob_y[$daughter_index]' size='2' value='$daughter[dob_y]'/></td>";
				$daughters_html .= "<td><select id='dm_$daughter_index' name='daughter_marital_status[$daughter_index]' onchange='daughter_husband_toggle(this.name)' class='daughter_ms_select'><option value='1'>عزباء</option><option value='2'>متزوجة</option><option value='3'>طليقة</option><option value='4'>أرملة</option></select></td>";
				$daughters_html .= "<td><input type='text' class='zoghiby-daughter-husband-autocomplete' id='daughter_husband_$daughter_index' name='daughter_husband_name[$daughter_index]' placeholder='اسم الزوج (رباعي)' value='$daughter[husband_name]' /></td></tr>";
				
				// JS
				$js_on_load .= sprintf("update_daughter_mom_id(%d, %d); ", $daughter_index, $daughter["mother_id"]);
				$js_on_load .= sprintf("update_daughter_is_alive(%d, %d); ", $daughter_index, $daughter["is_alive"]);
				$js_on_load .= sprintf("update_daughter_marital_status(%d, %d); ", $daughter_index, $daughter["marital_status"]);
				$js_on_load .= sprintf("daughter_husband_toggle('daughter_marital_status[%d]'); ", $daughter_index);
				$js_on_load .= sprintf("update_daughter_dob_d(%d, %d); ", $daughter_index, $daughter["dob_d"]);
				$js_on_load .= sprintf("update_daughter_dob_m(%d, %d); ", $daughter_index, $daughter["dob_m"]);
				
				$daughter_index--;
			}
		
			$daughters_html .= "</tbody></table></div>";
		}
	}
	else
	{
		// --------------------------------------------------
		// BASIC INFORMATION
		// --------------------------------------------------
		$mobile = $member["mobile"];
		$location = $member["location"];
		$pob = $member["pob"];
		$education = $member["education"];
		$major = $member["major"];
		$job_title = $member["job_title"];
	
		// --------------------------------------------------
		// MOTHER
		// --------------------------------------------------
		$mothers = get_mothers($member["id"]);
		$mother_info = get_member_id($member["mother_id"]);
		$mother_name = ($mother_info) ? $mother_info["fullname"] : "";

		// --------------------------------------------------
		// ALREADY WIVIES
		// --------------------------------------------------
		$wives = get_wives($member["id"]);
		$already_wives = get_wives($member["id"], "hash");

		// --------------------------------------------------
		// SONS
		// --------------------------------------------------
		$sons = get_sons($member["id"]);

		// --------------------------------------------------
		// DAUGHTERS
		// --------------------------------------------------
		$daughters = get_daughters($member["id"]);

		// --------------------------------------------------
		// COMPANY NAME
		// --------------------------------------------------
		$company_name = get_company_name($member["company_id"]);

		// --------------------------------------------------
		// JS ON LOAD, MOTHERS OPTION	
		// --------------------------------------------------
		$js_on_load = "";
	
		// Update marital status.
		$js_on_load .= sprintf("update_marital_status(%d); ", $member["marital_status"]);
		$js_on_load .= sprintf("update_is_alive(%d); ", $member["is_alive"]);

		// If there is any son.
		if (count($sons) > 0)
		{
			foreach ($sons as $son)
			{
				list($dob_y, $dob_m, $dob_d) = sscanf($son["dob"], "%d-%d-%d");
			
				$js_on_load .= sprintf("update_son_mom_id(%d, %d); ", $son["id"], $son["mother_id"]);
				$js_on_load .= sprintf("update_son_is_alive(%d, %d); ", $son["id"], $son["is_alive"]);
				$js_on_load .= sprintf("update_son_dob_d(%d, %d); ", $son["id"], $dob_d);
				$js_on_load .= sprintf("update_son_dob_m(%d, %d); ", $son["id"], $dob_m);
			}
		}
	
		// If there is any daughter.
		if (count($daughters) > 0)
		{
			foreach ($daughters as $daughter)
			{
				list($dob_y, $dob_m, $dob_d) = sscanf($daughter["dob"], "%d-%d-%d");
		
				$js_on_load .= sprintf("update_daughter_mom_id(%d, %d); ", $daughter["id"], $daughter["mother_id"]);
				$js_on_load .= sprintf("update_daughter_is_alive(%d, %d); ", $daughter["id"], $daughter["is_alive"]);
				$js_on_load .= sprintf("update_daughter_marital_status(%d, %d); ", $daughter["id"], $daughter["marital_status"]);
				$js_on_load .= sprintf("daughter_husband_toggle('daughter_marital_status[%d]'); ", $daughter["id"]);
				$js_on_load .= sprintf("update_daughter_dob_d(%d, %d); ", $daughter["id"], $dob_d);
				$js_on_load .= sprintf("update_daughter_dob_m(%d, %d); ", $daughter["id"], $dob_m);
			}
		}
	
		// If there is any wife.
		if (count($wives) > 0)
		{
			foreach ($wives as $wife)
			{
				$js_on_load .= sprintf("update_wife_marital_status(%d, %d); ", $wife["id"], $wife["ms_int"]);
				$js_on_load .= sprintf("update_wife_is_alive(%d, %d); ", $wife["id"], $wife["is_alive"]);
			}
		}
	
		// If there is any mother.
		if (count($mothers) > 0)
		{
			foreach ($mothers as $mother)
			{
				if ($mother["id"] == $member["mother_id"])
				{
					$js_on_load .= "update_mother_ms($mother[ms_int]); ";
					$js_on_load .= "update_mother_is_alive($mother[is_alive]); ";
				}
			}
		}
	
		// --------------------------------------------------
		// WIVES
		// --------------------------------------------------
		$wives_html = "";

		if (count($wives) > 0)
		{
			$wives_html = "<h5 class='subheader'>تحديث الزوجات (" . count($wives) . ")</h5><div id='update_wives' class='row'><div class='large-12 columns'>";

			foreach ($wives as $wife)
			{
				$wives_html .= "<div class='row'>";
				$wives_html .= "<div class='large-8 columns'><input class='wife' type='hidden' name='wife[$wife[id]]' value='$wife[fullname]' /><label class='partnername'><a href='update_profile_female.php?id=$wife[id]'>$wife[fullname]</a></label></div>";
				$wives_html .= "<div class='large-2 small-6 columns'><select id='wife_marital_status_$wife[id]' name='wife_marital_status[$wife[id]]'><option value='2'>زوجة</option><option value='3'>طليقة</option></select></div>";
				$wives_html .= "<div class='large-2 small-6 columns'><select id='wife_is_alive_$wife[id]' name='wife_is_alive[$wife[id]]'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></div>";
				$wives_html .= "</div>";
			}

			$wives_html .= "</div></div>";
		}

		// --------------------------------------------------
		// SONS
		// --------------------------------------------------
		$sons_html = "";
	
		if (count($sons) > 0)
		{
			$updated_sons_count = count($sons);
		
			$sons_html = "<h5 class='subheader'>تحديث الأبناء ($updated_sons_count)</h5><div id='update_sons' class='row'><div class='large-12 columns'>";

			foreach ($sons as $son)
			{
				list($dob_y, $dob_m, $dob_d) = sscanf($son["dob"], "%d-%d-%d");
				$dob_y = lz($dob_y, 4);

				if ($dob_y == "0000")
				{
					$dob_y = "";
				}
			
				if ($son["mobile"] == 0)
				{
					$son["mobile"] = "";
				}

				$sons_html .= "<label>الابن</label><div class='row'>";
				$sons_html .= "<div class='large-4 small-4 columns'><input class='childname childnamein' type='hidden' name='son[$son[id]]' value='$son[name]' /><a href='update_profile_male.php?id=$son[id]'>$son[name]</a></div>";
				$sons_html .= "<div class='large-2 small-2 columns'><select name='son_is_alive[$son[id]]' id='son_is_alive_$son[id]'><option value='1'>حيّ يرزق</option><option value='0'>متوفّى</option></select></div>";
				$sons_html .= "<div class='large-6 small-6 columns'><select class='moms' name='son_mom[$son[id]]' id='son_mom_id_$son[id]'></select></div></div>";
				$sons_html .= "<div class='row'><div class='large-6 small-6 columns'><input type='text' placeholder='رقم الجوّال' name='son_mobile[$son[id]]' value='$son[mobile]' size='8' /></div>";
				$sons_html .= "<div class='large-2 small-2 columns'><select name='son_dob_d[$son[id]]' id='son_dob_d_$son[id]'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select></div>";
				$sons_html .= "<div class='large-2 small-2 columns'><select name='son_dob_m[$son[id]]' id='son_dob_m_$son[id]'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select></div>";
				$sons_html .= "<div class='large-2 small-2 columns'><input type='text' placeholder='0000' name='son_dob_y[$son[id]]' size='2' value='$dob_y'/></div>\n";
				$sons_html .= "</div>";
			}

			$sons_html .= "</div></div>";
		}
	
		// --------------------------------------------------
		// DAUGHTERS
		// --------------------------------------------------
		$daughters_html = "";
	
		if (count($daughters) > 0)
		{
			$updated_daughters_count = count($daughters);
	
			$daughters_html = "<h5 class='subheader'>تحديث البنات ($updated_daughters_count)</h5><div id='updated_daughters' class='row'><div class='large-12 columns'>";
	
			foreach ($daughters as $daughter)
			{
				list($dob_y, $dob_m, $dob_d) = sscanf($daughter["dob"], "%d-%d-%d");
				$dob_y = lz($dob_y, 4);
			
				if ($dob_y == "0000")
				{
					$dob_y = "";
				}
			
				if ($daughter["mobile"] == 0)
				{
					$daughter["mobile"] = "";
				}
		
				$daughters_html .= "<label>البنت</label><div class='row'>";
				$daughters_html .= "<div class='large-2 small-2 columns'><input class='childname childnamein' type='hidden' name='daughter[$daughter[id]]' value='$daughter[name]' /><a href='update_profile_female.php?id=$daughter[id]'>$daughter[name]</a></div>";
				$daughters_html .= "<div class='large-2 small-2 columns'><select name='daughter_is_alive[$daughter[id]]' id='daughter_is_alive_$daughter[id]'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></div>";
				$daughters_html .= "<div class='large-1 small-1 columns'><select name='daughter_dob_d[$daughter[id]]' id='daughter_dob_d_$daughter[id]'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select></div>";
				$daughters_html .= "<div class='large-1 small-1 columns'><select name='daughter_dob_m[$daughter[id]]' id='daughter_dob_m_$daughter[id]'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select></div>";
				$daughters_html .= "<div class='large-1 small-1 columns'><input type='text' placeholder='0000' name='daughter_dob_y[$daughter[id]]' size='2' value='$dob_y'/></div>";
				$daughters_html .= "<div class='large-5 small-5 columns'><select class='moms' name='daughter_mom[$daughter[id]]' id='daughter_mom_id_$daughter[id]'></select></div></div>";
				$daughters_html .= "<div class='row'><div class='large-4 small-4 columns'><input type='text' placeholder='رقم الجوّال' value='$daughter[mobile]' name='daughter_mobile[$daughter[id]]' size='8' /></div>";
				$daughters_html .= "<div class='large-3 small-3 columns'><select id='dm_$daughter[id]' name='daughter_marital_status[$daughter[id]]' onchange='daughter_husband_toggle(this.name)' class='daughter_ms_select'><option value='1'>عزباء</option><option value='2'>متزوجة</option><option value='3'>طليقة</option><option value='4'>أرملة</option></select></div>";
				$daughters_html .= "<div class='large-5 small-5 columns'><input type='text' class='zoghiby-daughter-husband-autocomplete' id='daughter_husband_$daughter[id]' name='daughter_husband_name[$daughter[id]]' placeholder='اسم الزوج (رباعي)' value='$daughter[husband_name]' /></div>";
				$daughters_html .= "</div>";
			}
			
			//
		
			$daughters_html .= "</div></div>";
		}

		// --------------------------------------------------
		// DATE OF BIRTH
		// --------------------------------------------------
		$date_of_birth = sscanf($member["dob"], "%d-%d-%d");
	
		$dob_y = lz($date_of_birth[0], 4);
		$dob_m = $date_of_birth[1];
		$dob_d = $date_of_birth[2];

		if ($dob_y == "0000")
		{
			$dob_y = "";
		}

		// --------------------------------------------------
		// DATE OF DEATH
		// --------------------------------------------------
		$date_of_death = sscanf($member["dod"], "%d-%d-%d");
	
		$dod_y = lz($date_of_death[0], 4);
		$dod_m = $date_of_death[1];
		$dod_d = $date_of_death[2];
	
		if ($dod_y == "0000")
		{
			$dod_y = "";
		}
	}

	// Get the header
	$header = website_header(
		"تحديث المعلومات الأساسية للعضو $member[fullname]",
		"صفحة من أجل تحديث المعلومات الأساسية للعضو $member[fullname]",
		array(
			"عائلة", "الزغيبي", "شجرة", "تحديث", "معلومات", "العضو"
		)
	);

	// Get the footer.
	$footer = website_footer();

	// --------------------------------------------------
	// TEMPLATE
	// --------------------------------------------------
	$template = template(
		"views/update_member_male.html", 
		array(
			"js_on_load" => $js_on_load,
			"fullname" => $member["fullname"], "mother_name" => $mother_name,
			"member_id" => $member["id"], "suggested_mothers" => $suggested_mothers,
			"mobile" => $mobile, "location" => $location,
			"dob_y" => $dob_y, "dob_m" => $dob_m, "dob_d" => $dob_d,
			"dod_y" => $dod_y, "dod_m" => $dod_m, "dod_d" => $dod_d,
			"pob" => $pob, "education" => $education,
			"major" => $major, "company_name" => $company_name,
			"job_title" => $job_title, "already_wives" => $already_wives,
			"wives_html" => $wives_html, "sons_html" => $sons_html, "daughters_html" => $daughters_html,
			"wife_index" => $wife_index, "son_index" => $son_index, "daughter_index" => $daughter_index,
			"qkey" => $qkey
		)
	);
	
	echo $header;
	echo $template;
	echo $footer;
}

