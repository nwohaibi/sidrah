<?php

// COMMITTEES.PHP

require_once("inc/functions.inc.php");

$user = user_information();

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{	
	case "add_committee":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		$name = trim(mysql_real_escape_string(@$_POST["name"]));
		$minimum_age = trim(mysql_real_escape_string(@$_POST["minimum_age"]));
		$priority = trim(mysql_real_escape_string(@$_POST["priority"]));
		$tasks = trim(mysql_real_escape_string(@$_POST["tasks"]));
		$members_description = trim(mysql_real_escape_string(@$_POST["members_description"]));
		$head_name = trim(mysql_real_escape_string(@$_POST["head_name"]));
		$keywords = trim(mysql_real_escape_string(@$_POST["keywords"]));

		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (empty($name) || empty($priority) || empty($tasks) || empty($members_description) || empty($head_name) || empty($keywords))
		{
			echo error_message("الرجاء تعبئة الحقول المطلوبة.");
			return;
		}
		
		// Get the id of the member.
		$head_member = get_member_fullname($head_name);

		if ($head_member == false)
		{
			echo error_message("لم يتم العثور على العضو.");
			return;
		}
		
		// Insert the committee.
		$now = time();
		$insert_committee_query = mysql_query("INSERT committee (name, tasks, members_description, keywords, minimum_age, priority, created) VALUES ('$name', '$tasks', '$members_description', '$keywords', '$minimum_age', '$priority', '$now')");
		$committee_id = mysql_insert_id();

		// Insert the head member to be a head of the committe.
		$insert_head_member = mysql_query("INSERT INTO member_committee (member_id, committee_id, status, member_title, joined) VALUES ('$head_member[id]', '$committee_id', 'accepted', 'head', '$now')");

		echo success_message(
			"تم إضافة اللجنة بنجاح.",
			"manage_committees.php"
		);
	break;
	
	case "update_committees":

		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$do = mysql_real_escape_string(@$_POST["do"]);
		$check = @$_POST["check"];
		$priority = @$_POST["priority"];

		// Array to hold committees.
		$committees = array();
		
		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (!isset($check))
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Everything is good, almost.
		// Check if there is a committee priority empty.
		if (count($check) > 0)
		{
			foreach ($check as $k => $v)
			{
				$_priority = trim(mysql_real_escape_string(@$priority[$k]));
				
				if (empty($_priority))
				{
					echo error_message("الرجاء إدخال ترتيب الظهور لجميع اللجان.");
					return;
				}

				$committees[$k] = $_priority;
			}
		}

		if ($do == "update_order")
		{			
			foreach ($committees as $id => $priority)
			{
				$update_query = mysql_query("UPDATE committee SET priority = '$priority' WHERE id = '$id'");
			}
		}
		else if ($do == "delete")
		{
			// Remove all members related to this committee.
			foreach ($committees as $id => $priority)
			{
				$delete_committee_query = mysql_query("DELETE FROM committee WHERE id = '$id'");
				$delete_member_committee_query = mysql_query("DELETE FROM member_committee WHERE  committee_id = '$id'");
			}
		}

		echo success_message(
			"تم تحديث اللجان بنجاح.",
			"manage_committees.php"
		);
		
		return;
	
	break;
	
	case "edit_committee":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the committee does exist.
		$get_committee_query = mysql_query("SELECT * FROM committee WHERE id = '$id'");
		
		if (mysql_num_rows($get_committee_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (!empty($submit))
		{
			$name = trim(mysql_real_escape_string(@$_POST["name"]));
			$minimum_age = trim(mysql_real_escape_string(@$_POST["minimum_age"]));
			$priority = trim(mysql_real_escape_string(@$_POST["priority"]));
			$tasks = trim(mysql_real_escape_string(@$_POST["tasks"]));
			$members_description = trim(mysql_real_escape_string(@$_POST["members_description"]));
			$keywords = trim(mysql_real_escape_string(@$_POST["keywords"]));

			if (empty($submit))
			{
				echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
				return;
			}
		
			if (empty($name) || empty($priority) || empty($tasks) || empty($members_description) || empty($keywords))
			{
				echo error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			// Everything is sweet, do some update.
			$update_committee_query = mysql_query("UPDATE committee SET name = '$name', minimum_age = '$minimum_age', priority = '$priority', tasks = '$tasks', members_description = '$members_description', keywords = '$keywords' WHERE id = '$id'");
			
			echo success_message(
				"تم تعديل اللجنة بنجاح.",
				"manage_committees.php"
			);
		}
		else
		{
			// Get the committee details.
			$committee = mysql_fetch_array($get_committee_query);
		
			// Get the template and do some replacements.
			$content = template(
				"views/edit_committee.html",
				array(
					"id" => $committee["id"],
					"name" => $committee["name"],
					"minimum_age" => $committee["minimum_age"],
					"priority" => $committee["priority"],
					"tasks" => $committee["tasks"],
					"members_description" => $committee["members_description"],
					"keywords" => $committee["keywords"]
				)
			);
			
			// Get the header
			$header = website_header(
				$committee["name"],
				"صفحة من أجل تعديل $committee[name]",
				array(
					"عائلة", "الزغيبي", "تعديل", "لجنة"
				)
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}

	break;
	
	case "manage_committee_members":
		
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the committee does exist.
		$get_committee_query = mysql_query("SELECT * FROM committee WHERE id = '$id'");
		
		if (mysql_num_rows($get_committee_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (!empty($submit))
		{
			$do = mysql_real_escape_string(@$_POST["do"]);
			$check = @$_POST["check"];
			$member_title = @$_POST["member_title"];
			
			// Array to hold new member_committee ids.
			$member_committees = array();
			
			if (!isset($check))
			{
				echo error_message("الرجاء اختيار خيار واحد على الأقل.");
				return;
			}
			
			// Everything is good, almost.
			if (count($check) > 0)
			{
				foreach ($check as $k => $v)
				{
					$member_committees[$k] = $member_title[$k];
				}
			}
			
			if ($do == "update")
			{
				foreach ($member_committees as $member_committee_id => $member_title)
				{
					$update_query = mysql_query("UPDATE member_committee SET member_title = '$member_title' WHERE id = '$member_committee_id'");
				}
			}
			
			if ($do == "delete")
			{
				foreach ($member_committees as $member_committee_id => $member_title)
				{
					$delete_query = mysql_query("DELETE FROM member_committee WHERE id = '$member_committee_id'");
				}
			}
			
			// Done.
			echo success_message(
			"تم تحديث أعضاء اللجنة بنجاح.",
				"manage_committees.php?action=manage_committee_members&id=$id"
			);
		
			return;
		}
		else
		{
			// Get the committee.
			$committee = mysql_fetch_array($get_committee_query);
		
			// Get the members of the committee (if any).
			$get_committee_members_query = mysql_query("SELECT member_committee.id AS id, member.id AS member_id, member.fullname AS member_fullname, member.gender AS gender, member_committee.member_title AS member_title FROM member_committee, member WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$id'")or die(mysql_error());
			$committee_members_html = "";
			
			if (mysql_num_rows($get_committee_members_query) == 0)
			{
				$committee_members_html = "<tr><td colspan='4' class='error'><i class='icon-exclamation-sign'></i> لا يوجد أعضاء بعد.</td></tr>";
			}
			else
			{
				while ($committee_member = mysql_fetch_array($get_committee_members_query))
				{
					$gender_icon = ($committee_member["gender"] == 1) ? "male.png" : "female.png";
					$member_selected = ($committee_member["member_title"] == "member") ? "selected='selected'" : "";
					$head_selected = ($committee_member["member_title"] == "head") ? "selected='selected'" : "";
					
					$committee_members_html .= "<tr><td><input type='checkbox' name='check[$committee_member[id]]' id='check_$committee_member[id]' /></td><td><img src='views/img/$gender_icon' /></td><td><a href='familytree.php?id=$committee_member[member_id]'>$committee_member[member_fullname]</a></td><td><select name='member_title[$committee_member[id]]'><option value='member' $member_selected>عضو</option><option value='head' $head_selected>رئيس</option></select></td></tr>";
				}
			}
		
			// Get the content.
			$content = template(
				"views/manage_committee_members.html",
				array(
					"committee_id" => $committee["id"],
					"committee_name" => $committee["name"],
					"committee_members" => $committee_members_html
				)
			);
			
			// Get the header.
			$header = website_header(
				"إدارة أعضاء $committee[name]",
				"صفحة من أجل إدارة أعضاء $committee[name]",
				array(
					"عائلة", "الزغيبي", "إدارة", "أعضاء", $committee["name"]
				)
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "suggest_members":
		$committee_id = mysql_real_escape_string(@$_GET["committee_id"]);
		
		// Check if the committee is correct.
		$get_committee_id_query = mysql_query("SELECT * FROM committee WHERE id = '$committee_id'");
		
		if (mysql_num_rows($get_committee_id_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Everything is cool,
		// Get the committee information.
		$one_committee = mysql_fetch_array($get_committee_id_query);
		
		// Get the keywords
		$keywords = explode("،", $one_committee["keywords"]);
		$keywords_count = count($keywords);
		
		// Set the query.
		$major_array = array();
		$job_array = array();
		
		// Make a clean query, but before, loop to empty whitespace.
		for ($i=0; $i<$keywords_count; $i++)
		{
			$keywords[$i] = trim($keywords[$i]);
			$major_array []= "major LIKE '%$keywords[$i]%'";
			$job_array []= "job_title LIKE '%$keywords[$i]%'";
		}
		
		// Build the (where) query.
		$where_query = sprintf("(%s) OR (%s)", implode(" OR ", $major_array), implode(" OR ", $job_array));
		
		// Execute the query.
		// TODO: For now, only display men
		$get_suggested_members_query = mysql_query("SELECT id, fullname, age, major, job_title, location FROM member WHERE ($where_query) AND (gender = 1 AND age >= $one_committee[minimum_age])");
		$suggested_members_count = mysql_num_rows($get_suggested_members_query);

		if ($suggested_members_count == 0)
		{
			echo error_message("لم يتم العثور على أسماء مقترحة لهذه اللجنة.");
			return;
		}
		
		$suggested_members_html = "";
		
		// Start walking up-on the names.
		while ($suggested_member = mysql_fetch_array($get_suggested_members_query))
		{
			$major = highlight_strings($suggested_member["major"], $keywords);
			$job_title = highlight_strings($suggested_member["job_title"], $keywords);
			
			$suggested_members_html .= "<tr><td><input type='checkbox' name='check[$suggested_member[id]]' checked /></td><td><a href='familytree.php?id=$suggested_member[id]' target='_blank'>$suggested_member[fullname]</a></td><td>$suggested_member[age]</td><td>$major</td><td>$job_title</td><td>$suggested_member[location]</td></tr>";
		}
		
		// Get the content.
		$content = template(
			"views/committee_suggested_members.html",
			array(
				"suggested_members" => $suggested_members_html,
				"committee_id" => $committee_id
			)
		);
		
		// Get the header.
		$header = website_header(
			"أعضاء مقترحون ($suggested_members_count)",
			"صفحة من أجل عرض أعضاء مقترحون للانضمام إلى $one_committee[name]",
			array(
				"عائلة", "الزغيبي", "أعضاء", "مقترحون", "اللجنة", $one_committee["name"]
			)
		);
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "tell_suggested_members":
	
		$committee_id = mysql_real_escape_string(@$_GET["committee_id"]);
		$send_sms = mysql_real_escape_string(@$_POST["send_sms"]);
		$check = @$_POST["check"];
		
		if (!isset($check))
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Check if the [check] is empty.
		if (count($check) == 0)
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Check if the committee is correct.
		$get_committee_id_query = mysql_query("SELECT * FROM committee WHERE id = '$committee_id'");
		
		if (mysql_num_rows($get_committee_id_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Everything is cool,
		// Get the committee information.
		$one_committee = mysql_fetch_array($get_committee_id_query);
		$total_nominees = 0;
		
		// Start notifying everyone.
		foreach ($check as $member_id => $value)
		{
			// Check if the member is already in the committee.
			$get_member_committee_query = mysql_query("SELECT * FROM member_committee WHERE committee_id = '$one_committee[id]' AND member_id = '$member_id'");
		
			if (mysql_num_rows($get_member_committee_query) == 0)
			{
				$get_user_id_query = mysql_query("SELECT id FROM user WHERE member_id = '$member_id'")or die(mysql_error());
				$get_user_id_fetch = mysql_fetch_array($get_user_id_query);
				
				// Add the member to the committee to be a nominee.
				$insert_nominee_committee_query = mysql_query("INSERT INTO member_committee (member_id, committee_id, status, member_title) VALUES ('$member_id', '$one_committee[id]', 'nominee', 'member')");
				
				// Notify the member.
				notify("committee_nominee", $get_user_id_fetch["id"], "تم ترشيحك للإنضمام إلى $one_committee[name].", "committees.php?do=view_committee&id=$one_committee[id]");
				
				// Send an SMS to the member if checked.
				if (!empty($send_sms))
				{
					$member_info = get_member_id($member_id);
					$mobile = $member_info["mobile"];
					
					if ($mobile != "0")
					{
						send_sms(
							array("966" . $mobile),
							"تهانينا! لقد تم ترشيحك للانضمام إلى $one_committee[name]، تفضل بالدخول بعضويتك إلى موقع الزغيبي لمزيد من التفاصيل."
						);
					}
				}
				
				$total_nominees++;
			}
		}
		
		if ($total_nominees == 0)
		{
			echo error_message("لم يتم ترشيح أي عضو للانضمام إلى $one_committee[name].");
			return;
		}
		else
		{
			echo success_message(
				"تم ترشيح و إبلاغ $total_nominees عضو للانضمام إلى $one_committee[name]",
				"manage_committees.php"
			);
		}
	break;
	
	default: case "view_committees":
	
		$committees_html = "";
		$get_committees_query = mysql_query("SELECT * FROM committee ORDER BY priority ASC");
		
		if (mysql_num_rows($get_committees_query) == 0)
		{
			$committees_html = "<tr><td colspan='5' class='error'><i class='icon-exclamation-sign'></i> لم يتم إضافة لجان بعد.</td></tr>";
		}
		else
		{
			while ($committee = mysql_fetch_array($get_committees_query))
			{
				// Get the count of members for this committee.
				$committee_members_query = mysql_query("SELECT count(id) AS members FROM member_committee WHERE committee_id = '$committee[id]'");
				$committee_members_fetch = mysql_fetch_array($committee_members_query);
				$committee_members = $committee_members_fetch["members"];
				
				// Get the head of the committee.
				$get_committee_head_query = mysql_query("SELECT member.fullname AS fullname, member.id AS id FROM member, member_committee WHERE member_committee.member_id = member.id AND member_committee.member_title = 'head' AND member_committee.committee_id = '$committee[id]'");
				$get_committee_head_fetch = mysql_fetch_array($get_committee_head_query);
				
				$committee_head_name = shorten_name($get_committee_head_fetch["fullname"]);
				
				$committees_html .= "<tr><td><input type='checkbox' id='check_$committee[id]' name='check[$committee[id]]'></td><td><input type='text' name='priority[$committee[id]]' value='$committee[priority]' size='1' /></td><td><a href='committees.php?do=view_committee&id=$committee[id]'>$committee[name]</a></td><td>($committee_head_name)</td><td><center><a href='manage_committees.php?action=manage_committee_members&id=$committee[id]'>$committee_members</a></center></td><td>$committee[keywords]</td><td><a href='manage_committees.php?action=edit_committee&id=$committee[id]' class='sidrah_btn'><i class='icon-pencil'></i> تعديل</a> <a class='sidrah_btn positive' href='manage_committees.php?action=suggest_members&committee_id=$committee[id]'><i class='icon-thumbs-up icon-white'></i> اقترح أعضاء</a></td></tr>";
			}
		}

		// Get the header.
		$header = website_header(
			"إدارة اللجان",
			"صفحة من أجل إدارة اللجان",
			array(
				"عائلة", "الزغيبي", "إدارة", "اللجان"
			)
		);

		// Get the template of the page.
		$template = template(
				"views/manage_committees.html",
				array(
					"committees" => $committees_html
				)
		);
		
		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $template;
		echo $footer;
	break;
}

