<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();
$action = mysql_real_escape_string(@$_GET["action"]);

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Get the member information.
$member = get_member_id($user["member_id"]);

// Start switching between actions.
switch ($action)
{
	default: case "view_current_dean":
	
		// Get the current deanship period.
		$deanship_html = "";
		$get_current_deanship_period = mysql_query("SELECT * FROM deanship_period WHERE status != 'finished' ORDER BY id DESC LIMIT 1");

		if (mysql_num_rows($get_current_deanship_period) > 0)
		{
			$current_deanship_period = mysql_fetch_array($get_current_deanship_period);
	
			switch ($current_deanship_period["status"])
			{
				case "nomination":
			
					$nominate_yourself = "";
			
					// Check if member is applicable to be a dean.
					if (is_accepted_dean($member["id"]))
					{
						// Check if the member has already nominated himself.
						$member_already_nominated_query = mysql_query("SELECT id FROM dean WHERE period_id = '$current_deanship_period[id]' AND member_id = '$member[id]'");
				
						if (mysql_num_rows($member_already_nominated_query) == 0)
						{
							$nominate_yourself = "<a href='family_dean.php?action=nominate_yourself&period_id=$current_deanship_period[id]' class='white_button'>رشّح نفسك لعمادة عائلة الزغيبي.</a>";
						}
						else
						{
							$member_already_nominated = mysql_fetch_array($member_already_nominated_query);
							$nominate_yourself = "<span class='family_dean_nominated'>لقد قمت بترشيح نفسك إلى عمادة عائلة الزغيبي <i class='icon-star'></i> <a href='family_dean.php?action=view_dean_info&id=$member_already_nominated[id]'>تفاصيل أكثر</a></span>";
						}
					}
			
					// Get the main template.
					$deanship_html = template(
						"views/family_dean_nomination_box.html",
						array(
							"nominate_yourself" => $nominate_yourself,
							"from" => $current_deanship_period["from_period"],
							"to" => $current_deanship_period["to_period"]
						)
					);
				break;
				
				case "voting":
				
					// Check if the member has voted.
					$has_member_voted_query = mysql_query("SELECT * FROM member_dean WHERE member_id = '$member[id]' AND dean_id IN (SELECT id FROM dean WHERE period_id = '$current_deanship_period[id]')");
					$vote_html = "";
					
					if (mysql_num_rows($has_member_voted_query) > 0)
					{
						$fetch_voted_dean = mysql_fetch_array($has_member_voted_query);
						$get_dean_information_query = mysql_query("SELECT * FROM dean WHERE id = '$fetch_voted_dean[dean_id]'");
						$fetch_dean_information = mysql_fetch_array($get_dean_information_query);
						$voted_dean = get_member_id($fetch_dean_information["member_id"]);
						$voted_dean_shortname = shorten_name($voted_dean["fullname"]);
						
						$vote_html = "<span class='family_dean_nominated'>لقد قمت بالتصويت للمرشح <i class='icon-star'></i> <a href='family_dean.php?action=view_dean_info&id=$fetch_dean_information[id]'>$voted_dean_shortname</a></span>";;
					}
					else
					{
						$vote_html = "<a href='family_dean.php?action=view_nominated_deans&period_id=$current_deanship_period[id]' class='white_button'>عرض قائمة المُرشحين لعمادة العائلة.</a>";
					}
				
					// Get the main template.
					$deanship_html = template(
						"views/family_dean_voting_box.html",
						array(
							"from" => $current_deanship_period["from_period"],
							"to" => $current_deanship_period["to_period"],
							"vote" => $vote_html
						)
					);
				break;
				
				case "ongoing":
					// Get the current dean for the current period.
					$get_selected_dean_query = mysql_query("SELECT * FROM dean WHERE period_id = '$current_deanship_period[id]' AND selected = '1'");
		
					if (mysql_num_rows($get_selected_dean_query) > 0)
					{
						// Fetch this dean.
						$fetch_selected_dean = mysql_fetch_array($get_selected_dean_query);
						
						// Get the information of the dean.
						$dean_info = get_member_id($fetch_selected_dean["member_id"]);
						
						$deanship_html = template(
							"views/family_dean_info_box.html",
							array(
								"from" => $current_deanship_period["from_period"],
								"to" => $current_deanship_period["to_period"],
								"photo" => rep_photo($dean_info["photo"], $dean_info["gender"]),
								"fullname" => $dean_info["fullname"],
								"shortname" => shorten_name($dean_info["fullname"]),
								"id" => $fetch_selected_dean["id"],
								"period_id" => $fetch_selected_dean["period_id"]
							)
						);
					}
					else
					{
						$deanship_html = "";
					}
				break;
			}
		}

		// Get the content.
		$content = template(
			"views/family_dean.html",
			array(
				"deanship" => $deanship_html
			)
		);

		// Get the header.
		$header = website_header(
			"عميد العائلة",
			"صفحة من أجل عرض مهام العميد (المعرّف) و مواصفاته.",
			array(
				"عائلة", "الزغيبي", "عميد", "العائلة"
			)
		);

		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;	
	break;
	
	case "view_dean_info":
		
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the dean is correct.
		$get_dean_query = mysql_query("SELECT * FROM dean WHERE id = '$id'");
		
		if (mysql_num_rows($get_dean_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Fetch some information.
		$get_dean_fetch = mysql_fetch_array($get_dean_query);
		
		// Get the information about the dean.
		$dean_info = get_member_id($get_dean_fetch["member_id"]);
		$cv_html = parse_string_or_list($dean_info["cv"]);
		
		// Get the period information.
		$get_period_query = mysql_query("SELECT from_period, to_period FROM deanship_period WHERE id = '$get_dean_fetch[period_id]'");
		$get_period_fetch = mysql_fetch_array($get_period_query);
		
		// Set the word, either a dean, or a nominee.
		$dean_status = ($get_dean_fetch["selected"] == 1) ? "عميد العائلة" : "المُرشح لعمادة العائلة";
		
		// Get the slogan of the dean, and platform.
		$slogan = trim($get_dean_fetch["slogan"]);
		$platform = trim($get_dean_fetch["platform"]);
		
		// Set slogan HTML, and platform HTML.
		$slogan_html = "";
		$platform_html = "";
		
		if (!empty($slogan))
		{
			$slogan_html = "<h2>شعار الحملة الانتخابيّة</h2><div class='slogan'>$slogan</div>";
		}
		
		if (!empty($platform))
		{
			$platform_list = parse_string_or_list($platform);
			$platform_html = "<h2>برنامج الحملة الانتخابيّة</h2><p><ul>$platform_list</ul></p>";
		}
		
		// Check if the member is able to vote.
		$can_vote_to_dean = can_vote_to_dean($member["id"], $get_dean_fetch["id"]);
		$vote_to_dean_html = "";

		switch ($can_vote_to_dean)
		{
			case "VOTE_ERROR":
				// Nothing here.
			break;
			
			case "VOTE_CANNOT_YOURSELF":
				// Nothing here.
			break;
			
			case "VOTE_ALREADY_VOTED":
				$vote_to_dean_html = "<span><i class='icon-ok'></i> شكراً لتصويتك.</span>";
			break;
			
			case "VOTE_CANNOT_NOT_APPLICABLE":
				$vote_to_dean_html = "<span><i class='icon-warning-sign'></i> لا يمكنك التصويت لعدم تطابق الشروط.</span>";
			break;
			
			case "VOTE_ACCEPTED":
				$vote_to_dean_html = "<a href='family_dean.php?action=vote_to_dean&dean_id=$id' class='white_button'>صوّت لهذا المرشّح ليكون عميداً للعائلة.</a>";
			break;
		}
		
		// Get the content.
		$content = template(
			"views/view_dean_info.html",
			array(
				"dean_status" => $dean_status,
				"photo" => rep_photo($dean_info["photo"], $dean_info["gender"], "avatar"),
				"cv" => $cv_html,
				"slogan" => $slogan_html,
				"platform" => $platform_html,
				"member_id" => $dean_info["id"],
				"fullname" => $dean_info["fullname"],
				"age" => "$dean_info[age] سنة",
				"mobile" => $dean_info["mobile"],
				"company" => rep_company($dean_info["company_id"]),
				"major" => $dean_info["major"],
				"location" => $dean_info["location"],
				"vote_to_dean" => $vote_to_dean_html
			)
		);
		
		// Dean shortname.
		$dean_shortname = shorten_name($dean_info["fullname"]);
		
		// Get the header.
		$header = website_header(
			"$dean_status $dean_shortname",
			"صفحة من أجل عرض تفاصيل $dean_status $dean_shortname",
			array(
				"عائلة", "الزغيبي", "عميد", "مرشح", "عمادة"
			)
		);
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "nominate_yourself":
		
		$period_id = mysql_real_escape_string(@$_GET["period_id"]);
		
		// Check if the period is ok.
		$get_deanship_period_query = mysql_query("SELECT * FROM deanship_period WHERE id = '$period_id' AND status = 'nomination'");
		
		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول  إلى هذه الصفحة.");
			return;
		}
		
		// Check if member is applicable to be a dean.
		if (is_accepted_dean($member["id"]))
		{
			// Check if the member has already nominated himself.
			$member_already_nominated_query = mysql_query("SELECT id FROM dean WHERE period_id = '$period_id' AND member_id = '$member[id]'");
				
			if (mysql_num_rows($member_already_nominated_query) > 0)
			{
				echo error_message("لقد قمت بالفعل بترشيح نفسك لعمادة عائلة الزغيبي.");
				return;
			}
		}
		else
		{
			echo error_message("لا يمكنك ترشيح نفسك لعمادة عائلة الزغيبي لعدم تطابق الشروط.");
			return;
		}
		
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$cv = trim(mysql_real_escape_string(@$_POST["cv"]));
		$slogan = trim(mysql_real_escape_string(@$_POST["slogan"]));
		$platform = trim(mysql_real_escape_string(@$_POST["platform"]));
		
		if (!empty($submit))
		{
			// Check if the cv is empty.
			if (empty($cv))
			{
				echo error_message("الرجاء تعبئة حقل السيرة الذاتيّة.");
				return;
			}
			
			// Update the member cv.
			$update_member_cv_query = mysql_query("UPDATE member SET cv = '$cv' WHERE id = '$member[id]'");
			
			// Insert a new dean
			$now = time();
			$insert_dean_query = mysql_query("INSERT INTO dean (period_id, member_id, slogan, platform, created) VALUES ('$period_id', '$member[id]', '$slogan', '$platform', '$now')");
			$inserted_dean_id = mysql_insert_id();
			
			echo success_message(
				"تم ترشيحك لعمادة عائلة الزغيبي بنجاح.",
				"family_dean.php?action=view_dean_info&id=$inserted_dean_id"
			);
		}
		else
		{
			// Get the content.
			$content = template(
				"views/nominate_yourself_dean.html",
				array(
					"period_id" => $period_id,
					"cv" => $member["cv"]
				)
			);
		
			// Get the header.
			$header = website_header(
				"رشّح نفسك لعمادة العائلة",
				"صفحة من أجل الترشّح لعمادة العائلة",
				array(
					"عائلة", "الزغيبي", "الترشيح", "عمادة"
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
	
	case "vote_to_dean":
		
		$dean_id = mysql_real_escape_string(@$_GET["dean_id"]);
		$can_vote_to_dean = can_vote_to_dean($member["id"], $dean_id);
		
		// Check if the member can vote for dean.
		switch ($can_vote_to_dean)
		{
			case "VOTE_ERROR":
				echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			break;
			
			case "VOTE_CANNOT_YOURSELF":
				echo error_message("لا يمكنك التصويت لنفسك.");
			break;
			
			case "VOTE_ALREADY_VOTED":
				echo error_message("لقد قمت بالتصويت مسبقاً.");
			break;
			
			case "VOTE_CANNOT_NOT_APPLICABLE":
				echo error_message("لا يمكنك التصويت لعدم تطابق الشروط.");
			break;

			case "VOTE_ACCEPTED":
			
				$now = time();
				$insert_vote_query = mysql_query("INSERT INTO member_dean (member_id, dean_id, created) VALUES ('$member[id]', '$dean_id', '$now')");
				
				// Everything is alright.
				echo success_message(
					"شكراً لك على تصويتك، و بارك الله فيك.",
					"family_dean.php?action=view_dean_info&id=$dean_id"
				);
			break;
		}
	break;	
	
	case "view_nominated_deans":
	
		$period_id = mysql_real_escape_string(@$_GET["period_id"]);
		
		// Check if the period is correct and status is 'nomination'.
		$get_deanship_period_query = mysql_query("SELECT * FROM deanship_period WHERE id = '$period_id' AND status = 'voting'");
		
		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get all nominated deans.
		$get_nominated_deans_query = mysql_query("SELECT * FROM dean WHERE period_id = '$period_id' ORDER BY RAND()");
		
		if (mysql_num_rows($get_nominated_deans_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		$nominated_deans_html = "";
		
		// Start walking up-on the nominated deans.
		while ($nominated_dean = mysql_fetch_array($get_nominated_deans_query))
		{
			// Get the dean information.
			$dean_info = get_member_id($nominated_dean["member_id"]);
		
			$nominated_deans_html .= template(
				"views/nominated_dean_info_box.html",
				array(
					"fullname" => $dean_info["fullname"],
					"shortname" => shorten_name($dean_info["fullname"]),
					"id" => $nominated_dean["id"],
					"photo" => rep_photo($dean_info["photo"], $dean_info["gender"], "avatar"),
					"age" => "$dean_info[age] سنة"
				)
			);
		}	
		
		// Get the content.
		$content = template(
			"views/nominated_deans.html",
			array(
				"nominated_deans" => $nominated_deans_html
			)
		);
		
		// Get the header.
		$header = website_header(
			"قائمة المرشحين لعمادة العائلة",
			"صفحة من أجل عرض قائمة المرشحين لعمادة العائلة.",
			array(
				"عائلة", "الزغيبي", "المرشحين", "للعمادة"
			)
		);
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "voting_results":
		
		$period_id = mysql_real_escape_string(@$_GET["period_id"]);
		
		// Check if the period is correct and status is 'nomination'.
		$get_deanship_period_query = mysql_query("SELECT * FROM deanship_period WHERE id = '$period_id' AND (status = 'ongoing' OR status = 'finished')");
		
		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Fetch deanship period.
		$fetch_deanship_period = mysql_fetch_array($get_deanship_period_query);
		
		// Get all votes count for this period.
		$get_all_votes_query = mysql_query("SELECT COUNT(id) AS all_votes FROM member_dean WHERE dean_id IN (SELECT id FROM dean WHERE period_id = '$fetch_deanship_period[id]')");
		
		if (mysql_num_rows($get_all_votes_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Fetch all votes.
		$fetch_all_votes = mysql_fetch_array($get_all_votes_query);
		$all_votes = $fetch_all_votes["all_votes"];
		
		// Get period deans and votes.
		$get_period_deans_query = mysql_query("SELECT dean.id AS id, dean.member_id AS member_id, dean.selected AS selected, (SELECT COUNT(member_dean.id) FROM member_dean WHERE member_dean.dean_id = dean.id) AS votes FROM dean WHERE period_id = '$fetch_deanship_period[id]' ORDER BY votes DESC");
		$deans_html = "";
		
		if (mysql_num_rows($get_period_deans_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			while ($dean = mysql_fetch_array($get_period_deans_query))
			{
				// Get dean information.
				$dean_info = get_member_id($dean["member_id"]);
				$selected = ($dean["selected"] == 1) ? "(العميد)" : "";
				$percent = round(($dean["votes"]/$all_votes), 2) * 100;
				$deans_html .= "<tr><td><a href='family_dean.php?action=view_dean_info&id=$dean[id]'>$dean_info[fullname]</a> $selected</td><td><span class='number'>$dean[votes]</span> <span>($percent%)</span></td><tr>";
			}
		}
		
		$content = template(
			"views/deans_voting_results.html",
			array(
				"deans" => $deans_html,
				"from" => $fetch_deanship_period["from_period"],
				"to" => $fetch_deanship_period["to_period"]
			)
		);
		
		// Get the header.
		$header = website_header(
			"نتائج التصويت للمرشحين لعمادة العائلة",
			"صفحة من أجل عرض نتائج التصويت للمرشحين لعمادة العائلة",
			array(
				"عائلة", "الزغيبي", "نتائج", "التصويت", "مرشحين", "العمادة"
			)
		);
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
}
