<?php

require_once("config.inc.php");

function database_connect()
{
	$database_link = @mysql_connect(database_server, database_username, database_password);
	$database_connect = @mysql_select_db(database_name, $database_link);
	
	return $database_connect;
}

function redirect($page)
{
	header("location: $page");
}

function redirect_to_login()
{
	$current_url = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	redirect("login.php?url=" . urlencode($current_url));
}

function alert_to_url($message, $type = "success")
{
	$status = array(
		"type" => $type,
		"message" => $message
	);
	
	return urlencode(base64_encode(serialize($status)));
}

function url_to_alert($url)
{
	$status = @unserialize(urldecode(base64_decode($url)));
	if ($status)
	{
		return alert($status["message"], $status["type"]);
	}
}

function alert($message, $type = "success")
{
	return "<div class=\"alert alert-$type\">$message</div>";
}

// public
// parent = [father|mother]
// usergroup = [visitor|user|moderator|admin]
function get_member_children_json($tribe_id = main_tribe_id, $father_id = -1, $usergroup = "visitor", $related_fullname = null)
{
	$conditions = array();
	
	if ($tribe_id != null)
	{
		$conditions []= "tribe_id = '$tribe_id'";
	}

	$conditions []= "father_id = '$father_id'";
	
	switch ($usergroup)
	{
		case "visitor": case "user":
			$conditions []= "gender = '1'";
		break;

		case "moderator":
			$conditions []= "(gender = '1' OR (gender = '0' AND fullname LIKE '%$related_fullname'))";
		break;
	}

	$condition = implode("AND ", $conditions);
	$get_children_query = mysql_query("SELECT id, father_id, name, nickname, dob, dod, gender FROM member WHERE $condition ORDER BY id");
	$return = "";
	
	if (mysql_num_rows($get_children_query) > 0)
	{
		while ($child = mysql_fetch_array($get_children_query))
		{

			if (empty($child["nickname"]))
			{
				$nickname = "";
			}
			else
			{
				$nickname = " (" . $child["nickname"] . ")";
			}
			
			$date = "";
			
			$display_dob = privacy_display($child["id"], "dob", $usergroup, false, false, ($usergroup == "moderator"));
			
			if (($display_dob || $child["dod"] != "0000-00-00") && $child["dob"] != "0000-00-00")
			{
				$dob_array = sscanf($child["dob"], "%d-%d-%d");
				$date = $dob_array[0];
			}
			
			if($child["dod"] != "0000-00-00")
			{
				$dod_array = sscanf($child["dod"], "%d-%d-%d");
				$date .= "-" . $dod_array[0];
			}
			
			//$parent = ($child["gender"] == 1) ? "father" : "mother";
			$return .= sprintf("{id:\t\"%s\",\nname:\t\"<div class='node'>%s%s<span class='node_datetime'>$date</span></div>\",\nchildren:[", $child["id"], $child["name"], $nickname);		
			
			if ($child["gender"] == 1)
			{
				$return .= get_member_children_json(null, $child["id"], $usergroup, $related_fullname);
			}

			$return .= "]},";
		}
	}
	
	return substr($return, 0, strlen($return)-1);	
}

// public
function get_sons($id, $of = "father")
{
	$get_sons_query = mysql_query("SELECT * FROM member WHERE gender = '1' AND {$of}_id = '$id'");
	
	if (mysql_num_rows($get_sons_query) == 0)
	{
		return array();
	}
	else
	{
		$sons = array();
		
		while ($son = mysql_fetch_array($get_sons_query))
		{
			$sons []= array(
				"id" => $son["id"], "name" => $son["name"], "mother_id" => $son["mother_id"], "father_id" => $son["father_id"],
				"is_alive" => $son["is_alive"], "mobile" => $son["mobile"], "dob" => $son["dob"], "tribe_id" => $son["tribe_id"]
			);
		}
		
		return $sons;
	}
}

// public
function get_daughters($id, $of = "father")
{
	$get_daughters_query = mysql_query("SELECT * FROM member WHERE gender = '0' AND {$of}_id = '$id'");
	
	if (mysql_num_rows($get_daughters_query) == 0)
	{
		return array();
	}
	else
	{
		$daughters = array();
		
		while ($daughter = mysql_fetch_array($get_daughters_query))
		{
			$husband_name = "";

			// Check if the daughter is not single, get her husband name.
			if ($daughter["marital_status"] != 1)
			{
				$husbands = get_husbands($daughter["id"]);
				
				if (count($husbands) > 0)
				{
					// Get the last relationship.
					$last_relation = $husbands[0];
					$husband_name = $last_relation["fullname"];
				}
			}

			$daughters []= array(
				"id" => $daughter["id"], "name" => $daughter["name"], "mother_id" => $daughter["mother_id"],
				"father_id" => $daughter["father_id"], "is_alive" => $daughter["is_alive"], "mobile" => $daughter["mobile"],
				"dob" => $daughter["dob"], "marital_status" => $daughter["marital_status"], "husband_name" => $husband_name,
				"mobile" => $daughter["mobile"], "tribe_id" => $daughter["tribe_id"]
			);
		}

		return $daughters;
	}
}

// public
function user_information()
{
	
	// COOKIE
	if (!isset($_COOKIE["sidrah_username"]) && !isset($_COOKIE["sidrah_password"]))
	{
		return array("group" => "visitor");
	}

	// Escape the data, someone might attack server.
	$cookie_username = mysql_real_escape_string($_COOKIE["sidrah_username"]);
	$cookie_password = mysql_real_escape_string($_COOKIE["sidrah_password"]);

	// Otherwise, there is information.
	$get_user_info_query = mysql_query("SELECT * FROM user WHERE username = '$cookie_username' AND password = '$cookie_password'");
	
	if (mysql_num_rows($get_user_info_query) == 0)
	{
		return array("group" => "visitor");
	}
	else
	{
		$user = mysql_fetch_array($get_user_info_query);

		return array(
			"id" => $user["id"],
			"username" => $user["username"],
			"password" => $user["password"],
			"group" => $user["usergroup"],
			"member_id" => $user["member_id"],
			"first_login" => $user["first_login"],
			"twitter_userid" => $user["twitter_userid"]
		);
	}
}

// public
function md5_salt($string)
{
	return md5($string . sidrah_salt);
}

// public
// Delete all related sons, daughters, relationships, hobbies, etc.
function delete_member($id)
{
	// Check if the member does exist.
	$member = get_member_id($id);
	
	if ($member)
	{
		// Check if the member is a tribe name.
		if ($member["gender"] == 1 && $member["father_id"] == -1)
		{
			// Delete the tribe
			$delete_tribe_query = mysql_query("DELETE FROM tribe WHERE id = '$member[tribe_id]'");
		}
	
		$parent = ($member["gender"] == 1) ? "father" : "mother";
		$partner = ($member["gender"] == 1) ? "husband" : "wife";
		
		// Delete this member.
		$delete_member_query = mysql_query("DELETE FROM member WHERE id = '$member[id]'");
		
		// Delete user mapped to this member.
		$delete_user_query = mysql_query("DELETE FROM user WHERE member_id = '$member[id]'");
		
		// Delete all married relations.
		$delete_married_query = mysql_query("DELETE FROM married WHERE {$partner}_id = '$member[id]'");
		
		// Delete all pending requests affecting this member.
		$delete_pending_requests_query = mysql_query( "DELETE FROM request WHERE status = 'pending' AND affected_id = '$member[id]'");
		
		// Delete all hobbies.
		$delete_hobbies_query = mysql_query("DELETE FROM member_hobby WHERE member_id = '$member[id]'");
		
		// Get all children and remove them.
		$get_children_query = mysql_query("SELECT id FROM member WHERE {$parent}_id = '$member[id]'");

		if (mysql_num_rows($get_children_query) > 0)
		{
			while ($child = mysql_fetch_array($get_children_query))
			{
				delete_member($child["id"]);
			}
		}
	}
}

// public
function update_fullname($id)
{
	$member = get_member_id($id);
	$fullname = "";
	
	if ($member["father_id"] == -1 && $member["gender"] == 1)
	{
		$fullname = $member["name"];
		
		// Update the tribe
		$update_tribe_query = mysql_query("UPDATE tribe SET name = '$member[name]' WHERE id = '$member[tribe_id]'");
	}
	else
	{
		$father = get_member_id($member["father_id"]);
		$fullname = "$member[name] $father[fullname]";
	}
	
	// Update the fullname of the member.
	$update_member_fullname_query = mysql_query("UPDATE member SET fullname = '$fullname' WHERE id = '$id'");
	
	// Get parent
	$parent = ($member["gender"] == 1) ? "father" : "mother";
	
	// Get all children and remove them.
	$get_children_query = mysql_query("SELECT id FROM member WHERE {$parent}_id = '$member[id]'");
	
	if (mysql_num_rows($get_children_query) > 0)
	{
		while ($child = mysql_fetch_array($get_children_query))
		{
			update_fullname($child["id"]);
		}
	}

	return mysql_affected_rows();
}

// public
function generate_key($length = 4, $use_numbers = true, $use_capital_letters = false, $use_small_letters = false, $use_symbols = false)
{
	$key_components = array();
	
	if ($use_numbers)
	{
		$key_components []= "1234567890";
	}
	
	if ($use_capital_letters)
	{
		$key_components []= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	}
	
	if ($use_small_letters)
	{
		$key_components []= "abcdefghijklmnopqrstuvwxyz";
	}

	if ($use_symbols)
	{
		$key_components []= "@$#!";
	}

	$components_count = count($key_components);

	// Do a bit of shuffling.
	shuffle($key_components);

	$key = "";

	for ($i=0; $i<$length; $i++)
	{
		$component_index = ($i % $components_count);
		$component_length = strlen($key_components[$component_index]);
		$random = rand(0, $component_length-1);
		$key .= $key_components[$component_index]{$random};
	}
	
	return $key;
}

// public
function view_member_page($id, $tribe_id = main_tribe_id)
{
	// Get user information.
	$user = user_information();
	$member = get_member_id($id);

	if ($user["group"] == "visitor")
	{
		$is_admin = $is_me = $is_accepted_moderator = $is_relative_user = false;
	}
	else
	{
		// Check if the user is admin
		$is_admin = ($user["group"] == "admin");

		// Check if the user is seeing his/her profile.
		$is_me = ($member["id"] == $user["member_id"]);

		// Check if the moderator is accepted (if any).
		$is_accepted_moderator = is_accepted_moderator($member["id"]);

		// Check if the user is relative to the member.
		$is_relative_user = is_relative_user($member["id"]);
	}

	// Initialize some variables.
	$gender = ($member["gender"] == 1) ? "male" : "female";
	$parent = ($member["gender"] == 1) ? "father" : "mother";
	
	$photo = rep_photo($member["photo"], $member["gender"]);
	
	// Get the fullname.
	$fullname = fullname($member["id"], "link", "<a class='names_serise' href='#toppage' onclick='get_node(%d);'>%s</a>%s");
	$fullname_title = $fullname;

	$nickname = "";
	$nickname_row = "";
	$mercy_word = "";
	$dod_row = "";
	
	// Set the privacies.
	$display_mother = privacy_display($member["id"], "mother", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_partners = privacy_display($member["id"], "partners", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_daughters = privacy_display($member["id"], "daughters", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);

	$display_marital_status = privacy_display($member["id"], "marital_status", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_dob = privacy_display($member["id"], "dob", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_pob = privacy_display($member["id"], "pob", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_age = privacy_display($member["id"], "age", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_mobile = privacy_display($member["id"], "mobile", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_phone_home = privacy_display($member["id"], "phone_home", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_email = privacy_display($member["id"], "email", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_phone_work = privacy_display($member["id"], "phone_work", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_location = privacy_display($member["id"], "location", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_living = privacy_display($member["id"], "living", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_neighborhood = privacy_display($member["id"], "neighborhood", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_education = privacy_display($member["id"], "education", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_major = privacy_display($member["id"], "major", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_company = privacy_display($member["id"], "company", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	$display_job_title = privacy_display($member["id"], "job_title", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	$display_blood_type = privacy_display($member["id"], "blood_type", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
	// Main array in this page.
	$details_array = array();

	$cv_html = parse_string_or_list($member["cv"]);
	
	if (!empty($cv_html))
	{
		$cv_html = "<ul>$cv_html</ul>";
	}
	
	$add_info = "";
	
	if ($user["group"] != "visitor")
	{
		$add_info = "<a href='edit_cv.php?id=$member[id]' class='small button'>إضافة معلومة</a>";
	}
	
	$details []= "<p><b>السيرة الذاتيّة</b>$cv_html<br />$add_info</p>";

	//TODO: If need for admin
	if ($is_admin)
	{
		// Change name.
		$details []= "<form action='init_change_name.php?id=$member[id]' method='post'><p><b>تغيير الاسم</b><br /><input type='text' name='name' value='$member[name]' /><button type='submit' name='submit' value='1' class='small button'>تم</button></p></form>";
	
		// Change nickname.
		$details []= "<form action='init_change_nickname.php?id=$member[id]' method='post'><p><b>تغيير اللقب</b><br /><input type='text' name='nickname' value='$member[nickname]' /><button type='submit' name='submit' value='1' class='small button'>تم</button></p></form>";
	
		// Change is_alive.
		$is_alive_toggle = ($member["is_alive"] == 1) ? "متوفّى" : "حيّ يرزق";	
		$details []= "<p><b>تغيير النبض</b><br /><a href='init_alive_toggle.php?id=$member[id]' class='small button'>$is_alive_toggle</a></p>";
	}

	// -------------------------------------------------
	// MOTHER
	// -------------------------------------------------
	//if ($is_admin || $is_me || $is_accepted_moderator || $is_relative_user)
	if ($display_mother)
	{
		$mother = get_member_id($member["mother_id"]);
		
		if ($mother)
		{
			// Check if the member has the same tribe.
			if ($tribe_id == $mother["tribe_id"])
			{
				$details []= "<p><b>الأم:</b><br /><a href='#toppage' onclick='get_node($mother[id]);'>$mother[fullname]</a></p>";
			}
			else
			{
				$details []= "<p><b>الأم:</b><br /><a href='familytree.php?tribe_id=$mother[tribe_id]&id=$mother[id]' target='_blank'>$mother[fullname]</a></p>";
			}
		}
	}

	// No privacy specified for this.
	// If the member has a nickname.
	if (!empty($member["nickname"]))
	{
		$details []= "<p><b>اللقب:</b><br />$member[nickname]</p>";
		$nickname = "($member[nickname])";
	}
	
	// No privacy specified for this.
	if ($user["group"] == "admin" || $user["group"] == "moderator")
	{
		$details []= "<form action='init_change_mobile.php?id=$member[id]' method='post'><p><b>الجوّال</b><br /><input type='text' name='mobile' value='$member[mobile]' /><button type='submit' name='submit' value='1' class='small button'>تم</button></p></form>";
	}

	else if ($display_mobile && $member["mobile"] != 0)
	{
		$details []= "<p><b>الجوّال</b><br />$member[mobile]</p>";
	}

	if ($display_phone_home && $member["phone_home"] != 0)
	{
		$details []= "<p><b>الهاتف</b><br />$member[phone_home]</p>";
	}
	
	if ($display_location && !empty($member["location"]))
	{
		$details []= "<p><b>مكان الإقامة الحاليّ</b><br />$member[location]</p>";
	}

	// Date of birth
	if (($display_dob || $member["dod"] != "0000-00-00") && $member["dob"] != "0000-00-00")
	{
		$details []= "<p><b>تاريخ الميلاد</b><br />$member[dob]</p>";
	}
	
	// Place of birth
	if ($display_pob && !empty($member["pob"]))
	{
		$details []= "<p><b>مكان الميلاد</b><br />$member[pob]</p>";
	}

	// If the member is dead.
	$mercy_word = ($member["gender"] == 1) ? rep_dead_mercy_male($member["is_alive"]) : rep_dead_mercy_female($member["is_alive"]);
	
	if ($member["dod"] != "0000-00-00")
	{
		$details []= "<p><b>تاريخ الوفاة</b><br />$member[dod]</p>";
	}
	
	if ($display_age && $member["age"] > 0)
	{
		$details []= "<p><b>العمر</b><br />$member[age]</p>";
	}
	
	// -------------------------------------------------
	// MARITAL STATUS
	// -------------------------------------------------
	if ($display_marital_status && $member["marital_status"] > 0)
	{
		$string_ms = ($member["gender"] == 1) ? rep_marital_status_male($member["marital_status"]) : rep_marital_status_female($member["marital_status"]);
		$details []= "<p><b>الحالة الاجتماعية</b><br />$string_ms</p>";
	}

	// -------------------------------------------------
	// EDUCATION	
	// -------------------------------------------------
	if ($display_education && $member["education"] != 0)
	{
		$details []= sprintf("<p><b>التعليم</b><br />%s</p>", rep_education($member["education"]));
	}

	if ($display_major && !empty($member["major"]))
	{
		$details []= "<p><b>التخصّص</b><br />$member[major]</p>";
	}

	// -------------------------------------------------
	// WORK/CAREER
	// -------------------------------------------------
	$company = rep_company($member["company_id"]);
	
	if ($display_company && !empty($company))
	{
		$details []= "<p><b>جهة العمل</b><br />$company</p>";
	}
		
	if ($display_job_title && !empty($member["job_title"]))
	{
		$details []= "<p><b>المسمّى الوظيفي</b><br />$member[job_title]</p>";
	}
	
	if ($display_phone_work && $member["phone_work"] != 0)
	{
		$details []= "<p><b>هاتف العمل</b><br />$member[phone_work]</p>";
	}

	// -------------------------------------------------
	// BLOOD TYPE
	// -------------------------------------------------
	if ($display_blood_type && !empty($member["blood_type"]))
	{
		$details []= "<p><b>فصيلة الدم</b><br />$member[blood_type]</p>";
	}

	// -------------------------------------------------
	// WIVES (OR) HUSBANDS
	// -------------------------------------------------
	if ($display_partners)
	{
		if ($member["gender"] == 1)
		{
			$wives = get_wives($member["id"]);
			$temp = array();
		
			if (count($wives) > 0)
			{
				foreach ($wives as $wife)
				{
					$alive_or_not = $wife["is_alive"] ? "<img src='views/img/female_alive.png' title='حيّة ترزق' />" : "<img src='views/img/not_alive.png' title='متوفّاة' />";
					
					// Check if the wife has the same tribe.
					if ($wife["tribe_id"] == $tribe_id)
					{
						$temp []= sprintf("$alive_or_not <a href='#toppage' onclick='get_node($wife[id]);'>$wife[fullname]</a> (%s)", rep_wife_ms_string($wife["ms_int"]));
					}
					else
					{
						$temp []= sprintf("$alive_or_not <a href='familytree.php?tribe_id=$wife[tribe_id]&id=$wife[id]' target='_blank'>$wife[fullname]</a> (%s)", rep_wife_ms_string($wife["ms_int"]));
					}
				}

				$details []= sprintf("<p><b>الزوجات (%d)</b><br />%s</p>", count($temp), implode("<br />", $temp));
			}
		}
		else
		{
			$husbands = get_husbands($member["id"]);
			$temp = array();
		
			if (count($husbands) > 0)
			{
				foreach ($husbands as $husband)
				{
					$alive_or_not = $husband["is_alive"] ? "<img src='views/img/male_alive.png' title='حيّ يرزق' />" : "<img src='views/img/not_alive.png' title='متوفّى' />";
					
					// Check if the husband has the same tribe.
					if ($husband["tribe_id"] == $tribe_id)
					{
						$temp []= sprintf("$alive_or_not <a href='#toppage' onclick='get_node($husband[id]);'>$husband[fullname]</a> (%s)", rep_husband_ms_string($husband["ms_int"]));
					}
					else
					{
						$temp []= sprintf("$alive_or_not <a href='familytree.php?tribe_id=$husband[tribe_id]&id=$husband[id]' target='_blank'>$husband[fullname]</a> (%s)", rep_husband_ms_string($husband["ms_int"]));
					}
				}

				$details []= sprintf("<p><b>الأزواج (%d)</b><br />%s</p>", count($temp), implode("<br />", $temp));
			}
		}
	}

	// -------------------------------------------------
	// SONS
	// -------------------------------------------------
	$sons_array = get_sons($member["id"], $parent);
	
	if (count($sons_array) > 0)
	{
		$_temp = array();
	
		$sons_count = sprintf("(%d)", count($sons_array));
		$sons = "";
		
		foreach ($sons_array as $son)
		{
			if ($tribe_id == $son["tribe_id"])
			{
				$_temp [] = sprintf("<a href='#toppage' onclick='get_node(%d);'>%s</a>", $son["id"], $son["name"]);
			}
			else
			{
				$_temp [] = sprintf("<a href='familytree.php?tribe_id=$son[tribe_id]&id=%d' target='_blank'>%s</a>", $son["id"], $son["name"]);
			}
		}
		
		$sons = implode(", ", $_temp);
	}
	else
	{
		$sons_count = "";
		$sons = "لا أبناء";
	}
	
	$details []= "<p><b>الأبناء $sons_count</b><br />$sons</p>";
	
	if ($display_daughters)
	{
		// Get daughters.
		$daughters_array = get_daughters($member["id"], $parent);
	
		if (count($daughters_array) > 0)
		{
			$_temp = array();
	
			$daughters_count = sprintf("(%d)", count($daughters_array));
			$daughters = "";

			foreach ($daughters_array as $daughter)
			{
				if ($tribe_id == $daughter["tribe_id"])
				{
					$_temp [] = sprintf("<a href='#toppage' onclick='get_node(%d);'>%s</a>", $daughter["id"], $daughter["name"]);
				}
				else
				{
					$_temp [] = sprintf("<a href='familytree.php?tribe_id=$daughter[tribe_id]&id=%d' target='_blank'>%s</a>", $daughter["id"], $daughter["name"]);
				}
			}
		
			$daughters = implode(", ", $_temp);
		}
		else
		{
			$daughters_count = "";
			$daughters = "لا بنات";
		}
		
		$details []= "<p><b>البنات $daughters_count</b><br />$daughters</p>";
	}

	// -------------------------------------------------
	// EMAIL
	// -------------------------------------------------
	if ($display_email && !empty($member["email"]))
	{
		$details []= "<p><b>البريد الإلكتروني</b><br /><a href='mailto:$member[email]'>$member[email]</a></p>";
	}

	// -------------------------------------------------
	// HOBBIES
	// -------------------------------------------------
	if ($user["group"] != "visitor")
	{
		$hobbies = get_member_hobbies($member["id"]);
		
		if (count($hobbies) > 0)
		{
			$hobbies_html = array();
		
			foreach ($hobbies as $hobby)
			{
				$hobbies_html []= "<a href='#hobby?name=$hobby'>$hobby</a>";
			}
		
			$details []= sprintf("<p><b>الهوايات</b><br />%s</p>", implode(", ", $hobbies_html));
		}
	}

	$socials = array();
	
	if (!empty($member["facebook"]))
	{
		$socials []= "<a href='http://www.facebook.com/$member[facebook]' target='_blank' title='$member[facebook]'><img src='views/img/profile-facebook.png' border='0' /></a>";
	}

	if (!empty($member["twitter"]))
	{
		$socials []= "<a href='http://www.twitter.com/$member[twitter]' target='_blank' title='@$member[twitter]'><img src='views/img/profile-twitter.png' border='0' /></a>";
	}

	if (!empty($member["linkedin"]))
	{
		$socials []= "<a href='$member[linkedin]' target='_blank'><img src='views/img/profile-linkedin.png' border='0' /></a>";
	}

	if (!empty($member["flickr"]))
	{
		$socials []= "<a href='http://www.flickr.com/photos/$member[flickr]' target='_blank' title='$member[flickr]'><img src='views/img/profile-flickr.png' border='0' /></a>";
	}

	if (!empty($member["website"]))
	{
		$socials []= "<a href='$member[website]' target='_blank' title='$member[website]'><img src='views/img/profile-website.png' border='0' /></a>";
	}
	
	if (count($socials) > 0)
	{
		$details []= "<p>" . implode(" ", $socials) . "</p>";		
	}

	$details_html = implode("\n", $details);

	$controls_html = "";
	$controls = array();
	
	// Check if the user is able to update a profile.
	if ($is_admin || $is_me || $is_accepted_moderator || $is_relative_user)
	{
		$controls []= "<a target='_blank' href='update_profile_{$gender}.php?id=$member[id]'>تحديث المعلومات الأساسية</a>";
		$controls []= "<a target='_blank' href='update_optional.php?id=$member[id]'>تحديث المعلومات الاختيارية</a>";
	}
	
	if ($is_admin || $is_accepted_moderator)
	{
		if ($member["mobile"] != "0")
		{
			$controls []= "<a href='init_send_user_info.php?member_id=$member[id]' title='إرسال معلومات الدخول إلى جوّال العضو عبر رسالة SMS.'>إرسال بيانات العضو</a>";
		}
	}
	
	if ($is_admin)
	{
		$controls []= "<a href='#toppage' onclick='delete_member($member[id])'>حذف العضو</a>";
	}
	
	if (count($controls) > 0)
	{
		$controls_html = "<p class='controls'>" . implode("<br />", $controls) . "</p>";
	}

	$content = template(
		"views/view_member.html", 
		array(
			"id" => $member["id"], "photo" => $photo, "name" => $member["name"], "nickname" => $nickname, "fullname" => $fullname_title,
			"mercy_word" => $mercy_word, "details" => $details_html, "controls" => $controls_html,
		)
	);
	
	return $content;
}

// public
function template($name, $replacements = null)
{
	$content = file_get_contents($name);

	if ($replacements != null && is_array($replacements))
	{
		foreach ($replacements as $key => $value)
		{
			$content = str_replace("{" . $key . "}", $value, $content);
		}
	}
	
	return $content;
}

// public
function normalize_name($fullname)
{
	$fullname = preg_replace("/[^أاإآبتثجحخدذرزسشصضطظعغفقكلمنهوؤيئءىﻻﻵة ]/u", "", $fullname);

	// Remove [Ben, Bent]
	$fullname = preg_replace("/(بنت|بن) /", '', $fullname);

	// Normalize [Abd]
	$fullname = preg_replace("/عبد /", "عبد", $fullname);

	// Special names
	$fullname = preg_replace("/(إ|أ|ا)نس/", "أنس", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)يمن/", "أيمن", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)يوب/", "أيوب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)حمد/", "أحمد", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)ديب/", "أديب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)سامة/", "أسامة", $fullname); // Could cause a bug.
	$fullname = preg_replace("/(إ|أ|ا)هاب/", "إيهاب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)ياد/", "إياد", $fullname);
	$fullname = preg_replace("/(أ|إ|ا)براهيم/", "إبراهيم", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)لهام/", "إلهام", $fullname);

	// Remove [Al]
	$fullname = preg_replace("/\s(ال)/", " ", $fullname);

	// Many spaces to one space
	$fullname = preg_replace("/[\s|\t]+/", " ", $fullname);
	$fullname = trim($fullname);
	
	return $fullname;
}

// public
function escape_confusing($sql)
{
	// Characters could cause some confusing.
	$sql = preg_replace("/(آ|أ|ا|إ)/", '(آ|أ|ا|إ)', $sql);
	$sql = preg_replace("/(ه|ة)/", "(ه|ة)", $sql);
	
	return $sql;
}

// public
function rep_marital_status_male($index)
{
	$status = array("بدون", "أعزب", "متزوج");
	return $status[$index];
}

// public
function rep_marital_status_female($index)
{
	$status = array("بدون", "عزباء", "متزوجة", "طليقة", "أرملة", "متوفاة");
	return $status[$index];
}

// public
function rep_is_alive_male($index)
{
	$status = array("متوفّى", "حيّ يرزق");
	return $status[$index];
}

// public
function rep_is_alive_female($index)
{
	$status = array("متوفاة", "حيّة ترزق");
	return $status[$index];
}

// public
function rep_education($index)
{
	$status = array("بدون", "ابتدائي", "متوسط", "ثانوي", "دبلوم", "بكالوريوس", "ماجستير", "دكتوراه");
	return $status[$index];
}

// public
function rep_dead_mercy_male($index)
{
	$mercy = array("(رحمه الله)", "");
	return $mercy[$index];
}

// public
function rep_dead_mercy_female($index)
{
	$mercy = array("(رحمها الله)", "");
	return $mercy[$index];
}

// public
function rep_company($id)
{
	if ($id == -1)
	{
		return "";
	}
	else
	{
		$get_company_query = mysql_query("SELECT name FROM company WHERE id = '$id'");
		$company = mysql_fetch_array($get_company_query);
		return $company["name"];
	}
}

// public
function rep_empty($str)
{
	return (empty($str)) ? "بدون" : $str;
}

// public
function rep_husband_ms_string($ms_int)
{
	$ms = array("", "", "زوج", "مطلّق");
	return $ms[$ms_int];
}

// public
function rep_wife_ms_string($ms_int)
{
	$ms = array("", "", "زوجة", "طليقة", "أرملة");
	return $ms[$ms_int];
}

// public
function rep_deanship_period_status($deanship_period_status)
{
	switch ($deanship_period_status)
	{
		case "nomination":
			return "ترشيح";
		break;
		
		case "voting":
			return "تصويت";
		break;
		
		case "ongoing":
			return "جارية";
		break;
		
		case "finished":
			return "منتهية";
		break;
	}
}

// public
function rep_photo($photo, $gender, $class = "")
{
	$src = "";

	if (empty($photo))
	{
		$path = "views/img";
		$src = ($gender == 1) ? "{$path}/photo_male.png" : "{$path}/photo_female.png";
	}
	else
	{
		$src = "views/pics/$photo";
	}
	
	return "<img src='$src' border='0' class='$class' />";
	//return $src;
}

// public
function rep_percent($total, $over)
{
	return round(($over/$total)*100) . "%";
}

// public
function get_tribe_id($tribe_name)
{
	// Check first, if found, then everything is ok.
	$get_tribe_query = mysql_query("SELECT id FROM tribe WHERE name = '$tribe_name' LIMIT 1");
	
	if (mysql_num_rows($get_tribe_query) > 0)
	{
		$tribe = mysql_fetch_array($get_tribe_query);
		return $tribe["id"];
	}
	else
	{
		// Insert a new tribe.
		$insert_tribe = mysql_query("INSERT INTO tribe (name) VALUES ('$tribe_name')");
		return mysql_insert_id();
	}
}

// public
function add_married($husband_id, $wife_id, $marital_status)
{
	
	// Find the relation, or insert a new one.
	$get_married_query = mysql_query("SELECT * FROM married WHERE husband_id = '$husband_id' AND wife_id = '$wife_id'");
	
	if (mysql_num_rows($get_married_query) > 0)
	{
		$married_info = mysql_fetch_array($get_married_query);
		$married_id = $married_info["id"];
	}
	else
	{
		// Insert a new relation.
		$insert_married_query = mysql_query("INSERT INTO married (husband_id, wife_id) VALUES ('$husband_id', '$wife_id')");
		$married_id = mysql_insert_id();
	}
	
	// Update marital status of the entry.
	$update_married_query = mysql_query("UPDATE married SET marital_status = '$marital_status' WHERE id = '$married_id'");
	
	// Update the marital status of both, husband and wife.
	update_husband_marital_status($husband_id);
	update_wife_marital_status($wife_id);
}

// public
function update_husband_marital_status($husband_id)
{
	$wives = get_wives($husband_id);
	$is_alive_value = "";
	
	if (count($wives) > 0)
	{
		$married_times = 0;
		$widow_times = 0;

		foreach ($wives as $wife)
		{
			if ($wife["ms"] == "married")
			{
				$married_times++;
			}
			else if ($wife["ms"] == "widow")
			{
				$widow_times++;
				$is_alive_value = ", is_alive = '0'";
			}
		}
	}
	
	if ($married_times > 0 || $widow_times > 0)
	{
		$marital_status = 2;
	}
	else
	{
		$marital_status = 1;
	}

	// Update the marital status of the member to be $marital_status.
	$update_ms_query = mysql_query("UPDATE member SET marital_status = '$marital_status'$is_alive_value WHERE id = '$husband_id'");
}

// public
function update_wife_marital_status($wife_id)
{
	$last_relation = 2;
	$husbands = get_husbands($wife_id);
	
	if (count($husbands) > 0)
	{
		$married_times = 0;
		$divorced_times = 0;
		$widower_times = 0;
		$widow_times = 0;

		$last_relation_enum = $husbands[0]["ms"];
		
		switch ($last_relation_enum)
		{
			case "married": case "widower":
				$last_relation = 2;
			break;
			
			case "divorced":
				$last_relation = 3;
			break;
			
			case "widow":
				$last_relation = 4;
			break;
		}
		
		foreach ($husbands as $husband)
		{
			switch ($husband["ms"])
			{
				case "married":
					$married_times++;
				break;
				
				case "divorced":
					$divorced_times++;
				break;
				
				case "widower":
					$widower_times++;
				break;
				
				case "widow":
					$widow_times++;
				break;
			}
		}
	}
	
	if ($married_times > 0)
	{
		$marital_status = 2;
	}
	else if ($divorced_times > 0 && $widower_times == 0 && $widow_times == 0)
	{
		$marital_status = 3;
	}
	else if ($widower_times > 0 && $divorced_times == 0 && $widow_times == 0)
	{
		$marital_status = 2;
	}
	else if ($widow_times > 0 && $divorced_times == 0 && $widower_times == 0)
	{
		$marital_status = 4;
	}
	else
	{
		$marital_status = $last_relation;
	}
	
	$is_alive_value = "";
	
	if ($widower_times > 0)
	{
		$is_alive_value = ", is_alive = '0'";
	}
	
	// Update the marital status of the member to be $marital_status.
	$update_ms_query = mysql_query("UPDATE member SET marital_status = '$marital_status'$is_alive_value WHERE id = '$wife_id'");
	return;
}

// public
function update_member_is_alive($member_id, $is_alive = 1)
{
	$update_query = mysql_query("UPDATE member SET is_alive = '$is_alive' WHERE id = '$member_id'");
	return mysql_affected_rows();
}

// public
function add_child($form, $tribe_id, $name, $father_id, $mother_id, $gender, $is_alive, $mobile, $dob = "", $location = "", $marital_status = "", $visible = "")
{
	// Depending on the form type, is it: male, or female.
	if ($form == "male")
	{
		$where = "AND (father_id = '$father_id')";
	}
	else
	{
		$where = "AND (father_id = '$father_id' AND (mother_id = '$mother_id' OR mother_id = '-1'))";
	}

	// Search for the child if exists.
	$get_child_query = mysql_query("SELECT id FROM member WHERE tribe_id = '$tribe_id' AND name = '$name' AND gender = '$gender' $where");

	$sql_array = array(
		"tribe_id" => "'$tribe_id'",
		"name" => "'$name'",
		"father_id" => "'$father_id'",
		"mother_id" => "'$mother_id'",
		"gender" => "'$gender'",
		"is_alive" => "'$is_alive'",
		"mobile" => "'$mobile'",
		"dob" => "'$dob'",
		"marital_status" => "'$marital_status'",
		"visible" => "'$visible'"
	);
	
	$child_id = null;
	
	if (mysql_num_rows($get_child_query) == 0)
	{
		// Add location too.
		$sql_array["location"] = "'$location'";
	
		$sql_k = implode(", ", array_keys($sql_array));
		$sql_v = implode(", ", array_values($sql_array));
	
		$now = time();
	
		// Insert a new one.
		$insert_child = mysql_query("INSERT INTO member($sql_k, created) VALUES ($sql_v, '$now')");
		$id = mysql_insert_id();

		// Update the fullname of the child.
		update_fullname($id);
		
		// LOG:
		echo "Add a new child: " . print_r($sql_array, 1);
		$child_id = $id;
	}
	else
	{
		$member = mysql_fetch_array($get_child_query);
		$sql_update = "";

		// Update the member marital status, is alive, and so on.
		foreach ($sql_array as $key => $value)
		{
			if ($value == "''")
			{
				continue;
			}

			$sql_update .= "$key = $value, ";
		}
		
		$full_update_sql = "UPDATE member SET " . substr($sql_update, 0, strlen($sql_update)-2) . " WHERE id = '$member[id]'"; 
		$update_member_query = mysql_query($full_update_sql);

		// LOG:
		if (mysql_affected_rows() > 0)
		{
			echo "Update a child: " . print_r($sql_array, 1);	
		}
		
		$child_id = $member["id"];
	}
	
	// Send mobile message if man, and has a mobile.
	if ($tribe_id == main_tribe_id)
	{
		create_user($child_id, $name);
	}

	return $child_id;
}

// public
function add_child_to_member($childname, $father_id, $gender = 1)
{
	$father_info = get_member_id($father_id, "gender = '1'");
	$child_fullname = "$childname $father_info[fullname]";
	$tribe_id = $father_info["tribe_id"];

	// Check if the child exists.
	$get_child_query = mysql_query("SELECT id FROM member WHERE tribe_id = '$tribe_id' AND fullname = '$child_fullname' AND gender = '$gender'");
	$childid = null;
	
	if (mysql_num_rows($get_child_query) > 0)
	{
		// If the child has been found.
		$child_info = mysql_fetch_array($get_child_query);
		$childid = $child_info["id"];
	}
	else
	{
		$now = time();
	
		// If the child has (not) been found.
		// Insert the child, with the fullname too.
		$insert_child_query = mysql_query("INSERT INTO member (tribe_id, father_id, name, fullname, gender, created) VALUES ('$tribe_id', '$father_info[id]', '$childname', '$child_fullname', '$gender', '$now')");
		$childid = mysql_insert_id();
	}

	// Send mobile message if man, and has a mobile.
	if ($tribe_id == main_tribe_id)
	{
		create_user($childid, $childname);
	}

	return $childid;
}

// public
function execute_request($key, $executed_by)
{
	ob_start();

	// Get the request by searching for the key.
	$get_request_query = mysql_query("SELECT * FROM request WHERE random_key = '$key'");
	
	if (mysql_num_rows($get_request_query) > 0)
	{
		$now = time();
	
		// Execute the PHP code.
		$request = mysql_fetch_array($get_request_query);
		eval($request["phpscript"]);
		
		// Accept the request, after executing it.
		$accept_request = mysql_query("UPDATE request SET status = 'accepted', executed = '$now', executed_by = '$executed_by' WHERE random_key = '$key'");
		
		// Get the user id of the affected member.
		$get_user_id_query = mysql_query("SELECT id FROM user WHERE member_id = '$request[affected_id]'");
		$get_user_id_fetch = mysql_fetch_array($get_user_id_query);
		$user_id = $get_user_id_fetch["id"];
		
		// Update the first login of the user to be not.
		$update_first_login = mysql_query("UPDATE user SET first_login = '0' WHERE id = '$user_id'");
	}
	
	// Get the output from buffer.
	$output = ob_get_contents();
	
	// Clean the output buffer a bit.
	ob_end_clean();
	
	return $output;
}

// public
// [l]eading [z]eros
function lz($str, $int = 2)
{
	return str_pad($str, $int, "0", STR_PAD_LEFT);
}

// public
function get_company_name($company_id)
{
	if ($company_id == -1)
	{
		return "";
	}
	else
	{
		$get_company_query = mysql_query("SELECT name FROM company WHERE id = '$company_id'");
		$company = mysql_fetch_array($get_company_query);
		return $company["name"];
	}
}

// public
function get_company_id($company_name)
{
	if ($company_name == "")
	{
		return -1;
	}

	$get_company_query = mysql_query("SELECT id FROM company WHERE name = '$company_name'");
	
	if (mysql_num_rows($get_company_query) == 0)
	{
		// Insert the company.
		$insert_company = mysql_query("INSERT INTO company (name) VALUES ('$company_name')");
		return mysql_insert_id();
	}
	else
	{
		// Get the company then.
		$company = mysql_fetch_array($get_company_query);
		return $company["id"];
	}
}

// public
function get_enum_married($husband_is_alive, $wife_is_alive, $husband_marital_status, $wife_marital_status)
{
	// If the wife is divorced.
	if ($wife_marital_status == 3)
	{
		return "divorced";
	}
	else if ($wife_marital_status == 4)
	{
		return "widow";
	}
	else if ($wife_marital_status == 2)
	{
		if ($husband_is_alive == 0 && $wife_is_alive == 0)
		{
			return "married";
		}
		else if ($husband_is_alive == 1 && $wife_is_alive == 0)
		{
			return "widower";
		}
		else if ($husband_is_alive == 0 && $wife_is_alive == 1)
		{
			return "widow";
		}
		
		return "married";
	}
	else if ($wife_marital_status == 1)
	{
		if ($husband_marital_status == 3)
		{
			return "divorced";
		}
		else if ($husband_marital_status == 2)
		{
			if ($husband_is_alive == 0 && $wife_is_alive == 0)
			{
				return "married";
			}
			else if ($husband_is_alive == 1 && $wife_is_alive == 0)
			{
				return "widower";
			}
			else if ($husband_is_alive == 0 && $wife_is_alive == 1)
			{
				return "widow";
			}
		
			return "married";
		}
	}
}

// public
function get_recommended_hobbies($member_id)
{
	$get_hobbies_query = mysql_query("SELECT hobby.id AS id, hobby.name AS name FROM hobby WHERE hobby.id NOT IN (SELECT member_hobby.hobby_id FROM member_hobby WHERE member_hobby.member_id = '$member_id') ORDER BY hobby.rank DESC LIMIT 10");
	
	if (mysql_num_rows($get_hobbies_query) == 0)
	{
		return array();
	}
	else
	{
		$hobbies = array();
		
		while ($hobby = mysql_fetch_array($get_hobbies_query))
		{
			$hobbies[$hobby["id"]]= $hobby["name"];
		}
		
		return $hobbies;
	}
}

// public
function get_member_hobbies($member_id)
{
	$get_hobbies_query = mysql_query("SELECT hobby.id AS id, hobby.name AS name FROM hobby, member_hobby WHERE member_hobby.hobby_id = hobby.id AND member_hobby.member_id = '$member_id'");
	
	if (mysql_num_rows($get_hobbies_query) == 0)
	{
		return array();
	}
	else
	{
		$hobbies = array();
		
		while ($hobby = mysql_fetch_array($get_hobbies_query))
		{
			$hobbies [$hobby["id"]]= $hobby["name"];
		}
		
		return $hobbies;
	}
}

// TODO: maybe there is a better way.
// public
function remove_member_hobbies($member_id)
{
	$hobbies = get_member_hobbies($member_id);
	
	if (count($hobbies) == 0)
	{
		return false;
	}
	else
	{
		foreach ($hobbies as $hkey => $hname)
		{
			// Delete hobby from member
			$delete_hobby = mysql_query("DELETE FROM member_hobby WHERE hobby_id = '$hkey' AND member_id = '$member_id'")or die(mysql_error());
			$update_hobby = mysql_query("UPDATE hobby SET rank = rank - 1 WHERE name = '$hname'")or die(mysql_error());
		}
		
		return true;
	}
}

// public
function add_hobby_to_member($hobby, $member_id)
{
	// Check if the hoppy already exists.
	$get_hobby_query = mysql_query("SELECT * FROM hobby WHERE name = '$hobby'");
	
	if (mysql_num_rows($get_hobby_query) == 0)
	{
		// Insert the hobby.
		$insert_hobby_query = mysql_query("INSERT INTO hobby (name) VALUES ('$hobby')");
		$hobby_id = mysql_insert_id();
	}
	else
	{
		// Get the hobby.
		$hobby = mysql_fetch_array($get_hobby_query);
		$hobby_id = $hobby["id"];
	}
	
	// Add the hobby for the member if it doesn't already exists.
	$get_member_hobby_query = mysql_query("SELECT * FROM member_hobby WHERE member_id = '$member_id' AND hobby_id = '$hobby_id'");
	
	if (mysql_num_rows($get_member_hobby_query) == 0)
	{
		// Insert the hobby for the member.
		$insert_member_hobby = mysql_query("INSERT INTO member_hobby (member_id, hobby_id) VALUES ('$member_id', '$hobby_id')");
	}
	
	// Update the rank of the hobby one+.
	$update_hobby_rank = mysql_query("UPDATE hobby SET rank = rank + 1 WHERE id = '$hobby_id'");
	return mysql_affected_rows();
}

// TODO: move to constants.
// public
function upload_file($file)
{
	$MAX_SIZE = 200 * 1024; // KB
	$NEW_WIDTH = 66;
	$NEW_HEIGHT = 77;
	$NEW_FILE_NAME = time() . ".jpg";
	$FULL_FILE_NAME = "views/pics/" . $NEW_FILE_NAME;
	
	$content_types = array(
		"image/jpeg", "image/gif", "image/png"
	);
	
	if ($file["error"] != 0)
	{
		return false;
	}
	
	if ($file["size"] > $MAX_SIZE)
	{
		return false;
	}
	
	if (!in_array($file["type"], $content_types))
	{
		return false;
	}
	
	if ($file["type"] == "image/jpeg")
	{
		$src = imagecreatefromjpeg($file["tmp_name"]);
	}
	else if ($file["type"] == "image/gif")
	{
		$src = imagecreatefromgif($file["tmp_name"]);
	}
	else if ($file["type"] == "image/png")
	{
		$src = imagecreatefrompng($file["tmp_name"]);
	}
	
	list($width, $height) = getimagesize($file["tmp_name"]);
	
	$image = imagecreatetruecolor($NEW_WIDTH, $NEW_HEIGHT);
	
	imagecopyresampled($image, $src, 0, 0, 0, 0, $NEW_WIDTH, $NEW_HEIGHT, $width, $height);
	
	imagejpeg($image, $FULL_FILE_NAME, 100);
	chmod($FULL_FILE_NAME, 0777);
	
	imagedestroy($src);
	imagedestroy($image);
	
	return $NEW_FILE_NAME;
}

// public
function shorten_name($fullname)
{
	$names = explode(" ", $fullname);
	return sprintf("%s %s %s %s", $names[0], $names[1], $names[2], $names[count($names)-1]);
}

// public
function get_num_stats($query, $key)
{
	$mysql_query = mysql_query($query);
	$mysql_fetch = mysql_fetch_array($mysql_query);
	return $mysql_fetch[$key];
}

// public
function get_range_stats($from, $cases, $condition = "", $order = "")
{
	$cases_query = implode(" ", $cases);
	$where = empty($condition) ? "" : "WHERE $condition";
	$order_by = empty($order) ? "" : "ORDER BY $order";
	
	$results = array();

	$get_ranges_stats = mysql_query("SELECT COUNT(id) AS counts, (CASE $cases_query END) AS ranges FROM $from $where GROUP BY ranges $order_by");
	
	if (mysql_num_rows($get_ranges_stats) > 0)
	{
		while ($range = mysql_fetch_array($get_ranges_stats))
		{
			$results[] = array(
				"range" => $range["ranges"],
				"count" => $range["counts"]
			);
		}
	}
	
	return $results;
}

// public
function get_rows_stats($query)
{
	$results = array();
	$rows_query = mysql_query($query)or die(mysql_error());
	
	if (mysql_num_rows($rows_query) > 0)
	{
		while ($row = mysql_fetch_array($rows_query))
		{
			$results[] = $row;
		}
	}
	
	return $results;
}

// public
// Convert a [greg] date to [hijri] date.
// TODO: Unrelaiable.
function greg_to_hijri($day, $month, $year)
{
	if (($year>1582) || ($year==1582 && $month>10) || (($year==1582) && ($month==10) && ($day>14)))
	{
		$jd = intval((1461*($year+4800+intval(($month-14)/12)))/4) +
		intval((367*($month-2-12*(intval(($month-14)/12))))/12) -
		intval((3*(intval(($year+4900+intval(($month-14)/12))/100)))/4)+$day-32075;
	}
	else
	{
		$jd = 367*$year-intval((7*($year+5001+intval(($month-9)/7)))/4)+intval((275*$month)/9)+$day+1729777;
	}
	
	$l = $jd-1948440+10632;
	$n = intval(($l-1)/10631);
	$l = $l-10631*$n+354;
	$j = (intval((10985-$l)/5316))*(intval((50*$l)/17719))+(intval($l/5670))*(intval((43*$l)/15238));
	$l = $l-(intval((30-$j)/15))*(intval((17719*$j)/50))-(intval($j/16))*(intval((15238*$j)/43))+29;
	
	$month = intval((24*$l)/709);
	$day = $l-intval((709*$month)/24);
	$year = 30*$n+$j-30;

	return array("d" => $day, "m" => $month, "y" => $year);
}

// public
function hijri_to_int($hj_day, $hj_month, $hj_year)
{
	return $hj_day + ($hj_month*29) + ($hj_year*355);
}

// public
function hijri_diff($hj_int2, $hj_int1)
{
	$hijri_diff = $hj_int2-$hj_int1;
	
	return array(
		"days" => $hijri_diff,
		"months" => floor($hijri_diff/29),
		"years" => floor($hijri_diff/355)
	);
}

// public
// @author: Khaled Mamdouh, Samir Greadly, Hussam Al-Zughaibi
// TODO: Unrelaiable.
function date_to_hijri($date)
{
	// Initialize.
	$days = round(strtotime($date)/(60*60*24));
	$hj_year = round($days/354.37419);
	$remain = $days-($hj_year*354.37419);
	$hj_month = round($remain/29.531182);
	$hj_day = $remain-($hj_month*29.531182);
	$hj_year = $hj_year+1389;
	$hj_month = $hj_month+10;
	$hj_day = $hj_day+23;

	// If the days is over 29, then update month and reset days.
	if ($hj_day>29.531188 and round($hj_day)!=30)
	{
		$hj_month = $hj_month+1;
		$hj_day = round($hj_day-29.531182);
	}
	else
	{
		$hj_day = round($hj_day);
	}

	// If months is over 12, then add a year and reset months.
	if($hj_month>12)
	{
		$hj_month = $hj_month-12;
		$hj_year = $hj_year+1;
	}

	// Return hijri date.
	return array($hj_day, $hj_month, $hj_year);
}

// public
function update_member_age($member_id, $age)
{
	return mysql_query("UPDATE member SET age = '$age' WHERE id = '$member_id'");
}

// public [array|hash]
function get_mothers($member_id, $output = "array")
{
	$mothers_array = array();
	$mothers_hash = "";
	
	$get_member_query = mysql_query("SELECT father_id FROM member WHERE id = '$member_id'");
	
	if (mysql_num_rows($get_member_query) > 0)
	{
		$member = mysql_fetch_array($get_member_query);
		
		if ($output == "array")
		{
			$mothers_array = get_wives($member["father_id"]);
		}
		else
		{
			$mothers_hash = get_wives($member["father_id"], "hash");
		}
	}
	
	if ($output == "array")
	{
		return $mothers_array;
	}
	else
	{
		return $mothers_hash;
	}
}

// public [array|hash]
function get_husbands($member_id, $output = "array")
{
	$husbands_array = array();
	$husbands_hash = array();
	
	$get_husbands_query = mysql_query("SELECT married.husband_id as husband_id, married.wife_id as wife_id, married.marital_status as ms FROM member, married WHERE (married.wife_id = member.id) AND married.wife_id = '$member_id' ORDER BY married.id DESC");
	
	if (mysql_num_rows($get_husbands_query) > 0)
	{
		while ($husband = mysql_fetch_array($get_husbands_query))
		{
			$one_husband = get_husband_id($husband["husband_id"]);
			
			if ($one_husband)
			{
				//$ms_int = 1;

				switch ($husband["ms"])
				{
					case "married": case "widow": case "widower":
						$ms_int = 2;
					break;

					case "divorced":
						$ms_int = 3;
					break;
				}

				$one_husband["ms"] = $husband["ms"];
				$one_husband["ms_int"] = $ms_int;
				
				$husbands_array []= $one_husband;
				$husbands_hash []= "{id: $one_husband[id], name: '$one_husband[fullname]'}";
			}
		}
	}
	
	if ($output == "array")
	{
		return $husbands_array;
	}
	else
	{
		return implode(",", $husbands_hash);
	}
}

// public [array|hash]
function get_wives($member_id, $output = "array")
{
	$wives_array = array();
	$wives_hash = array();
	
	$get_wives_query = mysql_query("SELECT married.wife_id as wife_id, married.husband_id as husband_id, married.marital_status as ms FROM member, married WHERE (married.husband_id = member.id) AND married.husband_id = '$member_id' ORDER BY married.id DESC");
	
	if (mysql_num_rows($get_wives_query) > 0)
	{
		while ($wife = mysql_fetch_array($get_wives_query))
		{
			$one_wife = get_wife_id($wife["wife_id"]);
			
			if ($one_wife)
			{
				$ms_int = 1;
		
				switch ($wife["ms"])
				{			
					case "married": case "widower":
						$ms_int = 2;
					break;

					case "divorced":
						$ms_int = 3;
					break;
				
					case "widow":
						$ms_int = 4;
					break;
				}
		
				$one_wife["ms"] = $wife["ms"];
				$one_wife["ms_int"] = $ms_int;
				
				$wives_array []= $one_wife;
				$wives_hash []= "{id: $one_wife[id], name: '$one_wife[fullname]'}";
			}
		}
	}
	
	if ($output == "array")
	{
		return $wives_array;
	}
	else
	{
		return implode(",", $wives_hash);
	}
}

// public
function get_husband_id($husband_id)
{
	return get_member_id($husband_id, "gender = '1'");
}

// public
function get_wife_id($wife_id)
{
	return get_member_id($wife_id, "gender = '0'");
}

// public
function get_member_id($member_id, $condition = "")
{
	$condition_query = (!empty($condition)) ? "AND $condition" : "";
	$get_member_id_query = mysql_query("SELECT * FROM member WHERE id = '$member_id' $condition_query");

	if (mysql_num_rows($get_member_id_query) > 0)
	{
		$one_member = mysql_fetch_array($get_member_id_query);
		return $one_member;
	}
	
	return false;
}

// public
function get_user_id($user_id, $condition = "")
{
	$condition_query = (!empty($condition)) ? "AND $condition" : "";
	$get_user_id_query = mysql_query("SELECT user.id as id, user.username as username, user.usergroup as usergroup, member.fullname as fullname, member.name as name, member.id as member_id, user.assigned_root_id as assigned_root_id FROM user, member WHERE user.member_id = member.id AND user.id = '$user_id' $condition_query");

	if (mysql_num_rows($get_user_id_query) > 0)
	{
		$one_user = mysql_fetch_array($get_user_id_query);
		return $one_user;
	}
	
	return false;
}

// public
function get_member_fullname($member_fullname, $condition = "")
{
	$condition_query = (!empty($condition)) ? "AND $condition" : "";
	$get_member_fullname_query = mysql_query("SELECT * FROM member WHERE fullname = '$member_fullname' $condition_query");

	if (mysql_num_rows($get_member_fullname_query) > 0)
	{
		$one_member = mysql_fetch_array($get_member_fullname_query);
		return $one_member;
	}
	
	return false;
}

// public
function add_member($fullname, $gender = 1)
{
	// First, check if the member already exists.
 	$get_member_fullname_query = mysql_query("SELECT id FROM member WHERE fullname = '$fullname' AND gender = '$gender'");
 
 	if (mysql_num_rows($get_member_fullname_query) > 0)
 	{
 		$member_info = mysql_fetch_array($get_member_fullname_query);
 		return $member_info["id"];
 	}
 	else
 	{
 		// Second, check the father name.
 		$names = explode(" ", $fullname);
 		$firstname = $names[0];
 		
 		unset($names[0]);
 		$fathername = implode(" ", $names);
 		
 		// Check if the father exists.
 		$get_father_fullname_query = mysql_query("SELECT id FROM member WHERE fullname = '$fathername' AND gender = '1'");
 		
 		if (mysql_num_rows($get_father_fullname_query) > 0)
 		{
 			$father_info = mysql_fetch_array($get_father_fullname_query);
 			return add_child_to_member($firstname, $father_info["id"], $gender);
 		}
 		else
 		{
 			// Start to walk from the last name.
 			// Get the tribe, or insert the tribe.
 			// FIXED: Bug of saving grandfather rather than familyname.
 			$tribe_id = get_tribe_id($names[count($names)]);
 			$member_id = add_member_walk($tribe_id, $fullname, -1, $gender);
 
			// Send mobile message if man, and has a mobile.
			if ($tribe_id == main_tribe_id)
			{
				create_user($member_id, $firstname);
			}
			
			return $member_id;
 		}
 	}
}

// public
function add_member_walk($tribe_id, $walk_name, $father_id, $gender = 1)
{
	// Get the names as an array.
	$names = explode(" ", $walk_name);
	
	if (count($names) == 1)
	{
		$firstname = $names[0];
		
		// Check if the member already exists.
		$get_member_query = mysql_query("SELECT id FROM member WHERE tribe_id = '$tribe_id' AND father_id = '$father_id' AND name = '$firstname' AND gender = '$gender'");
		
		if (mysql_num_rows($get_member_query) > 0)
		{
			$member_info = mysql_fetch_array($get_member_query);
			return $member_info["id"];
		}
		else
		{
			$father_info = get_member_id($father_id, "gender = '1'");
			$fullname = "$firstname $father_info[fullname]";
			$now = time();
			
			$insert_member_query = mysql_query("INSERT INTO member (tribe_id, father_id, name, fullname, gender, created) VALUES ('$tribe_id', '$father_id', '$firstname', '$fullname', '$gender', '$now')");
			return mysql_insert_id();
		}
	}
	else
	{
		$lastname = $names[count($names)-1];
		unset($names[count($names)-1]);
		
		// Check if the member already exists.
		$get_new_father_query = mysql_query("SELECT id FROM member WHERE tribe_id = '$tribe_id' AND father_id = '$father_id' AND name = '$lastname' AND gender = '1'");
		
		if (mysql_num_rows($get_new_father_query) > 0)
		{
			$new_father_info = mysql_fetch_array($get_new_father_query);
			$new_father_id = $new_father_info["id"];
		}
		else
		{
			if ($father_id == -1)
			{
				$fullname = $lastname;
			}
			else
			{
				$father_info = get_member_id($father_id, "gender = '1'");
				$fullname = "$lastname $father_info[fullname]";
			}

			$now = time();
			$insert_new_father_query = mysql_query("INSERT INTO member (tribe_id, father_id, name, fullname, gender, created) VALUES ('$tribe_id', '$father_id', '$lastname', '$fullname', '1', '$now')");
			$new_father_id = mysql_insert_id();
		}
		
		// Walk...
		return add_member_walk($tribe_id, implode(" ", $names), $new_father_id, $gender);
	}
}

// public
// $type = [string|link]
function fullname($member_id, $type = "string", $link_format = "")
{
	$result = "";

	// Get the information of the member.
	$member_info = get_member_id($member_id);

	if ($type == "string")
	{
		$result = $member_info["name"];
	}
	else
	{
		$nickname = ($member_info["nickname"] == "") ? "" : " ($member_info[nickname])";
		$result = sprintf($link_format, $member_id, $member_info["name"], $nickname);
	}
	
	// Check` if the member has a father.
	if ($member_info["father_id"] != -1)
	{
		return "$result " . fullname($member_info["father_id"], $type, $link_format);
	}
	else
	{
		return $result;
	}
}

// public
function assign_request($fullname)
{
	$get_moderators_query = mysql_query("SELECT user.id as user_id, member.fullname as assigned_fullname, LENGTH(member.fullname) as fullname_length FROM user, member WHERE user.assigned_root_id = member.id AND user.usergroup = 'moderator' ORDER BY fullname_length DESC")or die(mysql_error());
	
	if (mysql_num_rows($get_moderators_query) > 0)
	{
		while ($moderator = mysql_fetch_array($get_moderators_query))
		{
			if (preg_match("/$moderator[assigned_fullname]$/isU", $fullname) == true)
			{
				return $moderator["user_id"];
			}
		}
	}
	
	// If there is no an appropriate moderator.
	$get_rand_admin_query = mysql_query("SELECT id FROM user WHERE usergroup = 'admin' ORDER BY RAND() LIMIT 1");
	
	if (mysql_num_rows($get_rand_admin_query) > 0)
	{
		$admin = mysql_fetch_array($get_rand_admin_query);
		return $admin["id"];
	}
	else
	{
		return false;
	}
}

// public
function is_accepted_moderator($member_id)
{
	// Check if the member does exist.
	$member = get_member_id($member_id);
	
	if ($member)
	{
		// Get the current user information.
		$user = user_information();
		
		if ($user["group"] != "moderator")
		{
			return false;
		}
		else
		{
			// Get the fullname of the assigned root id.
			$user_info = get_user_id($user["id"]);
			
			if ($user_info)
			{
				$assigned_root_id = $user_info["assigned_root_id"];
				
				if ($assigned_root_id > 0)
				{
					$assigned_root_member = get_member_id($assigned_root_id);
					
					if ($assigned_root_member)
					{
						// Compare now between the name of the member and the name of the moderator.						
						if (preg_match("/$assigned_root_member[fullname]$/isU", $member["fullname"]))
						{
							return true;
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}				
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
	}
	else
	{
		return false;
	}
}

// public
function get_father($member_id)
{
	$member = get_member_id($member_id);
	
	if ($member)
	{
		return get_member_id($member["father_id"]);
	}
	else
	{
		return false;
	}
}

// public
function get_mother($member_id)
{
	$member = get_member_id($member_id);
	
	if ($member)
	{
		return get_member_id($member["mother_id"]);
	}
	else
	{
		return false;
	}
}

// public
function get_brothers($member_id)
{
	$member = get_member_id($member_id);
	
	if ($member)
	{
		$father = get_father($member_id);
		
		if ($father)
		{
			// Sons of my father are my brothers.
			$sons = get_sons($father["id"]);
			$brothers = array();
			
			for ($i=0; $i<count($sons); $i++)
			{
				if ($sons[$i]["id"] == $member_id)
				{
					continue;
				}
				else
				{
					$brothers []= $sons[$i];
				}
			}
			
			return $brothers;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

// public
function get_sisters($member_id)
{
	$member = get_member_id($member_id);
	
	if ($member)
	{
		$father = get_father($member_id);
		
		if ($father)
		{
			// Daughters of my father are my sisters.
			$daughters = get_daughters($father["id"]);
			$sisters = array();
			
			for ($i=0; $i<count($daughters); $i++)
			{
				if ($daughters[$i]["id"] == $member_id)
				{
					continue;
				}
				else
				{
					$sisters []= $daughters[$i];
				}
			}
			
			return $sisters;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

// public
function is_relative_user($member_id)
{
	// Check if the member does exist.
	$member = get_member_id($member_id);
	
	if ($member)
	{
		$user = user_information();
		
		if ($user["group"] == "user")
		{			
			// -------------------------------------------------
			// Father
			// -------------------------------------------------
			$father = get_father($member_id);
			
			if ($father)
			{
				if ($father["id"] == $user["member_id"])
				{
					// Father?
					return true;
				}
			}
			
			// -------------------------------------------------
			// Mother
			// -------------------------------------------------
			$mother = get_mother($member_id);
			
			if ($mother)
			{
				if ($mother["id"] == $user["member_id"])
				{
					// Mother?
					return true;
				}
			}

			// -------------------------------------------------
			// Brother
			// -------------------------------------------------
			$brothers = get_brothers($member_id);
			
			if ($brothers)
			{
				for ($i=0; $i<count($brothers); $i++)
				{
					if ($brothers[$i]["id"] == $user["member_id"])
					{
						// Brother?
						return true;
					}
				}
			}
			
			// -------------------------------------------------
			// Sister
			// -------------------------------------------------
			$sisters = get_sisters($member_id);
			
			if ($sisters)
			{
				for ($i=0; $i<count($sisters); $i++)
				{
					if ($sisters[$i]["id"] == $user["member_id"])
					{
						// Sister?
						return true;
					}
				}
			}
			
			// -------------------------------------------------
			// Son
			// -------------------------------------------------
			$sons = get_sons($member_id, "father");
			
			if ($sons)
			{
				for ($i=0; $i<count($sons); $i++)
				{
					if ($sons[$i]["id"] == $user["member_id"])
					{
						// Son?
						return true;
					}
				}
			}
			
			// -------------------------------------------------
			// Daughter
			// -------------------------------------------------
			$daughters = get_daughters($member_id, "father");
			
			if ($daughters)
			{
				for ($i=0; $i<count($daughters); $i++)
				{
					if ($daughters[$i]["id"] == $user["member_id"])
					{
						// Daughter?
						return true;
					}
				}
			}
			
			// Otherwise,
			return false;
		}
		else
		{
			return false;
		}
	}
	else
	{
		return false;
	}
}

// public
function create_user($id, $name)
{
	// Check if member id is correct.
	$member = get_member_id($id);
	
	if ($member)
	{
		// Check if there is a user id set to it.
		$get_user_info_query = mysql_query("SELECT * FROM user WHERE member_id = '$member[id]'");
		
		$username = "$name$id";
		$password = generate_key();
		$hashed_password = md5_salt($password);
		
		$sms_received = 0;
		
		// If there no users found.
		if (mysql_num_rows($get_user_info_query) == 0)
		{
			// Insert a new user
			$insert_query = mysql_query("INSERT INTO user (username, password, usergroup, member_id) VALUES ('$username', '$hashed_password', 'user', '$id')");
			$user_id = mysql_insert_id();
			
			// Send SMS.
			if ($member["mobile"] != 0)
			{
				$content = "عضويتك في موقع الزغيبي\nاسم المستخدم: $username\nكلمة المرور: $password";
				$sms_received = send_sms(array("966" . $member["mobile"]), $content);
				
				// Update the value of sms received.
				$update_sms_received_query = mysql_query("UPDATE user SET sms_received = '$sms_received' WHERE id = '$user_id'");
			}
		}
		else
		{
			// There is a user that is already inserted.
			$user_info = mysql_fetch_array($get_user_info_query);
			$user_id = $user_info["id"];
			
			// If the user has never received a message.
			if ($user_info["sms_received"] == 0 && $member["mobile"] != 0)
			{
				// Update the password (and only the password).
				$update_query = mysql_query("UPDATE user SET password = '$hashed_password' WHERE id = '$user_id'");
			
				$content = "عضويتك في موقع الزغيبي\nاسم المستخدم: $user_info[username]\nكلمة المرور: $password";
				$sms_received = send_sms(array("966" . $member["mobile"]), $content);

				// Update the value of sms received.
				$update_sms_received_query = mysql_query("UPDATE user SET sms_received = '$sms_received' WHERE id = '$user_id'");
			}
		}
		
		// Notify the user to change the password.
		if ($sms_received == 1)
		{
			notify("password_change", $user_id, "ننصحك بشدّة أن تقوم بتغيير كلمة المرور واستخدام كلمة مرور آمن.", "change_password.php");
		}
	}
}

// public
function auto_reassign_requests($user_id)
{
	// Check if the user does exist.
	$user = get_user_id($user_id);
	
	if ($user == false)
	{
		return false;
	}

	// Get all assigned requests.
	$get_assigned_requests_query = mysql_query("SELECT request.id as request_id, member.fullname as member_fullname FROM member, request WHERE request.affected_id = member.id AND request.assigned_to = '$user_id' AND request.status = 'pending'");
	
	if (mysql_num_rows($get_assigned_requests_query) > 0)
	{
		while ($request = mysql_fetch_array($get_assigned_requests_query))
		{
			$new_assigned_id = assign_request($request["member_fullname"]);
			$update_request_query = mysql_query("UPDATE request SET assigned_to = '$new_assigned_id' WHERE id = '$request[request_id]'");
		}
		
		return true;
	}
	else
	{
		return false;
	}
}

// public
function send_sms($to = array(), $content)
{
	/*
	// TODO: Exchange this with the given numbers.
	$to = array(
		"966553085572"
	);
	*/
	
	$message = string_to_unicode($content);

	$url = "http://www.mobily.ws/api/msgSend.php";
	
	$post = array(
		"mobile" => sms_username,
		"password" => sms_password,
		"numbers" => implode(",", $to),
		"sender" => sms_sender,
		"msg" => $message,
		"timeSend" => 0,
		"dateSend" => 0,
		"applicationType" => 24,
		"domainName" => $_SERVER["SERVER_NAME"],
		"deleteKey" => 705543
	);

	// Create a string containing the post variables.
	$post_string = http_build_query($post);

	// Create a new connection.
	$connection = curl_init();

	curl_setopt($connection, CURLOPT_URL, $url);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($connection, CURLOPT_HEADER, 0);
	curl_setopt($connection, CURLOPT_TIMEOUT, 5);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $post_string);
	
	// Execute the options up-there.
	$result = curl_exec($connection);

	// Log about it.
	$fopen = fopen("logs/sms.log", "a+");
	fwrite($fopen, implode(",", $to) . " -> " . $result . "\n");
	fclose($fopen);

	return (int)($result == 1);
}

// public
function check_balance()
{
	$url = "http://www.mobily.ws/api/balance.php";
	
	$post = array(
		"mobile" => sms_username,
		"password" => sms_password,
	);

	// Create a string containing the post variables.
	$post_string = http_build_query($post);

	// Create a new connection.
	$connection = curl_init();

	curl_setopt($connection, CURLOPT_URL, $url);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($connection, CURLOPT_HEADER, 0);
	curl_setopt($connection, CURLOPT_TIMEOUT, 5);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $post_string);
	
	// Execute the options up-there.
	$result = curl_exec($connection);
	
	// Split the balance from current
	$array = explode("/", $result);
	
	return array(
		"total" => $array[0],
		"current" => $array[1]
	);
}

// public
function string_to_unicode($string)
{
	// Replace string rather than La.
	$string = str_replace("ﻷ", "لأ", $string);
	$string = str_replace("ﻻ", "لا", $string);
	$string = str_replace("ﻵ", "لآ", $string);
	$string = str_replace("ﻹ", "لإ", $string);

	// Convert the string from utf-8 to windows-1256.
	$string = iconv("utf-8", "windows-1256", $string);
	
	$chars_unicodes = array(
		"،" => "060C",
		"؛" => "061B",
		"؟" => "061F",
		"ء" => "0621",
		"آ" => "0622",
		"أ" => "0623",
		"ؤ" => "0624",
		"إ" => "0625",
		"ئ" => "0626",
		"ا" => "0627",
		"ب" => "0628",
		"ة" => "0629",
		"ت" => "062A",
		"ث" => "062B",
		"ج" => "062C",
		"ح" => "062D",
		"خ" => "062E",
		"د" => "062F",
		"ذ" => "0630",
		"ر" => "0631",
		"ز" => "0632",
		"س" => "0633",
		"ش" => "0634",
		"ص" => "0635",
		"ض" => "0636",
		"ط" => "0637",
		"ظ" => "0638",
		"ع" => "0639",
		"غ" => "063A",
		"ف" => "0641",
		"ق" => "0642",
		"ك" => "0643",
		"ل" => "0644",
		"م" => "0645",
		"ن" => "0646",
		"ه" => "0647",
		"و" => "0648",
		"ى" => "0649",
		"ي" => "064A",
		"ـ" => "0640",
		"ً" => "064B",
		"ٌ" => "064C",
		"ٍ" => "064D",
		"َ" => "064E",
		"ُ" => "064F",
		"ِ" => "0650",
		"ّ" => "0651",
		"ْ" => "0652",
		"!" => "0021",
		"\"" => "0022",
		"#" => "0023",
		"$" => "0024",
		"%" => "0025",
		"&" => "0026",
		"'" => "0027",
		"(" => "0028",
		")" => "0029",
		"*" => "002A",
		"+" => "002B",
		"," => "002C",
		"-" => "002D",
		"." => "002E",
		"/" => "002F",
		"0" => "0030",
		"1" => "0031",
		"2" => "0032",
		"3" => "0033",
		"4" => "0034",
		"5" => "0035",
		"6" => "0036",
		"7" => "0037",
		"8" => "0038",
		"9" => "0039",
		":" => "003A",
		";" => "003B",
		"<" => "003C",
		"=" => "003D",
		">" => "003E",
		"?" => "003F",
		"@" => "0040",
		"A" => "0041",
		"B" => "0042",
		"C" => "0043",
		"D" => "0044",
		"E" => "0045",
		"F" => "0046",
		"G" => "0047",
		"H" => "0048",
		"I" => "0049",
		"J" => "004A",
		"K" => "004B",
		"L" => "004C",
		"M" => "004D",
		"N" => "004E",
		"O" => "004F",
		"P" => "0050",
		"Q" => "0051",
		"R" => "0052",
		"S" => "0053",
		"T" => "0054",
		"U" => "0055",
		"V" => "0056",
		"W" => "0057",
		"X" => "0058",
		"Y" => "0059",
		"Z" => "005A",
		"[" => "005B",
		"\\" => "005C",
		"]" => "005D",
		"^" => "005E",
		"_" => "005F",
		"`" => "0060",
		"a" => "0061",
		"b" => "0062",
		"c" => "0063",
		"d" => "0064",
		"e" => "0065",
		"f" => "0066",
		"g" => "0067",
		"h" => "0068",
		"i" => "0069",
		"j" => "006A",
		"k" => "006B",
		"l" => "006C",
		"m" => "006D",
		"n" => "006E",
		"o" => "006F",
		"p" => "0070",
		"q" => "0071",
		"r" => "0072",
		"s" => "0073",
		"t" => "0074",
		"u" => "0075",
		"v" => "0076",
		"w" => "0077",
		"x" => "0078",
		"y" => "0079",
		"z" => "007A",
		"{" => "007B",
		"|" => "007C",
		"}" => "007D",
		"~" => "007E",
		"©" => "00A9",
		"®" => "00AE",
		"÷" => "00F7",
		"×" => "00F7",
		"§" => "00A7",
		" " => "0020",
		"\n" => "000D",
		"\r" => "000A",
	);

	$windows_1256_chars_unicode = array();

	// Convert all keys to windows-1256.
	foreach ($chars_unicodes as $key => $value)
	{
		$new_key = iconv("utf-8", "windows-1256", $key);
		$windows_1256_chars_unicode[$new_key] = $value;
	}

	$string_length = strlen($string);
	$output = "";
	
	// Start to walk up-on the string.
	for ($i=0; $i<$string_length; $i++)
	{
		$output .= $windows_1256_chars_unicode[$string[$i]];
	}
	
	return $output;
}

// public
function get_request_update_notifications()
{
	$user = user_information();
	$assigned_to_query = "";
	
	if ($user["group"] == "admin")
	{
		$assigned_to_query = "";
	}
	else
	{
		$assigned_to_query = "AND assigned_to = '$user[id]'";
	}
	
	$get_pending_request = mysql_query("SELECT count(id) AS requests FROM request WHERE status = 'pending' $assigned_to_query");
	$pending_request = mysql_fetch_array($get_pending_request);
	
	// Returns, number of requests.
	return $pending_request["requests"];
}

// public
function get_notifications()
{
	$user = user_information();

	$get_notifications = mysql_query("SELECT count(id) AS notifications FROM notification WHERE user_id = '$user[id]' AND is_read = '0'");
	$updates_notifications = mysql_fetch_array($get_notifications);
	
	// Returns, number of requests.
	return $updates_notifications["notifications"];
}

// public
function get_committee_notifications()
{
	$user = user_information();
	
	// Check if the member is a head of a committee.
	$get_head_query = mysql_query("SELECT committee_id FROM member_committee WHERE member_id = '$user[member_id]' AND member_title = 'head'");
	
	if (mysql_num_rows($get_head_query) == 0)
	{
		return 0;
	}
	else
	{
		$head_committee = mysql_fetch_array($get_head_query);
		
		// Get the pending members to be joined the committee.
		$get_committee_pending_member_query = mysql_query("SELECT COUNT(id) AS pending_members FROM member_committee WHERE committee_id = '$head_committee[committee_id]' AND status = 'pending'");
		$committee_fetch = mysql_fetch_array($get_committee_pending_member_query);
		
		return $committee_fetch["pending_members"];
	}
}

// public
function get_all_notifications()
{
	// Get the notifications, for both: regular notifications and request update notifications.
	$request_updates_notifications_count = get_request_update_notifications();
	$notifications_count = get_notifications();
	//TODO: Uncomment when committees have been released. $committee_notifications = get_committee_notifications();
	
	$notifications_list = array();
		
	// Set the request updates notifications.
	if ($request_updates_notifications_count > 0)
	{
		$notifications_list []= "<a href='request_update.php' class='small button success' title='تحديثات معلّقة'>$request_updates_notifications_count</a>";
	}
	
	/* TODO: Uncomment when committees have been released.
	// Set the committee notifications.
	if ($committee_notifications > 0)
	{
		$notifications_list []= "<li><a href='request_committee.php' class='notification_committee' title='طلبات معلّقة للانضمام إلى اللجنة'><i class='icon-leaf icon-white'></i>$request_updates_notifications_count</a></li>";
	}
	*/
	
	if (count($notifications_list) == 0)
	{
		$notifications_list []= "<span class='small button secondary'>لا إشعارات</span>";
	}
	
	$notifications = template(
		"views/notifications_logged_in.html",
		array(
			"notifications" => implode("", $notifications_list)
		)
	);
	
	return $notifications;
}

function get_committees_nominee_congratulations()
{
	$user = user_information();
	$member_info = get_member_id($user["member_id"]);

	// Check if the member is nominee for any committee.
	$get_member_nominee_query = mysql_query("SELECT committee.id AS id, committee.name AS name FROM committee, member_committee WHERE committee.id = member_committee.committee_id AND member_committee.member_id = '$user[member_id]' AND member_committee.status = 'nominee'");
	$congratulations = "";

	if (mysql_num_rows($get_member_nominee_query) > 0)
	{
		$nominee_committees_array = array();
		
		while ($nominee_committee = mysql_fetch_array($get_member_nominee_query))
		{
			$nominee_committees_array []= "<a href='committees.php?do=view_committee&id=$nominee_committee[id]'>$nominee_committee[name]</a>";
		}
			
		$congratulations = template(
			"views/committee_congratulations.html",
			array(
				"name" => $member_info["name"],
				"nominee_committees" => implode(" و ", $nominee_committees_array)
			)
		);
	}
	
	return $congratulations;
}

function logged_in_box()
{
	// Get the information of the user.
	$user = user_information();
	$url = @$_GET["url"];
	
	if ($user["group"] == "visitor")
	{
		$logged = template(
			"views/userinfo2_not_logged.html",
			array(
				"url" => $url
			)
		);
	}
	else
	{
		// Get the gender.
		$member = get_member_id($user["member_id"]);
		$gender = ($member["gender"] == 1) ? "male" : "female";
		$page = basename($_SERVER["REQUEST_URI"]);
		$redirect_to = "update_profile_$gender.php?id=$member[id]";

		if ($user["group"] == "user" && $user["first_login"] == 1 && ($page != $redirect_to && $page != "change_password.php"))
		{
			redirect($redirect_to);
			return;
		}
		
		/* TODO: Uncomment when committees have been released.
		// Check if there is any committees.
		$get_committes_member_query = mysql_query("SELECT committee.name AS name, committee.id AS id FROM committee, member_committee WHERE member_committee.committee_id = committee.id AND member_committee.member_id = '$member[id]' AND member_committee.status != 'rejected'")or die(mysql_error());
		$committees = "";
		
		if (mysql_num_rows($get_committes_member_query) > 0)
		{
			$committees = '<li class="notice_string">اللجان</li>';
		
			while ($committee = mysql_fetch_array($get_committes_member_query))
			{
				$committees .= "<li><a href='committees.php?do=view_committee&id=$committee[id]'><i class='icon-flag'></i> $committee[name]</a></li>";
			}
		}
		*/
		
		$cockpit = "";
		
		// Check if the user is an admin.
		if ($user["group"] == "admin" || $user["group"] == "moderator")
		{			
			// Start to calculate everything.

			// Get request updates count.
			$request_updates_count = get_request_update_notifications();
			$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns' href='request_update.php'>تحديثات معلّقة ($request_updates_count)</a></div>";
			
			// Get the inactive members.
			
			// Get the related fullname.
			$user_info = get_user_id($user["id"]);
			$assigned_root_info = get_member_id($user_info["assigned_root_id"]);

			if ($assigned_root_info)
			{
				$related_fullname = $assigned_root_info["fullname"];
			}
			else
			{
				$related_fullname = "";
			}
			
			$get_inactive_users_query = mysql_query("SELECT count(user.id) as users_count FROM user, member WHERE user.member_id = member.id AND user.first_login = '1' AND member.mobile != '0' AND member.fullname LIKE '%$related_fullname'");
			$inative_users_fetch = mysql_fetch_array($get_inactive_users_query);
			$inactive_users_count = $inative_users_fetch["users_count"];
			$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns' href='inactive_users.php'>أعضاء غير فاعلين ($inactive_users_count)</a></div>";
			
			if ($user["group"] == "admin")
			{	
				/* TODO: Uncomment when committees have been released.
				// Get the committees.
				$get_committees_query = mysql_query("SELECT COUNT(id) as committees_count FROM committee");
				$committees_one = mysql_fetch_array($get_committees_query);
				$committees_count = $committees_one["committees_count"];
				$cockpit .= "<li><a href='manage_committees.php'><i class='icon-leaf'></i> إدارة اللجان ($committees_count)</a></li>";
				*/
				
				// Get the moderators.
				$get_moderators_query = mysql_query("SELECT COUNT(id) as moderators_count FROM user WHERE usergroup = 'moderator'");
				$moderators_one = mysql_fetch_array($get_moderators_query);
				$moderators_count = $moderators_one["moderators_count"];
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='manage_moderators.php'>إدارة المشرفين ($moderators_count)</a></div>";
				
				// Manage the dean.
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='manage_family_dean.php'>إدارة عمادة العائلة</a></div>";
				
				// Manage the jobs
				$get_jobs_query = mysql_query("SELECT COUNT(id) as jobs_count FROM job");
				$jobs_one = mysql_fetch_array($get_jobs_query);
				$jobs_count = $jobs_one["jobs_count"];
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='manage_jobs.php'>إدارة الوظائف ($jobs_count)</a></div>";
				
				// Get tribes count.
				$get_tribes_query = mysql_query("SELECT COUNT(id) as tribes_count FROM tribe");
				$tribes_one = mysql_fetch_array($get_tribes_query);
				$tribes_count = $tribes_one["tribes_count"];
				
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='send_sms.php'>إرسال SMS</a></div>";
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='tribes_familytrees.php'>شجر العوائل ($tribes_count)</a></div>";
				
				// Add filter page.
				$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns' href='filter.php'>تصفية و بحث</a></div>";
			}
		}
		
		if ($user["member_id"] == 348)
		{
			$cockpit .= "<div class='large-3 small-6 columns'><a class='small secondary button large-12 small-12 columns'  href='manage_ramadan_questions.php'>إدارة الأسئلة الرمضانيّة</a></div>";
		}

		$notifications = get_all_notifications();
		
		// Check if the user is not connected with Twitter.
		if (empty($user["twitter_userid"]))
		{
			$callback = "twitter.php?action=link";
			$cockpit .= "<div class='large-3 small-6 columns'><a class='small button large-12 small-12 columns'  href='twitter.php?action=authorize&callback=$callback'>ربط الحساب مع تويتر (@)</a></div>";
		}
		else
		{
			$cockpit .= "<div class='large-3 small-6 columns'><a class='small button secondary large-12 small-12 columns' href='twitter.php?action=unlink'>إلغاء ربط الحساب مع تويتر (@)</a></div>";
		}

		// Set the logged template
		$logged = template(
			"views/userinfo2_logged.html",
			array(
				"photo" => rep_photo($member["photo"], $member["gender"], "avatar"),
				"username" => $user["username"],
				"member_id" => $user["member_id"],
				"notifications" => $notifications,
				"gender" => $gender,
				"id" => $member["id"],
				//TODO: Uncomment when committees have been released. "committees" => $committees,
				"cockpit" => $cockpit
			)
		);
	}
	
	return $logged;
}

// public
function website_header($title = "", $description = "", $keywords = array(), $js = "")
{
	$js_html = "";

	if (!empty($js))
	{
		$js_html = '<script type="text/javascript">' . "\n$js\n" . '</script>';
	}
	
	$keywords_html = implode(",", $keywords);
	
	// Get the information of the user.
	$user = user_information();

	if ($user["group"] == "visitor")
	{
		$congratulations = "";
	}
	else
	{
		// TODO: Uncomment when committees have been released. $congratulations = get_committees_nominee_congratulations();
		$congratulations = "";

	}
	
	// Get logged in box.
	$logged = logged_in_box();
	
	$header = template(
		"views/header.html",
		array(
			"title" => $title,
			"description" => $description,
			"keywords" => $keywords_html,
			"js" => $js_html,
			"logged" => $logged,
			"congratulations" => $congratulations,
			"version" => version
		)
	);
	
	return $header;
}

// public
function website_footer()
{
	$request_uri = $_SERVER["REQUEST_URI"];
	$slash = strrpos($request_uri, "/");

	if ($slash === false)
	{
		$page = "";
	}
	else
	{
		$page = urlencode(
			substr($request_uri, $slash + 1)
		);
	}

	$footer = template(
		"views/footer.html",
		array(
			"page" => $page
		)
	);
	
	return $footer;
}

// public
function error_message($message = "")
{
	// Get the referer.
	$referer = @$_SERVER["HTTP_REFERER"];

	$template = template(
		"views/error_message.html",
		array(
			"message" => $message,
			"referer" => $referer,
		)
	);
	
	return $template;
}

// public
function success_message($message = "", $redirect = "")
{
	$template = template(
		"views/success_message.html",
		array(
			"message" => $message,
			"redirect" => $redirect
		)
	);
	
	return $template;
}

// public
function arabic_date($date_string)
{
	$date_string = str_replace("Nov", "نوفمبر", $date_string);
	$date_string = str_replace("Jan", "يناير", $date_string);
	$date_string = str_replace("Feb", "فبراير", $date_string);
	$date_string = str_replace("Mar", "مارس", $date_string);
	$date_string = str_replace("Apr", "أبريل" ,$date_string);
	$date_string = str_replace("May", "مايو", $date_string);
	$date_string = str_replace("Jun", "يونيو", $date_string);
	$date_string = str_replace("Jul", "يوليو", $date_string);
	$date_string = str_replace("Aug", "أغسطس", $date_string);
	$date_string = str_replace("Sep", "سبتمبر", $date_string);
	$date_string = str_replace("Oct", "أوكتوبر", $date_string);
	$date_string = str_replace("Dec", "ديسمبر", $date_string);
	
	return $date_string;
}

// public
function notify($type, $user_id, $content, $link)
{
	$now = time();
	$content = mysql_real_escape_string($content);

	// Add a new notification.
	$insert_notification = mysql_query("INSERT INTO notification (type, user_id, content, link, created) VALUES ('$type', '$user_id', '$content', '$link', '$now')");
	
	// Get member information.
	$get_member_query = mysql_query("SELECT member.email FROM member, user WHERE member.id = user.member_id AND user.id = '$user_id' AND member.email != ''");
	
	if (mysql_num_rows($get_member_query) > 0)
	{
		$member = mysql_fetch_array($get_member_query);
		notify_email(array($member["email"]), $type, $content, $link);
	}
}

// public
function notify_many($type, $content, $link, $user_ids = array())
{
	if (count($user_ids) > 0)
	{
		$values = array(); $now = time();
		$content = mysql_real_escape_string($content);
		
		// Initialize the SQL.
		$sql_insert =  "INSERT INTO notification (type, user_id, content, link, created) VALUES ";
		
		for ($i=0; $i<count($user_ids); $i++)
		{
			$values []= "('$type', '$user_ids[$i]', '$content', '$link', '$now')";
		}
		
		// Add some make-up.
		$sql_insert .= implode(", ", $values);
		
		// Execute the query.
		$insert_query = mysql_query($sql_insert);
		
		$user_ids_string = implode(", ", $user_ids);
		
		// Get member information.
		$get_member_query = mysql_query("SELECT member.email FROM member, user WHERE member.id = user.member_id AND user.id IN ($user_ids_string) AND member.email != ''");
	
		if (mysql_num_rows($get_member_query) > 0)
		{
			$emails = array();
		
			while ($member = mysql_fetch_array($get_member_query))
			{
				$emails []= $member["email"];
			}
			
			notify_email($emails, $type, $content, $link);
		}
	}
}

// public
function notify_all($type, $content, $link)
{
	// Get the alive users only.
	$get_users_query = mysql_query("SELECT user.id AS id FROM user, member WHERE user.member_id = member.id AND member.is_alive = 1");
	$user_ids = array();
	
	if (mysql_num_rows($get_users_query) > 0)
	{
		while ($user = mysql_fetch_array($get_users_query))
		{
			$user_ids []= $user["id"];
		}
		
		// Call another function.
		notify_many($type, $content, $link, $user_ids);
	}
}

// public
function notify_email($to = array(), $type, $content, $link)
{
	$subject_prefix = "موقع عائلة الزغيبي: ";

	switch ($type)
	{
		default:
			$subject = "إشعارات غير مقروءة.";
			$message = "$content</p><p><a href='http://www.alzughaibi.org/alzughaibi/$link'>تفضل هنا</a>.";
		break;
	}
	
	$footer = "موقع عائلة الزغيبي - جميع الحقوق محفوظة.";

	$email_content = template(
		"views/email_message.html",
		array(
			"subject" => $subject,
			"message" => $message,
			"footer" => $footer
		)
	);
	
	// Send the email.
	html_mail($to, "$subject_prefix$subject", $email_content);
}

// public
function get_all_mods_admins()
{
	$mods_admins = array();

	// Get all moderators and admins.
	$get_mods_admins_query = mysql_query("SELECT member.fullname as member_fullname, user.id as user_id, user.username as username FROM member, user WHERE user.member_id = member.id AND user.usergroup != 'user'");
	
	if (mysql_num_rows($get_mods_admins_query) > 0)
	{
		while ($mod_admin = mysql_fetch_array($get_mods_admins_query))
		{
			$mods_admins []= $mod_admin;
		}
	}
	
	return $mods_admins;
}

// public
function arabic_number($number)
{
	$number = str_replace("٠", "0", $number);
	$number = str_replace("۰", "0", $number);
	
	$number = str_replace("١", "1", $number);
	$number = str_replace("۱", "1", $number);
	
	$number = str_replace("٢", "2", $number);
	$number = str_replace("۲", "2", $number);
	
	$number = str_replace("٣", "3", $number);
	$number = str_replace("۳", "3", $number);
	
	$number = str_replace("٤", "4", $number);
	$number = str_replace("٥", "5", $number);
	$number = str_replace("٦", "6", $number);
	$number = str_replace("٧", "7", $number);
	
	$number = str_replace("٨", "8", $number);
	$number = str_replace("۸", "8", $number);
	
	$number = str_replace("٩", "9", $number);
	$number = str_replace("۹", "9", $number);
	
	return $number;
}

// public
function parse_string_or_list($string)
{
	// Check if there is a star.
	if (preg_match('/\*/', $string))
	{
		// Start replacing 'stars' with '<li>'.
		$string = preg_replace('/\*(.*)(\n|$)/', '<li>$1</li>', $string);
		
		// Add '<ol>' in the begining and in the end.
		$string = "<ol>$string</ol>";
	}

	return $string;
}

// public
// TODO:
function regex_arabic($string)
{

}

// public
function highlight_strings($text, $strings = array())
{	
	foreach ($strings as $string)
	{
		$text = str_replace($string, "<span class='highlight_string'>$string</span>", $text);
	}
	
	return $text;
}

// public
function time_diff($start, $end = null)
{
	$end = ($end == null) ? time() : $end;
	$diff = abs($start-$end);
	
	if ($diff == 0)
	{
		return "مباشرة";
	}
	
	$seconds = $diff;
	$minutes = round($diff / (60));
	$hours = round($diff / (60*60));
	$days = round($diff / (60*60*24));
	
	$diffs = array(

		$seconds => array(
			$seconds, "ثانية"
		),
		$minutes => array(
			$minutes, "دقيقة"
		),
		$hours => array(
			$hours, "ساعة"
		),
		$days => array(
			$days, "يوم"
		)
	);
	
	// Sort the array depending on the minmum.
	ksort($diffs);
	reset($diffs);
	
	$unit_value = 0;
	$unit_name = "";
	
	foreach ($diffs as $key => $value)
	{
		if ($key > 0)
		{
			$unit_value = $value[0];
			$unit_name = $value[1];
			break;
		}
	}

	return "$unit_value $unit_name";
}

// public
function privacy_select($select_name, $selected_value)
{
	// Privacy choices
	$privacy_choices = array(
		"all" => "للجميع",
		"members" => "للأعضاء فقط",
		"related_circle" => "لدائرة القرابة فقط",
		//"admins" => "للمدير فقط"
	);
	
	$select_html = "<select name='privacy[$select_name]'>\n";
	
	// Start walking up-on choices.
	foreach ($privacy_choices as $privacy_choice => $privacy_label)
	{
		// Check if this is selected.
		if ($selected_value == $privacy_choice)
		{
			$selected = "selected='selected'";
		}
		else
		{
			$selected = "";
		}
		
		$select_html .= "<option value='$privacy_choice' $selected>$privacy_label</option>\n";
	}
	
	$select_html .= "</select>\n\n";
	
	// Return the result.
	return $select_html;
}

// public
function privacy_display($member_id, $privacy_name, $usergroup, $is_me, $is_relative_user, $is_accepted_moderator)
{
	// Get the privacy name for the entered member.
	$get_privacy_name_query = mysql_query("SELECT privacy_{$privacy_name} FROM member WHERE id = '$member_id'");
	$fetch_privacy_name = mysql_fetch_array($get_privacy_name_query);
	$privacy = $fetch_privacy_name["privacy_{$privacy_name}"];
	
	// Check if the member is admin, or the same member is viewing his/her page, or the privacy specified to be viewed by all.
	if ($usergroup == "admin" || $is_me || $privacy == "all")
	{
		return true;
	}
	
	// Check if the member is user, or moderator.
	if ($usergroup == "user" || $usergroup == "moderator")
	{
		// Check if the privacy specified for only admins.
		if ($privacy == "admins")
		{
			return false;
		}
		
		// Check if the privacy is for members.
		if ($privacy == "members")
		{
			return false;
		}
		
		// Check if the privacy is for related circle.
		if ($privacy == "related_circle")
		{
			// Check if the member is relative, or is accepted moderator.
			if ($is_relative_user || $is_accepted_moderator)
			{
				return true;
			}
		}
	}
	
	return false;
}

// public
function check_user_availability($username, $new_username, $name)
{
	// Check if the new username is empty.
	if (empty($new_username))
	{
		return false;
	}

	$new_username_length = strlen($new_username);
	
	if ($new_username_length < user_min_length || $new_username_length > user_max_length)
	{
		return false;
	}

	// Check if the new username equals username.
	if ($username == $new_username)
	{
		return false;
	}
	
	// Check if the name comes after it any number.
	if (preg_match("/^([أاإآبتثجحخدذرزسشصضطظعغفقكلمنهوؤيئءىﻻﻵة]+)([0-9]+)$/", $new_username))
	{
		return false;
	}
	
	// Check if the username equals another username.
	$check_exact_username_query = mysql_query("SELECT username FROM user WHERE username = '$new_username'");
	
	if (mysql_num_rows($check_exact_username_query) > 0)
	{
		return false;
	}
	
	// Everything is okay.
	return true;
}

// public
function is_accepted_dean($member_id)
{
	// Get the member information.
	$member = get_member_id($member_id);

	// Accepted cities.
	$accepted_cities = array(
		"'الرياض'", "'جدة'", "'القصيم'", "'عنيزة'", "'البدائع'", "'الخبراء'", "'رياض الخبراء'", "'البكيرية'", "'الرس'"
	);

	// Accepted age.
	$accepted_minimum_age = 45;
	$accepted_maximum_age = 100;

	// Conditions.
	$conditions = array();

	$accepted_cities_string = implode(", ", $accepted_cities);

	$conditions[]= "member.gender = '1'";
	$conditions[]= "(member.age >= $accepted_minimum_age AND member.age <= $accepted_maximum_age)";
	//$conditions[]= "(member.location IN ($accepted_cities_string))";
	$conditions[]= "user.first_login = '0'";

	$conditions_string = implode(" AND ", $conditions);

	// Check if the member is applicable.
	$check_member_dean_query = mysql_query("SELECT member.id FROM member, user WHERE user.member_id = member.id AND member.id = '$member[id]' AND $conditions_string");
	
	// True, or False.
	return (mysql_num_rows($check_member_dean_query) > 0);
}

// public
// return: VOTE_ERROR, VOTE_CANNOT_YOURSELF, VOTE_CANNOT_NOT_APPLICABLE, VOTE_ALREADY_VOTED, VOTE_ACCEPTED
function can_vote_to_dean($member_id, $dean_id)
{
	// Get dean information.
	$get_dean_information_query = mysql_query("SELECT * FROM dean WHERE id = '$dean_id'");
	
	if (mysql_num_rows($get_dean_information_query) == 0)
	{
		return "VOTE_ERROR";
	}

	// Set a variable to hold dean information.
	$fetch_dean_information = mysql_fetch_array($get_dean_information_query);

	// Check if the dean is already selected.
	if ($fetch_dean_information["selected"] == 1)
	{
		return "VOTE_ERROR";
	}
	
	// Get the deanship information.
	$get_deanship_information_query = mysql_query("SELECT * FROM deanship_period WHERE id = '$fetch_dean_information[period_id]'");
	
	if (mysql_num_rows($get_deanship_information_query) == 0)
	{
		return "VOTE_ERROR";
	}
	
	$fetch_deanship_information = mysql_fetch_array($get_deanship_information_query);
	
	// Check if the status is not voting.
	if ($fetch_deanship_information["status"] != "voting")
	{
		return "VOTE_ERROR";
	}
	
	// Check if the member is the same dean.
	if ($fetch_dean_information["member_id"] == $member_id)
	{
		return "VOTE_CANNOT_YOURSELF";
	}
	
	// Get the information of the member.
	$member_info = get_member_id($member_id);
	
	if ($member_info == false)
	{
		return "VOTE_ERROR";
	}
	
	// Get the user information.
	$get_user_information_query = mysql_query("SELECT * FROM user WHERE member_id = '$member_info[id]'");
	
	if (mysql_num_rows($get_user_information_query) == 0)
	{
		return "VOTE_ERROR";
	}
	
	$user_info = mysql_fetch_array($get_user_information_query);

	// Check if the member has already voted.
	$get_member_dean_query = mysql_query("SELECT * FROM member_dean WHERE member_id = '$member_info[id]' AND dean_id IN (SELECT id FROM dean WHERE period_id = '$fetch_dean_information[period_id]')");
	
	if (mysql_num_rows($get_member_dean_query) > 0)
	{
		return "VOTE_ALREADY_VOTED";
	}

	// Check if the member is applicable for voting.
	if ($member_info["age"] < 18 || $user_info["first_login"] == 1)
	{
		return "VOTE_CANNOT_NOT_APPLICABLE";
	}
	
	return "VOTE_ACCEPTED";
}

// public
function text_replace($text, $replacements = null)
{
	if ($replacements != null && is_array($replacements))
	{
		foreach ($replacements as $key => $value)
		{
			$text = str_replace("{" . $key . "}", $value, $text);
		}
	}
	
	return $text;
}

// private
function gmod($n,$m)
{
	return (($n%$m) + $m) % $m;
}

// public
function date_info($day, $month, $year)
{
	$hijri_weekdays = array(
		0 => 1, // Sun => 1
		1 => 2, // Mon => 2
		2 => 3, // Tue => 3
		3 => 4, // Wed => 4
		4 => 5, // Thu => 5
		5 => 6, // Fri => 6
		6 => 0, // Sat => 0
	);

	$ummalqura_data = array(
			28607,28636,28665,28695,28724,28754,28783,28813,28843,28872,28901,28931,
			28960,28990,29019,29049,29078,29108,29137,29167,29196,29226,29255,29285,
			29315,29345,29375,29404,29434,29463,29492,29522,29551,29580,29610,29640,
			29669,29699,29729,29759,29788,29818,29847,29876,29906,29935,29964,29994,
			30023,30053,30082,30112,30141,30171,30200,30230,30259,30289,30318,30348,
			30378,30408,30437,30467,30496,30526,30555,30585,30614,30644,30673,30703,
			30732,30762,30791,30821,30850,30880,30909,30939,30968,30998,31027,31057,
			31086,31116,31145,31175,31204,31234,31263,31293,31322,31352,31381,31411,
			31441,31471,31500,31530,31559,31589,31618,31648,31676,31706,31736,31766,
			31795,31825,31854,31884,31913,31943,31972,32002,32031,32061,32090,32120,
			32150,32180,32209,32239,32268,32298,32327,32357,32386,32416,32445,32475,
			32504,32534,32563,32593,32622,32652,32681,32711,32740,32770,32799,32829,
			32858,32888,32917,32947,32976,33006,33035,33065,33094,33124,33153,33183,
			33213,33243,33272,33302,33331,33361,33390,33420,33450,33479,33509,33539,
			33568,33598,33627,33657,33686,33716,33745,33775,33804,33834,33863,33893,
			33922,33952,33981,34011,34040,34069,34099,34128,34158,34187,34217,34247,
			34277,34306,34336,34365,34395,34424,34454,34483,34512,34542,34571,34601,
			34631,34660,34690,34719,34749,34778,34808,34837,34867,34896,34926,34955,
			34985,35015,35044,35074,35103,35133,35162,35192,35222,35251,35280,35310,
			35340,35370,35399,35429,35458,35488,35517,35547,35576,35605,35635,35665,
			35694,35723,35753,35782,35811,35841,35871,35901,35930,35960,35989,36019,
			36048,36078,36107,36136,36166,36195,36225,36254,36284,36314,36343,36373,
			36403,36433,36462,36492,36521,36551,36580,36610,36639,36669,36698,36728,
			36757,36786,36816,36845,36875,36904,36934,36963,36993,37022,37052,37081,
			37111,37141,37170,37200,37229,37259,37288,37318,37347,37377,37406,37436,
			37465,37495,37524,37554,37584,37613,37643,37672,37701,37731,37760,37790,
			37819,37849,37878,37908,37938,37967,37997,38027,38056,38085,38115,38144,
			38174,38203,38233,38262,38292,38322,38351,38381,38410,38440,38469,38499,
			38528,38558,38587,38617,38646,38676,38705,38735,38764,38794,38823,38853,
			38882,38912,38941,38971,39001,39030,39059,39089,39118,39148,39178,39208,
			39237,39267,39297,39326,39355,39385,39414,39444,39473,39503,39532,39562,
			39592,39621,39650,39680,39709,39739,39768,39798,39827,39857,39886,39916,
			39946,39975,40005,40035,40064,40094,40123,40153,40182,40212,40241,40271,
			40300,40330,40359,40389,40418,40448,40477,40507,40536,40566,40595,40625,
			40655,40685,40714,40744,40773,40803,40832,40862,40892,40921,40951,40980,
			41009,41039,41068,41098,41127,41157,41186,41216,41245,41275,41304,41334,
			41364,41393,41422,41452,41481,41511,41540,41570,41599,41629,41658,41688,
			41718,41748,41777,41807,41836,41865,41894,41924,41953,41983,42012,42042,
			42072,42102,42131,42161,42190,42220,42249,42279,42308,42337,42367,42397,
			42426,42456,42485,42515,42545,42574,42604,42633,42662,42692,42721,42751,
			42780,42810,42839,42869,42899,42929,42958,42988,43017,43046,43076,43105,
			43135,43164,43194,43223,43253,43283,43312,43342,43371,43401,43430,43460,
			43489,43519,43548,43578,43607,43637,43666,43696,43726,43755,43785,43814,
			43844,43873,43903,43932,43962,43991,44021,44050,44080,44109,44139,44169,
			44198,44228,44258,44287,44317,44346,44375,44405,44434,44464,44493,44523,
			44553,44582,44612,44641,44671,44700,44730,44759,44788,44818,44847,44877,
			44906,44936,44966,44996,45025,45055,45084,45114,45143,45172,45202,45231,
			45261,45290,45320,45350,45380,45409,45439,45468,45498,45527,45556,45586,
			45615,45644,45674,45704,45733,45763,45793,45823,45852,45882,45911,45940,
			45970,45999,46028,46058,46088,46117,46147,46177,46206,46236,46265,46295,
			46324,46354,46383,46413,46442,46472,46501,46531,46560,46590,46620,46649,
			46679,46708,46738,46767,46797,46826,46856,46885,46915,46944,46974,47003,
			47033,47063,47092,47122,47151,47181,47210,47240,47269,47298,47328,47357,
			47387,47417,47446,47476,47506,47535,47565,47594,47624,47653,47682,47712,
			47741,47771,47800,47830,47860,47890,47919,47949,47978,48008,48037,48066,
			48096,48125,48155,48184,48214,48244,48273,48303,48333,48362,48392,48421,
			48450,48480,48509,48538,48568,48598,48627,48657,48687,48717,48746,48776,
			48805,48834,48864,48893,48922,48952,48982,49011,49041,49071,49100,49130,
			49160,49189,49218,49248,49277,49306,49336,49365,49395,49425,49455,49484,
			49514,49543,49573,49602,49632,49661,49690,49720,49749,49779,49809,49838,
			49868,49898,49927,49957,49986,50016,50045,50075,50104,50133,50163,50192,
			50222,50252,50281,50311,50340,50370,50400,50429,50459,50488,50518,50547,
			50576,50606,50635,50665,50694,50724,50754,50784,50813,50843,50872,50902,
			50931,50960,50990,51019,51049,51078,51108,51138,51167,51197,51227,51256,
			51286,51315,51345,51374,51403,51433,51462,51492,51522,51552,51582,51611,
			51641,51670,51699,51729,51758,51787,51816,51846,51876,51906,51936,51965,
			51995,52025,52054,52083,52113,52142,52171,52200,52230,52260,52290,52319,
			52349,52379,52408,52438,52467,52497,52526,52555,52585,52614,52644,52673,
			52703,52733,52762,52792,52822,52851,52881,52910,52939,52969,52998,53028,
			53057,53087,53116,53146,53176,53205,53235,53264,53294,53324,53353,53383,
			53412,53441,53471,53500,53530,53559,53589,53619,53648,53678,53708,53737,
			53767,53796,53825,53855,53884,53913,53943,53973,54003,54032,54062,54092,
			54121,54151,54180,54209,54239,54268,54297,54327,54357,54387,54416,54446,
			54476,54505,54535,54564,54593,54623,54652,54681,54711,54741,54770,54800,
			54830,54859,54889,54919,54948,54977,55007,55036,55066,55095,55125,55154,
			55184,55213,55243,55273,55302,55332,55361,55391,55420,55450,55479,55508,
			55538,55567,55597,55627,55657,55686,55716,55745,55775,55804,55834,55863,
			55892,55922,55951,55981,56011,56040,56070,56100,56129,56159,56188,56218,
			56247,56276,56306,56335,56365,56394,56424,56454,56483,56513,56543,56572,
			56601,56631,56660,56690,56719,56749,56778,56808,56837,56867,56897,56926,
			56956,56985,57015,57044,57074,57103,57133,57162,57192,57221,57251,57280,
			57310,57340,57369,57399,57429,57458,57487,57517,57546,57576,57605,57634,
			57664,57694,57723,57753,57783,57813,57842,57871,57901,57930,57959,57989,
			58018,58048,58077,58107,58137,58167,58196,58226,58255,58285,58314,58343,
			58373,58402,58432,58461,58491,58521,58551,58580,58610,58639,58669,58698,
			58727,58757,58786,58816,58845,58875,58905,58934,58964,58994,59023,59053,
			59082,59111,59141,59170,59200,59229,59259,59288,59318,59348,59377,59407,
			59436,59466,59495,59525,59554,59584,59613,59643,59672,59702,59731,59761,
			59791,59820,59850,59879,59909,59939,59968,59997,60027,60056,60086,60115,
			60145,60174,60204,60234,60264,60293,60323,60352,60381,60411,60440,60469,
			60499,60528,60558,60588,60618,60648,60677,60707,60736,60765,60795,60824,
			60853,60883,60912,60942,60972,61002,61031,61061,61090,61120,61149,61179,
			61208,61237,61267,61296,61326,61356,61385,61415,61445,61474,61504,61533,
			61563,61592,61621,61651,61680,61710,61739,61769,61799,61828,61858,61888,
			61917,61947,61976,62006,62035,62064,62094,62123,62153,62182,62212,62242,
			62271,62301,62331,62360,62390,62419,62448,62478,62507,62537,62566,62596,
			62625,62655,62685,62715,62744,62774,62803,62832,62862,62891,62921,62950,
			62980,63009,63039,63069,63099,63128,63157,63187,63216,63246,63275,63305,
			63334,63363,63393,63423,63453,63482,63512,63541,63571,63600,63630,63659,
			63689,63718,63747,63777,63807,63836,63866,63895,63925,63955,63984,64014,
			64043,64073,64102,64131,64161,64190,64220,64249,64279,64309,64339,64368,
			64398,64427,64457,64486,64515,64545,64574,64603,64633,64663,64692,64722,
			64752,64782,64811,64841,64870,64899,64929,64958,64987,65017,65047,65076,
			65106,65136,65166,65195,65225,65254,65283,65313,65342,65371,65401,65431,
			65460,65490,65520,65549,65579,65608,65638,65667,65697,65726,65755,65785,
			65815,65844,65874,65903,65933,65963,65992,66022,66051,66081,66110,66140,
			66169,66199,66228,66258,66287,66317,66346,66376,66405,66435,66465,66494,
			66524,66553,66583,66612,66641,66671,66700,66730,66760,66789,66819,66849,
			66878,66908,66937,66967,66996,67025,67055,67084,67114,67143,67173,67203,
			67233,67262,67292,67321,67351,67380,67409,67439,67468,67497,67527,67557,
			67587,67617,67646,67676,67705,67735,67764,67793,67823,67852,67882,67911,
			67941,67971,68000,68030,68060,68089,68119,68148,68177,68207,68236,68266,
			68295,68325,68354,68384,68414,68443,68473,68502,68532,68561,68591,68620,
			68650,68679,68708,68738,68768,68797,68827,68857,68886,68916,68946,68975,
			69004,69034,69063,69092,69122,69152,69181,69211,69240,69270,69300,69330,
			69359,69388,69418,69447,69476,69506,69535,69565,69595,69624,69654,69684,
			69713,69743,69772,69802,69831,69861,69890,69919,69949,69978,70008,70038,
			70067,70097,70126,70156,70186,70215,70245,70274,70303,70333,70362,70392,
			70421,70451,70481,70510,70540,70570,70599,70629,70658,70687,70717,70746,
			70776,70805,70835,70864,70894,70924,70954,70983,71013,71042,71071,71101,
			71130,71159,71189,71218,71248,71278,71308,71337,71367,71397,71426,71455,
			71485,71514,71543,71573,71602,71632,71662,71691,71721,71751,71781,71810,
			71839,71869,71898,71927,71957,71986,72016,72046,72075,72105,72135,72164,
			72194,72223,72253,72282,72311,72341,72370,72400,72429,72459,72489,72518,
			72548,72577,72607,72637,72666,72695,72725,72754,72784,72813,72843,72872,
			72902,72931,72961,72991,73020,73050,73080,73109,73139,73168,73197,73227,
			73256,73286,73315,73345,73375,73404,73434,73464,73493,73523,73552,73581,
			73611,73640,73669,73699,73729,73758,73788,73818,73848,73877,73907,73936,
			73965,73995,74024,74053,74083,74113,74142,74172,74202,74231,74261,74291,
			74320,74349,74379,74408,74437,74467,74497,74526,74556,74586,74615,74645,
			74675,74704,74733,74763,74792,74822,74851,74881,74910,74940,74969,74999,
			75029,75058,75088,75117,75147,75176,75206,75235,75264,75294,75323,75353,
			75383,75412,75442,75472,75501,75531,75560,75590,75619,75648,75678,75707,
			75737,75766,75796,75826,75856,75885,75915,75944,75974,76003,76032,76062,
			76091,76121,76150,76180,76210,76239,76269,76299,76328,76358,76387,76416,
			76446,76475,76505,76534,76564,76593,76623,76653,76682,76712,76741,76771,
			76801,76830,76859,76889,76918,76948,76977,77007,77036,77066,77096,77125,
			77155,77185,77214,77243,77273,77302,77332,77361,77390,77420,77450,77479,
			77509,77539,77569,77598,77627,77657,77686,77715,77745,77774,77804,77833,
			77863,77893,77923,77952,77982,78011,78041,78070,78099,78129,78158,78188,
			78217,78247,78277,78307,78336,78366,78395,78425,78454,78483,78513,78542,
			78572,78601,78631,78661,78690,78720,78750,78779,78808,78838,78867,78897,
			78926,78956,78985,79015,79044,79074,79104,79133,79163,79192,79222,79251,
			79281,79310,79340,79369,79399,79428,79458,79487,79517,79546,79576,79606,
			79635,79665,79695,79724,79753,79783,79812,79841,79871,79900,79930,79960,
			79990,
	);

	// Get calendar data.
	$m = $month;
	$y = $year;

	// Append January and February to the previous year (i.e. regard March as
	// the first month of the year in order to simplify leapday corrections).
	if($m < 3)
	{
		$y -= 1;
		$m += 12;
	}

	// Determine offset between Julian and Gregorian calendar.
	$a = floor($y/100.0);
	$jgc = $a - floor($a/4.0) - 2;

	// Compute Chronological Julian Day Number (CJDN).
	$cjdn = floor(365.25*($y+4716)) + floor(30.6001*($m+1)) + $day - $jgc - 1524;
	
	$a = floor(($cjdn - 1867216.25)/36524.25);
	$jgc = $a - floor($a/4.0) + 1;
	$b = $cjdn + $jgc + 1524;
	$c = floor(($b - 122.1)/365.25);
	$d = floor(365.25*$c);
	$month = floor(($b - $d)/30.6001);
	$day = ($b - $d)-floor(30.6001*$month);

	if($month>13) {
		$c += 1;
		$month -= 12;
	}

	$month -= 1;
	$year = $c - 4716;  

	// Compute weekday.
	$wd = gmod($cjdn + 1,7);
	$hijriwd = $hijri_weekdays[$wd];

	// Compute Modified Chronological Julian Day Number (MCJDN).
	$mcjdn = $cjdn - 2400000;

	// The MCJDN's of the start of the lunations in the 'Umm al-Qura' calendar are stored in 'ummalqura_data'.
	for($i=0; $i<count($ummalqura_data); $i++){
		if($ummalqura_data[$i] > $mcjdn)
		{
			break;
		}
	}

	// Compute and output the Umm al-Qura calendar date.
	$iln = $i + 16260;
	$ii = floor(($iln-1)/12);
	$iy = $ii + 1;
	$im = $iln - 12*$ii;
	$id = $mcjdn - $ummalqura_data[$i-1] + 1;
	$ml = $ummalqura_data[$i] - $ummalqura_data[$i-1];

	return array(
		"miladi_day" => $day,
		"miladi_month" => $month,
		"miladi_year" => $year,
		"julian_day" => $cjdn,
		"weekday" => $wd,
		"hijri_weekday" => $hijriwd,
		"mcjdn" => $mcjdn,
		"hijri_day" => $id,
		"hijri_month" => $im,
		"hijri_year" => $iy,
		"ilunnum" => $iln,
		"hijri_month_length" => $ml
	);
}

// private
function int_part($float)
{
	if ($float < -0.0000001)
	{
		return ceil($float-0.0000001);
	}
		
	return floor($float+0.0000001);
}

// public
function hijri_to_miladi($hijri_day, $hijri_month, $hijri_year)
{
	$d = $hijri_day;
	$m = $hijri_month;
	$y = $hijri_year;

	if($y < 1700)
	{
		$jd = int_part((11*$y+3)/30) + 354*$y + 30*$m - int_part(($m-1)/2) + $d + 1948440 - 385;

		if($jd > 2299160)
		{
			$l = $jd + 68569;
			$n = int_part((4*$l)/146097);
			$l = $l - int_part((146097*$n+3)/4);
			$i = int_part((4000*($l+1))/1461001);
			$l = $l - int_part((1461*$i)/4) + 31;
			$j = int_part((80*$l)/2447);
			$d = $l - int_part((2447*$j)/80);
			$l = int_part($j/11);
			$m = $j + 2 - 12*$l;
			$y = 100*($n-49) +$i + $l;
		}
		else
		{
			$j = $jd + 1402;
			$k = int_part(($j-1)/1461);
			$l = $j - 1461*$k;
			$n = int_part(($l-1)/365) - int_part($l/1461);
			$i = $l - 365*$n + 30;
			$j = int_part((80*$i)/2447);
			$d = $i - int_part((2447*$j)/80);
			$i = int_part($j/11);
			$m = $j + 2 - 12*$i;
			$y = 4*$k + $n + $i - 4716;
		}

		// Return miladi date.
		return array(
			"day" => $d,
			"month" => $m,
			"year" => $y
		);
	}
}

// public
function get_event_reaction($event_id, $member_id, $hijri_date)
{
	// Get the member information.
	$member = get_member_id($member_id);

	// Set the current hijri date as an integer.
	$hijri_date_int = $hijri_date["hijri_day"] + ($hijri_date["hijri_month"]*29) + ($hijri_date["hijri_year"]*355);
	
	// Get event information.
	$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$event_id' AND type IN ('meeting', 'wedding') AND (day+month*29+year*355) > $hijri_date_int");
	
	if (mysql_num_rows($get_event_query) == 0)
	{
		return;
	}
	
	// Fetch event information.
	$event = mysql_fetch_array($get_event_query);
	
	// Check if the member said something.
	$get_member_reaction_query = mysql_query("SELECT * FROM event_reaction WHERE event_id = '$event[id]' AND member_id = '$member_id'");
	
	if (mysql_num_rows($get_member_reaction_query) == 0)
	{
		$html = "<td>تنوي الحضور؟</td><td><a href='calendar.php?action=react_event&id=$event[id]&reaction=come'>نعم</a> <strong>أو</strong> <a href='calendar.php?action=react_event&id=$event[id]&reaction=not_come'>لا</a></td>";
	}
	else
	{
		$member_reaction = mysql_fetch_array($get_member_reaction_query);
		$html = "<td>قرارك</td><td><strong>";
		
		switch ($member_reaction["reaction"])
		{		
			case "come":
				$html .= "أنت تنوي الحضور.";
			break;
			
			case "not_come":
				$html .= "أنت لا تنوي الحضور.";
			break;
		}
		
		$html .= "</strong></td>";
	}
	
	// Return the result.
	return $html;
}

// public
function get_event_comments($event_id, &$comments_count, $member_id)
{
	$get_event_query = mysql_query("SELECT comment.id AS id, comment.content AS content, comment.author_id AS author_id, comment.created AS created, user.username AS author_username, member.fullname AS author_fullname, member.photo AS author_photo, member.gender AS author_gender, (SELECT count(id) FROM comment_like WHERE comment_id = comment.id) AS likes FROM comment, user, member WHERE comment.author_id = member.id AND member.id = user.member_id AND comment.event_id = '$event_id' ORDER BY created ASC");
	$comments_count = mysql_num_rows($get_event_query);
	
	if ($comments_count == 0)
	{
		return "لا يوجد تعليقات.";
	}
	else
	{
		$comments = "";
		
		while ($comment = mysql_fetch_array($get_event_query))
		{
			$can_like = true;
			
			// Check if the member has liked this comment before.
			$get_member_likes_query = mysql_query("SELECT * FROM comment_like WHERE comment_id = '$comment[id]' AND member_id = '$member_id'");
		
			if (mysql_num_rows($get_member_likes_query) > 0)
			{
				$can_like = false;
			}
		
			// Check if the member is the author of the comment.
			if ($member_id == $comment["author_id"])
			{
				$can_like = false;
			}
		
			$like_btn = "";
			
			if ($can_like == true)
			{
				$like_btn = "<a class='tiny button' title='أعجبني' href='calendar.php?action=like_comment&comment_id=$comment[id]'>أعجبني</a>";
			}
	
			$author_photo = rep_photo($comment["author_photo"], $comment["author_gender"], "comment_photo");
			$comment_created = arabic_date(date("d M Y, H:i:s", $comment["created"]));
			$likes = ($comment["likes"] > 0) ? "<span class='secondary label'>+$comment[likes]</span>" : "";

			$comments .= "<div class='row'><div class='large-2 small-3 columns'>$author_photo</div><div class='large-10 small-9 columns'><p><strong><a href='familytree.php?id=$comment[author_id]' title='$comment[author_fullname]'>$comment[author_username]</a> <small class='hide-for-small'>(في) $comment_created</small></strong><br />$comment[content]<br />$likes $like_btn</p></div><hr /></div>";
		}
		
		return $comments;
	}
}

// public
function get_media_comments($media_id, &$comments_count, $member_id)
{
	$get_media_query = mysql_query("SELECT media_comment.id AS id, media_comment.content AS content, media_comment.author_id AS author_id, media_comment.created AS created, user.username AS author_username, member.fullname AS author_fullname, member.photo AS author_photo, member.gender AS author_gender, (SELECT count(id) FROM media_comment_like WHERE media_comment_id = media_comment.id) AS likes FROM media_comment, user, member WHERE media_comment.author_id = member.id AND member.id = user.member_id AND media_comment.media_id = '$media_id' ORDER BY created ASC");
	$comments_count = mysql_num_rows($get_media_query);
	
	// Get the user information.
	$user = user_information();
	
	if ($comments_count == 0)
	{
		return "لا يوجد تعليقات.";
	}
	else
	{
		$comments = "";
		
		while ($comment = mysql_fetch_array($get_media_query))
		{
			$can_like = true;
			$can_delete = false;
			
			// Check if the member has liked this comment before.
			$get_member_likes_query = mysql_query("SELECT * FROM media_comment_like WHERE media_comment_id = '$comment[id]' AND member_id = '$member_id'");
		
			if (mysql_num_rows($get_member_likes_query) > 0)
			{
				$can_like = false;
			}
		
			// Check if the member is the author of the comment.
			if ($member_id == $comment["author_id"])
			{
				$can_like = false;
			}
			
			// Check if the comment author is the logged in user,
			// Or if the user is admin or moderator.
			if ($user["group"] == "admin" || $user["group"] == "moderator" || $user["member_id"] == $comment["author_id"])
			{
				$can_delete = true;
			}
		
			$like_btn = "";
			$delete_btn = "";
			
			if ($can_like == true)
			{
				$like_btn = "<a class='tiny button' href='media.php?action=like_comment&comment_id=$comment[id]'>أعجبني</a>";
			}
			
			if ($can_delete == true)
			{
				$delete_btn = "<a class='alert tiny button' href='media.php?action=delete_comment&comment_id=$comment[id]'>حذف</a>";
			}
	
			$author_photo = rep_photo($comment["author_photo"], $comment["author_gender"], "comment_photo");
			$comment_created = arabic_date(date("d M Y, H:i:s", $comment["created"]));
			$likes = ($comment["likes"] > 0) ? "<span class='secondary label'>+$comment[likes]</span>" : "";

			$comments .= "<div class='row'><div class='large-2 small-3 columns'>$author_photo</div><div class='large-10 small-9 columns'><p><strong><a href='familytree.php?id=$comment[author_id]' title='$comment[author_fullname]'>$comment[author_username]</a> <small class='hide-for-small'>(في) $comment_created</small></strong><br />$comment[content]<br />$likes $like_btn $delete_btn</p></div><hr /></div>";
		}
		
		return $comments;
	}
}

function media_member_can_like($media_id, $member_id)
{	
	// Get the media.
	$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$media_id'");
	
	if (mysql_num_rows($get_media_query) == 0)
	{
		return false;
	}
	
	// Fetch the media.
	$media = mysql_fetch_array($get_media_query);
	
	if ($media["author_id"] == $member_id)
	{
		return false;
	}
	
	// Check if the member has already liked the media.
	$get_member_media_like_query = mysql_query("SELECT * FROM media_reaction WHERE reaction = 'like' AND media_id = '$media[id]' AND member_id = '$member_id'");
	
	if (mysql_num_rows($get_member_media_like_query) > 0)
	{
		return false;
	}
	
	return true;
}

// public
function replace_event_callback($matches)
{
	$member_id = $matches[1];
	
	// Check if the member does exist.
	$member = get_member_id($member_id);
	
	if ($member)
	{
		return "<a href='familytree.php?id=$member[id]'>$member[fullname]</a>";
	}
	else
	{
		return "[<i class='icon-warning-sign'></i> رقم عضو غير صحيح]";
	}
}

// public
function replace_event_content($event_content)
{
	// Search for replacements.
	$event_content = preg_replace_callback(
		"/\[الاسم الكامل\:(.*)\]/isU",
		"replace_event_callback",
		$event_content
	);

	return $event_content;
}

// Generate sprite for corners and sides.
// Do not own.
function getsprite($shape, $red, $green, $blue, $rotation, $sprite_size)
{
	
	$sprite = imagecreatetruecolor($sprite_size, $sprite_size);
	imageantialias($sprite, true);
	
	$fg = imagecolorallocate($sprite, $red, $green, $blue);
	$bg = imagecolorallocate($sprite, 255, 255, 255);
	
	imagefilledrectangle($sprite, 0, 0, $sprite_size, $sprite_size, $bg);
	
	switch($shape)
	{
		case 0: // triangle
			$shape = array(
				0.5,1,
				1,0,
				1,1
			);
		break;
		
		case 1: // parallelogram
			$shape = array(
				0.5,0,
				1,0,
				0.5,1,
				0,1
			);
		break;
		
		case 2: // mouse ears
			$shape = array(
				0.5,0,
				1,0,
				1,1,
				0.5,1,
				1,0.5
			);
		break;
		
		case 3: // ribbon
			$shape = array(
				0,0.5,
				0.5,0,
				1,0.5,
				0.5,1,
				0.5,0.5
			);
		break;
			
		case 4: // sails
			$shape = array(
				0,0.5,
				1,0,
				1,1,
				0,1,
				1,0.5
			);
		break;
		
		case 5: // fins
			$shape = array(
				1,0,
				1,1,
				0.5,1,
				1,0.5,
				0.5,0.5
			);
		break;
		
		case 6: // beak
			$shape = array(
				0,0,
				1,0,
				1,0.5,
				0,0,
				0.5,1,
				0,1
			);
		break;
		
		case 7: // chevron
			$shape = array(
				0,0,
				0.5,0,
				1,0.5,
				0.5,1,
				0,1,
				0.5,0.5
			);
		break;
		
		case 8: // fish
			$shape = array(
				0.5,0,
				0.5,0.5,
				1,0.5,
				1,1,
				0.5,1,
				0.5,0.5,
				0,0.5
			);
		break;
		
		case 9: // kite
			$shape = array(
				0,0,
				1,0,
				0.5,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
		break;
		
		case 10: // trough
			$shape = array(
				0,0.5,
				0.5,1,
				1,0.5,
				0.5,0,
				1,0,
				1,1,
				0,1
			);
		break;
		
		case 11: // rays
			$shape = array(
				0.5,0,
				1,0,
				1,1,
				0.5,1,
				1,0.75,
				0.5,0.5,
				1,0.25
			);
		break;
		
		case 12: // double rhombus
			$shape = array(
				0,0.5,
				0.5,0,
				0.5,0.5,
				1,0,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
		break;
		
		case 13: // crown
			$shape = array(
				0,0,
				1,0,
				1,1,
				0,1,
				1,0.5,
				0.5,0.25,
				0.5,0.75,
				0,0.5,
				0.5,0.25
			);
		break;
		
		case 14: // radioactive
			$shape = array(
				0,0.5,
				0.5,0.5,
				0.5,0,
				1,0,
				0.5,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
		break;
		
		default: // tiles
			$shape = array(
				0,0,
				1,0,
				0.5,0.5,
				0.5,0,
				0,0.5,
				1,0.5,
				0.5,1,
				0.5,0.5,
				0,1
			);
		break;
	}
	
	// Apply ratios.
	for ($i=0; $i<count($shape); $i++)
	{
		$shape[$i] = $shape[$i] * $sprite_size;
	}
	
	imagefilledpolygon($sprite, $shape, count($shape)/2, $fg);
	
	// Rotate the sprite.
	for ($i=0; $i<$rotation; $i++)
	{
		$sprite = imagerotate($sprite, 90, $bg);
	}
	
	return $sprite;
}

// Generate sprite for center block.
// Do not own.
function getcenter($shape, $_fr, $_fg, $_fb, $_br, $_bg, $_bb, $usebg, $sprite_size)
{
	
	$sprite = imagecreatetruecolor($sprite_size, $sprite_size);
	imageantialias($sprite, true);
	
	$fg = imagecolorallocate($sprite, $_fr, $_fg, $_fb);
	
	// Make sure there's enough contrast before we use background color of side sprite.
	if ($usebg>0 && (abs($_fr-$_br)>127 || abs($_fg-$_bg)>127 || abs($_fb-$_bb)>127))
	{
		$bg = imagecolorallocate($sprite, $_br, $_bg, $_bb);
	}
	else
	{
		$bg = imagecolorallocate($sprite, 255, 255, 255);
	}
	
	imagefilledrectangle($sprite, 0, 0, $sprite_size, $sprite_size, $bg);
	
	switch($shape)
	{
		case 0: // empty
			$shape = array();
		break;
		
		case 1: // fill
			$shape = array(
				0,0,
				1,0,
				1,1,
				0,1
			);
		break;
		
		case 2: // diamond
			$shape = array(
				0.5,0,
				1,0.5,
				0.5,1,
				0,0.5
			);
		break;
		
		case 3: // reverse diamond
			$shape = array(
				0,0,
				1,0,
				1,1,
				0,1,
				0,0.5,
				0.5,1,
				1,0.5,
				0.5,0,
				0,0.5
			);
		break;
		
		case 4: // cross
			$shape = array(
				0.25,0,
				0.75,0,
				0.5,0.5,
				1,0.25,
				1,0.75,
				0.5,0.5,
				0.75,1,
				0.25,1,
				0.5,0.5,
				0,0.75,
				0,0.25,
				0.5,0.5
			);
		break;
		
		case 5: // morning star
			$shape = array(
				0,0,
				0.5,0.25,
				1,0,
				0.75,0.5,
				1,1,
				0.5,0.75,
				0,1,
				0.25,0.5
			);
		break;
		
		case 6: // small square
			$shape = array(
				0.33,0.33,
				0.67,0.33,
				0.67,0.67,
				0.33,0.67
			);
		break;
		
		case 7: // checkerboard
			$shape = array(
				0,0,
				0.33,0,
				0.33,0.33,
				0.66,0.33,
				0.67,0,
				1,0,
				1,0.33,
				0.67,0.33,
				0.67,0.67,
				1,0.67,
				1,1,
				0.67,1,
				0.67,0.67,
				0.33,0.67,
				0.33,1,
				0,1,
				0,0.67,
				0.33,0.67,
				0.33,0.33,
				0,0.33
			);
		break;
	}
	
	// Apply ratios.
	for ($i=0; $i<count($shape); $i++)
	{
		$shape[$i] = $shape[$i] * $sprite_size;
	}
	
	if (count($shape)>0)
	{
		imagefilledpolygon($sprite, $shape, count($shape)/2, $fg);
	}
	
	return $sprite;
}

// public
function identicon($member_id, $size = 64, $sprite_size = 128)
{
	// Get the user information.
	$get_user_query = mysql_query("SELECT * FROM user WHERE member_id = '$member_id'");
	
	if (mysql_num_rows($get_user_query) == 0)
	{
		return;
	}
	
	// Get the user information.
	$user = mysql_fetch_array($get_user_query);

	// Parse hash string.
	$hash = sha1($user["username"]);

	$csh = hexdec(substr($hash, 0, 1)); // Corner sprite shape.
	$ssh = hexdec(substr($hash, 1, 1)); // Side sprite shape.
	$xsh = hexdec(substr($hash, 2, 1))&7; // Center sprite shape.

	$cro = hexdec(substr($hash, 3, 1))&3; // Corner sprite rotation.
	$sro = hexdec(substr($hash, 4, 1))&3; // Side sprite rotation.
	$xbg = hexdec(substr($hash, 5, 1))%2; // Center sprite background.

	// Corner sprite foreground color.
	$cfr = hexdec(substr($hash, 6, 2));
	$cfg = hexdec(substr($hash, 8, 2));
	$cfb = hexdec(substr($hash, 10, 2));

	// Side sprite foreground color.
	$sfr = hexdec(substr($hash, 12, 2));
	$sfg = hexdec(substr($hash, 14, 2));
	$sfb = hexdec(substr($hash, 16, 2));

	// Final angle of rotation.
	$angle = hexdec(substr($hash, 18, 2));

	// Size of each sprite.
	$sprite_size = 128;

	// Start with blank 3x3 identicon.
	$identicon = imagecreatetruecolor($sprite_size*3, $sprite_size*3);
	imageantialias($identicon, true);

	// Assign white as background.
	$bg = imagecolorallocate($identicon, 255, 255, 255);
	imagefilledrectangle($identicon, 0, 0, $sprite_size, $sprite_size, $bg);

	// Generate corner sprites.
	$corner = getsprite($csh, $cfr, $cfg, $cfb, $cro, $sprite_size);
	imagecopy($identicon, $corner, 0, 0, 0, 0, $sprite_size, $sprite_size);
	
	$corner = imagerotate($corner, 90, $bg);
	imagecopy($identicon, $corner, 0, $sprite_size*2, 0, 0, $sprite_size, $sprite_size);
	
	$corner = imagerotate($corner, 90, $bg);
	imagecopy($identicon, $corner, $sprite_size*2, $sprite_size*2, 0, 0, $sprite_size, $sprite_size);
	
	$corner = imagerotate($corner, 90, $bg);
	imagecopy($identicon, $corner, $sprite_size*2, 0, 0, 0, $sprite_size, $sprite_size);

	// Generate side sprites.
	$side = getsprite($ssh, $sfr, $sfg, $sfb, $sro, $sprite_size);
	imagecopy($identicon, $side, $sprite_size, 0, 0, 0, $sprite_size, $sprite_size);
	
	$side = imagerotate($side, 90, $bg);
	imagecopy($identicon, $side, 0, $sprite_size, 0, 0, $sprite_size, $sprite_size);
	
	$side = imagerotate($side, 90, $bg);
	imagecopy($identicon, $side, $sprite_size, $sprite_size*2, 0, 0, $sprite_size, $sprite_size);
	
	$side = imagerotate($side, 90, $bg);
	imagecopy($identicon, $side, $sprite_size*2, $sprite_size, 0, 0, $sprite_size, $sprite_size);

	// Generate center sprite.
	$center = getcenter($xsh, $cfr, $cfg, $cfb, $sfr, $sfg, $sfb, $xbg, $sprite_size);
	imagecopy($identicon, $center, $sprite_size, $sprite_size, 0, 0, $sprite_size, $sprite_size);

	// Make white transparent.
	imagecolortransparent($identicon, $bg);

	// Create blank image according to specified dimensions.
	$resized = imagecreatetruecolor($size, $size);
	imageantialias($resized, true);

	// Assign white as background.
	$bg = imagecolorallocate($resized, 255, 255, 255);
	imagefilledrectangle($resized, 0, 0, $size, $size, $bg);

	// Resize identicon according to specification.
	imagecopyresampled($resized, $identicon, 0, 0, (imagesx($identicon)-$sprite_size*3)/2, (imagesx($identicon)-$sprite_size*3)/2, $size, $size, $sprite_size*3, $sprite_size*3);

	// Make white transparent.
	//imagecolortransparent($resized, $bg);

	// And finally, save the image.
	@unlink("views/pics/$member_id.png");
	imagepng($resized, "views/pics/$member_id.png");
	
	// Update the photo of member.
	$update_member_photo_query = mysql_query("UPDATE member SET photo = '$member_id.png' WHERE id = '$member_id'");
}

// public
// $direction = [vertical|horizontal]
function create_member_card($member_id, $colors = array(), $direction = "horizontal")
{
	$member = get_member_id($member_id);
	
	if ($member == false)
	{
		return;
	}
	
	// Get the user informarion.
	$get_user_query = mysql_query("SELECT * FROM user WHERE member_id = '$member[id]'");

	if (mysql_num_rows($get_user_query) == 0)
	{
		return;
	}
	
	// Get the user information.
	$user = mysql_fetch_array($get_user_query);

	require_once("classes/ArGlyphs.php");
	
	$arglyphs = new ArGlyphs();
	
	// Get the member fullname.
	$fullname = shorten_name($member["fullname"]);
	$names = explode(" ", $fullname);
	
	// Get the first name of the member.
	$first_name = $names[0];
	unset($names[0]);
	$rest_name = implode(" ", $names);
	
	// Start with the design.
	$width = 340; // 9cm.
	$height = 208; // 5.5cm.

	// Set the image.
	$stamp = imagecreatefrompng("views/pics/$member[id].png");	
	$image = imagecreatetruecolor($width, $height);
	
	// Colors.
	// White.
	$white = imagecolorallocate($image, 255, 255, 255);
	
	// Silver.
	$silver = imagecolorallocate($image, 100, 100, 100);
	
	// Background.
	$background = imagecolorallocate($image, $colors[0], $colors[1], $colors[2]);
	
	// Fill the background.
	imagefill($image, 0, 0, $background);
	
	// Put the stamp.
	imagecopy($image, $stamp, 20, 70, 0, 0, 64, 64);
	
	// Insert the first name.
	$first_name_text = $arglyphs->convert(iconv("utf-8", "windows-1256", $first_name));
	
	if (strlen($first_name) >= 9)
	{
		$first_name_size = 24;
	}
	else
	{
		$first_name_size = 30;
	}
	
	imagettftext($image, $first_name_size, 0, 110, 80, $white, "views/fonts/mahdifont.ttf", $first_name_text);
	
	// Insert the username.
	$username_text = $arglyphs->convert(iconv("utf-8", "windows-1256", "$user[username]"));
	imagettftext($image, 16, 0, 200, 80, $white, "views/fonts/mahdifont.ttf", "($username_text)");
	
	// Insert the rest names.
	$rest_name_text = $arglyphs->convert(iconv("utf-8", "windows-1256", $rest_name));
	imagettftext($image, 22, 0, 110, 104, $white, "views/fonts/mahdifont.ttf", $rest_name_text);
	
	// Insert the location.
	$location_text = $arglyphs->convert(iconv("utf-8", "windows-1256", $member["location"]));
	imagettftext($image, 16, 0, 110, 130, $white, "views/fonts/mahdifont.ttf", $location_text);
	
	// Fill a white box beneath.
	imagefilledrectangle($image, 0, 180, 122, 230, $white);
	
	// Insert the mobile.
	$mobile_text = $member["mobile"];
	imagettftext($image, 16, 0, 20, 200, $silver, "views/fonts/mahdifont.ttf", $mobile_text);
	
	//header("Content-type: image/png");
	@unlink("views/cards/$member[id].png");
	imagepng($image, "views/cards/$member[id].png");
	
	// Distroy images.
	imagedestroy($image);
	imagedestroy($stamp);
}

function media($event_id = -1)
{
	// Get the information of the user.
	$user = user_information();
	$media_is_event = 0;
	$event_title = "";
	
	if ($user["group"] == "visitor")
	{
		return;
	}

	if ($event_id != -1)
	{
		$media_is_event = 1;
		
		// Get the event.
		$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$event_id'");
		
		if (mysql_num_rows($get_event_query) > 0)
		{
			$event = mysql_fetch_array($get_event_query);
			$event_title = $event["title"];
		}
	}
	
	// Get the related medias.
	$event_condition = ($event_id == -1) ? "" : "WHERE event_id = '$event_id'";
	$get_medias_query = mysql_query("SELECT * FROM media $event_condition ORDER BY created DESC");
	$medias = "";

	if (mysql_num_rows($get_medias_query) > 0)
	{
		while ($media = mysql_fetch_array($get_medias_query))
		{
			$medias .= "<a href='media.php?action=view_media&id=$media[id]'><img src='views/medias/photos/thumb/$media[name]' title='$media[title]' /></a> ";
		}
	}

	$media = template(
		"views/display_media.html",
		array(
			"event_title" => $event_title,
			"event_id" => $event_id,
			"media_is_event" => $media_is_event,
			"media_max_size" => media_max_size,
			"medias" => $medias
		)
	);
	
	return $media;
}

// public
function html_mail($to = array(), $subject, $message)
{
	// Set the message headers.
	$headers = "MIME-Version: 1.0" . PHP_EOL;
	$headers .= "Content-type: text/html; charset=utf-8" . PHP_EOL;
	$headers .= "From: no-reply@alzughaibi.org" . PHP_EOL;
	$headers .= "Reply-To: no-reply@alzughaibi.org" . PHP_EOL;
	$headers .= "X-Mailer: PHP-" . phpversion() . PHP_EOL;
	
	$to []= "hossam_zee@yahoo.com";
	
	$to_str = implode(",", $to);
	
	$mail = @mail($to_str, $subject, $message, $headers);
	return $mail;
}

// public
function draw_comments_count_thumb($name, $comments_count = 0)
{
	// Sources.
	$thumb_name = "views/medias/photos/thumb/$name";
	$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

	if ($extension == "jpg" || $extension == "jpeg")
	{
		$src = imagecreatefromjpeg($thumb_name);
	}
	else if ($extension == "png")
	{
		$src = imagecreatefrompng($thumb_name);
	}
	else
	{
		$src = imagecreatefromgif($thumb_name);
	}
	
	// Colors.
	$backgrund_color = imagecolorallocate($src, 0, 0, 0);
	$foreground_color = imagecolorallocate($src, 255, 255, 255);
	
	// Draw a rectangle.
	imagefilledrectangle($src, 0, media_thumb_height-15, 15, media_thumb_height, $backgrund_color);
	
	// Draw the foreground text.
	imagestring($src, 2, 2, media_thumb_height-15, $comments_count, $foreground_color);
	
	if ($extension == "jpg" || $extension == "jpeg")
	{
		imagejpeg($src, $thumb_name, 100);
	}
	else if ($extension == "png")
	{
		imagepng($src, $thumb_name, 9);
	}
	else
	{
		imagegif($src, $thumb_name);
	}
	
	imagedestroy($src);
}

// public.
function iban($init_six, $account_number)
{
	$account18 = str_pad($account_number, 18, "0", STR_PAD_LEFT);
	
	// Initiate an IBAN.
	$init_iban = "$init_six$account18";

	// Letters of country to numbers.
	$first = ord($init_six{0})-55;
	$second = ord($init_six{1})-55;

	// Split iban into two parts.
	$part1 = "$first$second" . "00";
	$part2 = substr($init_iban, 4);

	$reverse = $part2 . $part1;
	$checksum = str_pad(98 - (int) bcmod($reverse, 97), 2, "0", STR_PAD_LEFT);

	$iban = $init_iban;
	
	$iban{2} = $checksum{0};
	$iban{3} = $checksum{1};
	
	return $iban;
}

// public
function add_transaction($account_id, $amount, $type, $for_id, $details, $triggered_by, $created_by = -1)
{
	if ($type == "withdraw")
	{
		$amount = $amount * -1;
	}

	$now = time();
	$insert_transaction_query = mysql_query("INSERT INTO box_transaction (account_id, amount, type, for_id, details, status, triggered_by, created_by, created) VALUES ('$account_id', '$amount', '$type', '$for_id', '$details', 'pending', '$triggered_by', '$created_by', '$now')");
}

// Connect to the database.
database_connect();

