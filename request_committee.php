<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();
$action = mysql_real_escape_string(@$_GET["action"]);

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Check if the member is a head of a committee.
$get_head_query = mysql_query("SELECT committee_id FROM member_committee WHERE member_id = '$user[member_id]' AND member_title = 'head'");

if (mysql_num_rows($get_head_query) == 0)
{
	echo error_message("لا يمكنك الوصول إلى هذه الوصول.");
	return;
}

// Get the committee id.
$committee_fetch = mysql_fetch_array($get_head_query);
$committee_id = $committee_fetch["committee_id"];

// Get the committee information.
$get_committee_query = mysql_query("SELECT * FROM committee WHERE id = '$committee_id'");
$get_committee_fetch = mysql_fetch_array($get_committee_query);
$committee_name = $get_committee_fetch["name"];

switch ($action)
{
	default: case "view":
	
		// -------------------------------------------------
		// Get the pending members who want to join the committee.
		// -------------------------------------------------
		$pending_members_query = mysql_query("SELECT member.id AS member_id, member.fullname AS member_fullname, member.mobile AS member_mobile, member.age AS member_age, member.major AS member_major, member.job_title AS member_job_title, member_committee.member_title AS member_title FROM member, member_committee WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$committee_id' AND member_committee.member_title != 'head' AND member_committee.status = 'pending'");
		$pending_members_count = mysql_num_rows($pending_members_query);
		$pending_members_html = "";
		
		if ($pending_members_count == 0)
		{
			$pending_members_html = "<tr><td colspan='2' class='error'><i class='icon-exclamation-sign'></i> لا يوجد طلبات معلّقة.</td></tr>";
		}
		else
		{
			while ($pending_member = mysql_fetch_array($pending_members_query))
			{
				$member_bio = "$pending_member[member_mobile] - $pending_member[member_major] - $pending_member[member_job_title]";
				$pending_members_html .= "<tr><td><p><a href='familytree.php?id=$pending_member[member_id]'>$pending_member[member_fullname]</a> ($pending_member[member_age] سنة)</p><p class='datetime'>$member_bio</p></td><td><ul class='ul_inline'><li><a id='pending_accept_$pending_member[member_id]' href='request_committee.php?action=accept&member_id=$pending_member[member_id]' class='zoghiby_btn positive'><i class='icon-ok icon-white'></i> قبول</a></li> <li><a href='#reject' class='zoghiby_btn negative' onclick='reject($pending_member[member_id])'><i class='icon-remove icon-white'></i> رفض</a></li></ul><div class='reject_reason'><input type='text' placeholder='سبب الرفض (إن وجد)' id='reject_reason_$pending_member[member_id]' onkeyup='write_reject_reason($pending_member[member_id])' onkeydown='write_reject_reason($pending_member[member_id])' onkeypress='write_reject_reason($pending_member[member_id])' /></div></td></tr>";
			}
		}
		
		// -------------------------------------------------
		// Get the members of the committee.
		// -------------------------------------------------
		$committee_members_query = mysql_query("SELECT member.id AS member_id, member.fullname AS member_fullname, member.mobile AS member_mobile, member.age AS member_age, member.major AS member_major, member.job_title AS member_job_title, member_committee.member_title AS member_title FROM member, member_committee WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$committee_id' AND member_committee.member_title != 'head' AND member_committee.status = 'accepted'");
		$committee_members_count = mysql_num_rows($committee_members_query);
		$committee_members_html = "";
		
		if ($committee_members_count == 0)
		{
			$committee_members_html = "<tr><td colspan='2' class='error'><i class='icon-exclamation-sign'></i> لا أعضاء بعد.</td></tr>";
		}
		else
		{
			while ($committee_member = mysql_fetch_array($committee_members_query))
			{
				//$member_bio = "$committee_member[member_mobile] - $committee_member[member_major] - $committee_member[member_job_title]";
				$committee_members_html .= "<tr><td><p><a href='familytree.php?id=$committee_member[member_id]'>$committee_member[member_fullname]</a></p></td><td><ul class='ul_inline'><li><input type='text' placeholder='سبب الإقالة (إن وجد)' id='resign_reason_$committee_member[member_id]'/></li> <li><a href='#reject' class='zoghiby_btn negative' onclick='resign($committee_member[member_id])'><i class='icon-minus icon-white'></i> إقالة</a></li></ul></td></tr>";
			}
		}
		
		// -------------------------------------------------
		// Get rejected or resigned members.
		// -------------------------------------------------
		$rejected_resigned_members_query = mysql_query("SELECT member.id AS member_id, member.fullname AS member_fullname, member.mobile AS member_mobile, member.age AS member_age, member.major AS member_major, member.job_title AS member_job_title, member_committee.member_title AS member_title, member_committee.status AS status, member_committee.reason AS reason FROM member, member_committee WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$committee_id' AND member_committee.member_title != 'head' AND (member_committee.status = 'rejected' OR member_committee.status = 'resigned') ORDER BY status");
		$rejected_resigned_members_count = mysql_num_rows($rejected_resigned_members_query);
		$rejected_resigned_members_html = "";
		
		if ($rejected_resigned_members_count == 0)
		{
			$rejected_resigned_members_html = "<tr><td colspan='3' class='error'><i class='icon-exclamation-sign'></i> لا أعضاء بعد.</td></tr>";
		}
		else
		{
			while ($rejected_resigned_member = mysql_fetch_array($rejected_resigned_members_query))
			{
				//$member_bio = "$rejected_resigned_member[member_mobile] - $rejected_resigned_member[member_major] - $rejected_resigned_member[member_job_title]";
				$status = ($rejected_resigned_member["status"] == "rejected") ? "مرفوض" : "مُقال";
				$rejected_resigned_members_html .= "<tr><td><p>($status) <a href='familytree.php?id=$rejected_resigned_member[member_id]'>$rejected_resigned_member[member_fullname]</a></p></td><td>$rejected_resigned_member[reason]</td><td><ul class='ul_inline'><li><a href='request_committee.php?action=rejoin&member_id=$rejected_resigned_member[member_id]' class='zoghiby_btn positive'><i class='icon-repeat icon-white'></i> إعادة</a></li></ul></td></tr>";
			}
		}
		
		// -------------------------------------------------
		// Get nominee members.
		// -------------------------------------------------
		$nominee_members_query = mysql_query("SELECT member.id AS member_id, member.fullname AS member_fullname, member.mobile AS member_mobile, member.age AS member_age, member.major AS member_major, member.job_title AS member_job_title, member_committee.member_title AS member_title, member_committee.status AS status, member_committee.reason AS reason FROM member, member_committee WHERE member.id = member_committee.member_id AND member_committee.committee_id = '$committee_id' AND member_committee.member_title != 'head' AND (member_committee.status = 'nominee') ORDER BY status");
		$nominee_members_count = mysql_num_rows($nominee_members_query);
		$nominee_members_html = "";
		
		if ($nominee_members_count == 0)
		{
			$nominee_members_html = "<tr><td colspan='3' class='error'><i class='icon-exclamation-sign'></i> لا أعضاء بعد.</td></tr>";
		}
		else
		{
			while ($nominee_member = mysql_fetch_array($nominee_members_query))
			{
				$nominee_members_html .= "<tr><td><p><a href='familytree.php?id=$nominee_member[member_id]'>$nominee_member[member_fullname]</a></p></td><td><i class='icon-time'></i> لم يقرّر بعد.</td></tr>";
			}
		}
		
		// Build the page.
		// Get the content.
		$content = template(
			"views/request_committee.html",
			array(
				"pending_members_count" => $pending_members_count,
				"pending_members" => $pending_members_html,
				"committee_members_count" => $committee_members_count,
				"committee_members" => $committee_members_html,
				"rejected_resigned_members_count" => $rejected_resigned_members_count,
				"rejected_resigned_members" => $rejected_resigned_members_html,
				"nominee_members_count" => $nominee_members_count,
				"nominee_members" => $nominee_members_html
			)
		);
		
		// Get the header.
		$header = website_header(
			"طلبات الانضمام إلى $committee_name",
			"صفحة من أجل عرض طلبات الانضمام إلى اللجنة.",
			array(
				"عائلة", "الانضمام", "الزغيبي", "اللجنة", $committee_name
			)
		);
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "accept":
		$member_id = mysql_real_escape_string(@$_GET["member_id"]);
		
		// Check if the member does already exist.
		$member = get_member_id($member_id);
		
		if ($member == false)
		{
			echo error_message("لم يتم العثور على العضو المطلوب.");
			return;
		}
		
		// Get the id of the member_committee.
		$get_member_committee_query = mysql_query("SELECT id FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$get_committee_fetch[id]'");
		$get_member_committee_fetch = mysql_fetch_array($get_member_committee_query);
		
		// Set the time (now).
		$now = time();
		
		// Otherwise, everything is okay.
		// Accept the member and join to the committee.
		$update_member_query = mysql_query("UPDATE member_committee SET status = 'accepted', joined = '$now' WHERE id = '$get_member_committee_fetch[id]'");
		
		// Ok.
		echo success_message(
			"تم قبول طلب الانضمام إلى اللجنة بنجاح.",
			"request_committee.php"
		);
	break;
	
	case "reject":
		$member_id = mysql_real_escape_string(@$_GET["member_id"]);
		$reason = trim(mysql_real_escape_string(@$_GET["reason"]));
		
		// Check if the member does already exist.
		$member = get_member_id($member_id);
		
		if ($member == false)
		{
			echo error_message("لم يتم العثور على العضو المطلوب.");
			return;
		}
		
		// Check if the reason is empty.
		if (empty($reason))
		{
			echo error_message("الرجاء إدخال سبب رفض العضو.");
			return;
		}
		
		// Get the id of the member_committee.
		$get_member_committee_query = mysql_query("SELECT id FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$get_committee_fetch[id]'");
		$get_member_committee_fetch = mysql_fetch_array($get_member_committee_query);
		
		// Otherwise, everything is okay.
		// Accept the member and join to the committee.
		$update_member_query = mysql_query("UPDATE member_committee SET status = 'rejected', reason = '$reason' WHERE id = '$get_member_committee_fetch[id]'");
		
		// Ok.
		echo success_message(
			"تم رفض العضو بنجاح.",
			"request_committee.php"
		);
	break;
	
	case "resign":
		$member_id = mysql_real_escape_string(@$_GET["member_id"]);
		$reason = trim(mysql_real_escape_string(@$_GET["reason"]));
		
		// Check if the member does already exist.
		$member = get_member_id($member_id);
		
		if ($member == false)
		{
			echo error_message("لم يتم العثور على العضو المطلوب.");
			return;
		}
		
		// Check if the reason is empty.
		if (empty($reason))
		{
			echo error_message("الرجاء إدخال سبب إقالة العضو.");
			return;
		}
		
		// Get the id of the member_committee.
		$get_member_committee_query = mysql_query("SELECT id FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$get_committee_fetch[id]'");
		$get_member_committee_fetch = mysql_fetch_array($get_member_committee_query);
		
		// Set the time (now).
		$now = time();
		
		// Otherwise, everything is okay.
		// Accept the member and join to the committee.
		$update_member_query = mysql_query("UPDATE member_committee SET status = 'resigned', reason = '$reason', leaved = '$now' WHERE id = '$get_member_committee_fetch[id]'");
		
		// Ok.
		echo success_message(
			"تم إقالة العضو بنجاح.",
			"request_committee.php"
		);
	break;
	
	case "rejoin":
		$member_id = mysql_real_escape_string(@$_GET["member_id"]);
		
		// Check if the member does already exist.
		$member = get_member_id($member_id);
		
		if ($member == false)
		{
			echo error_message("لم يتم العثور على العضو المطلوب.");
			return;
		}
		
		// Get the id of the member_committee.
		$get_member_committee_query = mysql_query("SELECT id FROM member_committee WHERE member_id = '$member[id]' AND committee_id = '$get_committee_fetch[id]'");
		$get_member_committee_fetch = mysql_fetch_array($get_member_committee_query);
		
		// Set the time (now).
		$now = time();
		
		// Otherwise, everything is okay.
		// Accept the member and join to the committee.
		$update_member_query = mysql_query("UPDATE member_committee SET status = 'accepted', joined = '$now', leaved = '0' WHERE id = '$get_member_committee_fetch[id]'");
		
		// Ok.
		echo success_message(
			"تم إعادة العضو إلى اللجنة بنجاح.",
			"request_committee.php"
		);
	break;
	
	
}
