<?php

// UPDATE_PROFILE_MALE.PHP
// Update male profile.
//
// Author:	Hussam Al-sidrah.
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
$user = user_information();
$member_id = null;
$affected_id = null;

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
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
	
	if(empty($mobile))
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
			$son_dob = sprintf("%04d-%02d-%02d", $son_dob_y[$son_key], $son_dob_m[$son_key], $son_dob_d[$son_key]);

			$php .= "add_child($member[tribe_id], '$son[$son_key]', $member[id], \$wife[$son_mom[$son_key]], 1, $son_is_alive[$son_key], '$son_mobile[$son_key]', '$son_dob');\n\n";
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
			$daughter_dob = sprintf("%04d-%02d-%02d", $daughter_dob_y[$daughter_key], $daughter_dob_m[$daughter_key], $daughter_dob_d[$daughter_key]);
			
			$php .= "\$temp_daughter_id = add_child($member[tribe_id], '$daughter[$daughter_key]', $member[id], \$wife[$daughter_mom[$daughter_key]], 0, $daughter_is_alive[$daughter_key], '$daughter_mobile[$daughter_key]', '$daughter_dob', '$daughter_marital_status[$daughter_key]', 0);\n";
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

	$insert_request = mysql_query("INSERT INTO request (random_key, title, description, phpscript, affected_id, created_by, assigned_to, created) VALUES ('$random_key', '$title', '$description', '$php', '$affected_id', '$created_by', '$assigned_to', '$now')");

	// Check if the user is admin
	$is_admin = ($user["group"] == "admin");
	
	// Check if the moderator is accepted (if any).
	$is_accepted_moderator = is_accepted_moderator($member["id"]);
	
	// Check if the user is an admin, or accepted moderator.
	if ($is_admin || $is_accepted_moderator)
	{
		execute_request($random_key);
	}

	// Update first time value.
	if ($member["id"] == $user["member_id"] && $user["first_login"] == 1)
	{
		$update_first_login_query = mysql_query("UPDATE user SET first_login = '0' WHERE id = '$user[id]'");
		$redirect = "update_optional.php";
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
	// --------------------------------------------------
	// MOTHER
	// --------------------------------------------------
	$mothers = get_mothers($member["id"]);
	$suggested_mothers = get_mothers($member["id"], "list");
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
		$wives_html = "<h4><i class='icon-refresh'></i> تحديث الزوجات (" . count($wives) . ")</h4><div id='update_wives'>";

		foreach ($wives as $wife)
		{
			$wives_html .= "<p><input class='wife' type='hidden' name='wife[$wife[id]]' value='$wife[fullname]' /><label class='partnername'><a href='update_profile_female.php?id=$wife[id]'>$wife[fullname]</a></label>";
			$wives_html .= " <select id='wife_marital_status_$wife[id]' name='wife_marital_status[$wife[id]]'><option value='2'>زوجة</option><option value='3'>طليقة</option></select>";
			$wives_html .= " <select id='wife_is_alive_$wife[id]' name='wife_is_alive[$wife[id]]'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></p>";
		}

		$wives_html .= "</div>";
	}

	// --------------------------------------------------
	// SONS
	// --------------------------------------------------
	$sons_html = "";
	
	if (count($sons) > 0)
	{
		$updated_sons_count = count($sons);
		
		$sons_html = "<h4><i class='icon-refresh'></i> تحديث الأبناء ($updated_sons_count)</h4><div id='update_sons'><table class='onechild'>";
		$sons_html .= "<thead><tr><th>الاسم</th><th>الأم</th><th>النبض</th><th>الجوّال</th><th>تاريخ الميلاد</th></tr></thead><tbdoy>";

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

			$sons_html .= "<tr><td class='childname'><input class='childnamein' type='hidden' name='son[$son[id]]' value='$son[name]' /><a href='update_profile_male.php?id=$son[id]'>$son[name]</a></td><td><select class='moms' name='son_mom[$son[id]]' id='son_mom_id_$son[id]'></select></td>";
			$sons_html .= "<td><select name='son_is_alive[$son[id]]' id='son_is_alive_$son[id]'><option value='1'>حيّ يرزق</option><option value='0'>متوفّى</option></select></td>";
			$sons_html .= "<td><input type='text' placeholder='رقم الجوّال' name='son_mobile[$son[id]]' value='$son[mobile]' size='10' /></td>";
			$sons_html .= "<td><select name='son_dob_d[$son[id]]' id='son_dob_d_$son[id]'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select>";
			$sons_html .= " <select name='son_dob_m[$son[id]]' id='son_dob_m_$son[id]'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select>";
			$sons_html .= " <input type='text' placeholder='0000' name='son_dob_y[$son[id]]' size='4' value='$dob_y'/></td></tr>\n";
		}

		$sons_html .= "</tbody></table></div>";
	}
	
	// --------------------------------------------------
	// DAUGHTERS
	// --------------------------------------------------
	$daughters_html = "";
	
	if (count($daughters) > 0)
	{
		$updated_daughters_count = count($daughters);
	
		$daughters_html = "<h4><i class='icon-refresh'></i> تحديث البنات ($updated_daughters_count)</h4><div id='updated_daughters'><table class='onechild'>";
		$daughters_html .= "<thead><tr><th>الاسم</th><th>الأم</th><th>النبض</th><th>الجوّال</th><th>تاريخ الميلاد</th><th>الحالة الإجتماعية</th><th>الزوج (إن وجد)</th></tr></thead><tbdoy>";
	
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
		
			$daughters_html .= "<tr><td class='childname'><input class='childnamein' type='hidden' name='daughter[$daughter[id]]' value='$daughter[name]' /><a href='update_profile_female.php?id=$daughter[id]'>$daughter[name]</a></td><td><select class='moms' name='daughter_mom[$daughter[id]]' id='daughter_mom_id_$daughter[id]'></select></td>";
			$daughters_html .= "<td><select name='daughter_is_alive[$daughter[id]]' id='daughter_is_alive_$daughter[id]'><option value='1'>حيّة ترزق</option><option value='0'>متوفّاة</option></select></td><td><input type='text' placeholder='رقم الجوّال' value='$daughter[mobile]' name='daughter_mobile[$daughter[id]]' size='10' /></td>";
			$daughters_html .= "<td><select name='daughter_dob_d[$daughter[id]]' id='daughter_dob_d_$daughter[id]'><option value='0'></option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option><option value='11'>11</option><option value='12'>12</option><option value='13'>13</option><option value='14'>14</option><option value='15'>15</option><option value='16'>16</option><option value='17'>17</option><option value='18'>18</option><option value='19'>19</option><option value='20'>20</option><option value='21'>21</option><option value='22'>22</option><option value='23'>23</option><option value='24'>24</option><option value='25'>25</option><option value='26'>26</option><option value='27'>27</option><option value='28'>28</option><option value='29'>29</option><option value='30'>30</option></select>";
			$daughters_html .= " <select name='daughter_dob_m[$daughter[id]]' id='daughter_dob_m_$daughter[id]'><option value='0'></option><option value='1'>محرم</option><option value='2'>صفر</option><option value='3'>ربيع الأول</option><option value='4'>ربيع الثاني</option><option value='5'>جمادى الأولى</option><option value='6'>جمادى الثانية</option><option value='7'>رجب</option><option value='8'>شعبان</option><option value='9'>رمضان</option><option value='10'>شوال</option><option value='11'>ذو القعدة</option><option value='12'>ذو الحجة</option></select>";
			$daughters_html .= " <input type='text' placeholder='0000' name='daughter_dob_y[$daughter[id]]' size='4' value='$dob_y'/></td>";
			$daughters_html .= "<td><select id='dm_$daughter[id]' name='daughter_marital_status[$daughter[id]]' onchange='daughter_husband_toggle(this.name)'><option value='1'>عزباء</option><option value='2'>متزوجة</option><option value='3'>طليقة</option><option value='4'>أرملة</option></select></td>";
			$daughters_html .= "<td><input type='text' class='sidrah-daughter-husband-autocomplete' id='daughter_husband_$daughter[id]' name='daughter_husband_name[$daughter[id]]' placeholder='اسم الزوج (رباعي)' value='$daughter[husband_name]' /></td></tr>";
		}
		
		$daughters_html .= "</tbody></table></div>";
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
			"mobile" => $member["mobile"], "location" => $member["location"],
			"dob_y" => $dob_y, "dob_m" => $dob_m, "dob_d" => $dob_d,
			"dod_y" => $dod_y, "dod_m" => $dod_m, "dod_d" => $dod_d,
			"pob" => $member["pob"], "education" => $member["education"],
			"major" => $member["major"], "company_name" => $company_name,
			"job_title" => $member["job_title"], "already_wives" => $already_wives,
			"wives_html" => $wives_html, "sons_html" => $sons_html, "daughters_html" => $daughters_html
		)
	);
	
	echo $header;
	echo $template;
	echo $footer;
}

