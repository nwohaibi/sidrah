<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();
$do = mysql_real_escape_string(@$_GET["do"]);

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Get the member information.
$member = get_member_id($user["member_id"]);

switch ($do)
{
	default: case "view_committees":

		// Get the committees.
		$get_committees_query = mysql_query("SELECT * FROM committee ORDER BY priority ASC");
		$committees_html = "";

		if (mysql_num_rows($get_committees_query) > 0)
		{
			while ($committee = mysql_fetch_array($get_committees_query))
			{
				// Get the count of members for this committee.
				$committee_members_query = mysql_query("SELECT count(id) AS members FROM member_committee WHERE committee_id = '$committee[id]' AND member_title != 'head' AND status = 'accepted'");
				$committee_members_fetch = mysql_fetch_array($committee_members_query);
				$committee_members = ($committee_members_fetch["members"] > 0) ? "$committee_members_fetch[members] عضواً" : "لا أعضاء";
				
				$committees_html .= "<div class='one_cockpit'><h3><i class='icon-leaf'></i> <a href='committees.php?do=view_committee&id=$committee[id]'>$committee[name]</a> <span>($committee_members)</span></h3></div>";
			}
		}

		// Get the content.
		$content = template(
			"views/committees.html",
			array(
				"committees" => $committees_html
			)
		);

		// Get the header.
		$header = website_header(
			"اللجان",
			"صفحة من أجل عرض اللجان.",
			array(
				"عائلة", "الزغيبي", "اللجان"
			)
		);

		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;

	break;
	
	case "view_committee":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the committee does exist.
		$get_committee_query = mysql_query("SELECT * FROM committee WHERE id = '$id'");
		
		if (mysql_num_rows($get_committee_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the committee details.
		$committee = mysql_fetch_array($get_committee_query);
		$join_committee = "";
		
		// Get the members of the committee.
		// Check if the member is older or equal [minimum_age].
		if ($member["age"] >= $committee["minimum_age"] && $member["gender"] == 1)
		{
			// Check if the user has not updated information.
			if ($user["first_login"] == 1)
			{
				$update_profile = "update_profile_male.php?id=$member[id]";
				$join_committee = "<p><span><i class='icon-exclamation-sign'></i> يجب <a href='$update_profile'>تحديث البيانات الأساسية</a> لتتمكن من الانضمام إلى اللجنة.</span></p>";
			}
			else
			{
				// Check if the member is already a member in this committee.
				$already_member_committee = mysql_query("SELECT id, status, member_title FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$committee[id]'");

				if (mysql_num_rows($already_member_committee) > 0)
				{
					$one_member_committee = mysql_fetch_array($already_member_committee);
					
					switch ($one_member_committee["status"])
					{
						case "pending":
							$join_committee = "<p><span class='notice_string'><i class='icon-time'></i> يجري معالجة طلبك للانضمام إلى هذه اللجنة.</span></p>";
						break;
						
						case "accepted":
							$join_committee = "<p><span class='notice_string'><i class='icon-user'></i> أنت عضو في هذه اللجنة.</span></p>";
						break;
						
						case "rejected":
						
						break;
						
						case "nominee":
							$join_committee  = "<div class='notice_string'><p><i class='icon-star'></i> أنت مرشح للانضمام إلى هذه اللجنة، فهل ترغب في ذلك؟</p>";
							$join_committee .= "<p><a class='zoghiby_btn positive' href='committees.php?do=decide_join_reject&committee_id=$committee[id]&decision=accept' id='accept'><i class='icon-ok icon-white'></i> نعم</a> <a class='zoghiby_btn negative href='#reject' onclick='reject();'><i class='icon-remove icon-white'></i> لا</a> <input type='text' id='reason' placeholder='بسبب...' onkeyup='write_reason()' onkeydown='write_reason()' onkeypress='write_reason()' /></p></div>";
						break;
					}
					
					// Add a button for managing the committee.
					if ($one_member_committee["member_title"] == "head")
					{
						$join_committee .= "<p><a href='request_committee.php' class='zoghiby_btn positive'><i class='icon-cog icon-white'></i> إدارة أعضاء اللجنة</a></p>";
					}
				}
				else
				{
					$join_committee = "<p><span><a class='zoghiby_btn positive' href='committees.php?do=join_request_committee&id=$committee[id]'><i class='icon-ok-circle icon-white'></i> طلب الانضمام إلى هذه اللجنة</a></span></p>";
				}
			}
		}

		// Get the (male) members of the committee.
		$get_committee_members = mysql_query("SELECT member.id AS member_id, member.fullname AS member_fullname, member_committee.member_title AS member_title FROM member, member_committee WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$committee[id]' AND member.gender = '1' AND member_committee.member_title != 'head' AND member_committee.status = 'accepted'");
		$committee_members_html = "";

		if (mysql_num_rows($get_committee_members) == 0)
		{
			$committee_members_html = "<tr><td colspan='2' class='error'><i class='icon-exclamation-sign'></i> لا يوجد أعضاء بعد.</td></tr>";
		}
		else
		{
			while ($committee_member = mysql_fetch_array($get_committee_members))
			{
				$group = ($committee_member["member_title"] == "member") ? "عضو" : "رئيس";
				$committee_members_html .= "<tr><td><a href='familytree.php?id=$committee_member[member_id]'>$committee_member[member_fullname]</a></td><td>($group)</td></tr>";
			}
		}

		// Set committee members count.
		$committee_members_count = mysql_num_rows($get_committee_members);
		$committee_members_string = ($committee_members_count > 0) ? "($committee_members_count)" : "";

		$committee_html  = "$join_committee<br /><table class='table'><thead><tr><th>أعضاء اللجنة $committee_members_string</th><th></th></tr></thead><tbody>";
		$committee_html .= $committee_members_html;
		$committee_html .= "</tbody></table>";
		
		$content = template(
			"views/committee.html",
			array(
				"tasks" => parse_string_or_list($committee["tasks"]),
				"members_description" => parse_string_or_list($committee["members_description"]),
				"committee_members" => $committee_html,
				"committee_id" => $committee["id"]
			)
		);
		
		// Get the header.
		$header = website_header(
			$committee["name"],
			"صفحة من أجل عرض $committee[name].",
			array(
				"عائلة", "الزغيبي", $committee["name"]
			)
		);

		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "decide_join_reject":
	
		$committee_id = mysql_real_escape_string(@$_GET["committee_id"]);
		$decision = mysql_real_escape_string(@$_GET["decision"]);
		$reason = trim(mysql_real_escape_string(@$_GET["reason"]));
		
		// Check if the committee id is correct and member id too.
		$get_nominee_member_query = mysql_query("SELECT * FROM member_committee WHERE committee_id = '$committee_id' AND member_id = '$member[id]' AND status = 'nominee'");
		
		if (mysql_num_rows($get_nominee_member_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if ($decision != "accept")
		{
			// Check the reason if it is empty.
			if (empty($reason))
			{
				echo error_message("الرجاء إدخال سبب رفضك الانضمام إلى اللجنة.");
				return;
			}
		}
		
		// Set the decision to be either: accepted, rejected
		$decision = ($decision == "accept") ? "accepted" : "rejected";
		
		// Update the member_committee.
		$now = time();
		$update_member_committee_query = mysql_query("UPDATE member_committee SET status = '$decision', joined = '$now' WHERE committee_id = '$committee_id' AND member_id = '$member[id]'");
		
		if ($decision == "accepted")
		{
			$message = "شكراً لك، تم انضمامك إلى اللجنة بنجاح.";
		}
		else
		{
			$message = "تم رفض طلبك بناءاً على طلبك، نتمنى لك التوفيق.";
		}
		
		echo success_message(
			$message,
			"committees.php?do=view_committee&id=$committee_id"
		);
		
		return;
	break;
	
	case "join_request_committee":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
	
		// Check if the id of the committee is correct.
		$get_committee_query = mysql_query("SELECT * FROM committee WHERE id = '$id'");
		
		if (mysql_num_rows($get_committee_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the committee information.
		$committee = mysql_fetch_array($get_committee_query);
		
		// Check if the user is not appropriate.
		if (($committee["minimum_age"] != 0 && $member["age"] < $committee["minimum_age"]) || $member["gender"] == 0)
		{
			echo error_message("العمر الأدنى للانضمام إلى هذه اللجنة هو $committee[minimum_age] سنة.");
			return;
		}
		
		// Check if the member is already a member of this committee.
		$get_member_committee_query = mysql_query("SELECT id FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$committee[id]'");
		
		if (mysql_num_rows($get_member_committee_query) > 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			// Insert the (pending) member to be a member of the committee.
			$insert_member_committee_query = mysql_query("INSERT INTO member_committee (member_id, committee_id, status, member_title) VALUES ('$member[id]', '$committee[id]', 'pending', 'member')");

			// Notify the user.
			$ok_message = "تم استلام طلبك للانضمام إلى اللجنة، و ستتم معالجته و إبلاغك خلال مدّة لا تتجاوز 24 ساعة.";
			notify("committee_join_request_receive", $user["id"], $ok_message, "committees.php?do=view_committee&id=$committee[id]");
			
			echo success_message(
				"تم ارسال طلبك للانضمام إلى اللجنة بنجاح.",
				"committees.php?do=view_committee&id=$committee[id]"
			);
		}
	break;
}
