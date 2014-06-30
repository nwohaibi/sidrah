<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

// Check if the user is not an admin.
if ($user["group"] != "admin")
{
	redirect_to_login();
	return;
}

$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	case "add_job":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$title = mysql_real_escape_string(@$_POST["title"]);
			$en_title = mysql_real_escape_string(@$_POST["en_title"]);
			$description = mysql_real_escape_string(@$_POST["description"]);
			$responsibilities = mysql_real_escape_string(@$_POST["responsibilities"]);
			$qualifications = mysql_real_escape_string(@$_POST["qualifications"]);
			$desired_skills = mysql_real_escape_string(@$_POST["desired_skills"]);
		
			// Check if the title of the job is empty and description.
			if (empty($title) || empty($description))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$insert_job_query = mysql_query("INSERT INTO job (title, en_title, description, responsibilities, qualifications, desired_skills, created) VALUES ('$title', '$en_title', '$description', '$responsibilities', '$qualifications', '$desired_skills', '$now')");
			
			// Awesome.
			echo success_message(
				"تم إضافة الوظيفة بنجاح.",
				"manage_jobs.php"
			);
		}
	
	break;
	
	case "edit_job":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the job does exist.
		$get_job_query = mysql_query("SELECT * FROM job WHERE id = '$id'");
		
		if (mysql_num_rows($get_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the job information.
		$job = mysql_fetch_array($get_job_query);
		
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$title = mysql_real_escape_string(@$_POST["title"]);
			$en_title = mysql_real_escape_string(@$_POST["en_title"]);
			$description = mysql_real_escape_string(@$_POST["description"]);
			$responsibilities = mysql_real_escape_string(@$_POST["responsibilities"]);
			$qualifications = mysql_real_escape_string(@$_POST["qualifications"]);
			$desired_skills = mysql_real_escape_string(@$_POST["desired_skills"]);
		
			// Check if the title of the job is empty and description.
			if (empty($title) || empty($description))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$update_job_query = mysql_query("UPDATE job SET title = '$title', en_title = '$en_title', description = '$description', responsibilities = '$responsibilities', qualifications = '$qualifications', desired_skills = '$desired_skills', created = '$now' WHERE id = '$job[id]'");
			
			// Awesome.
			echo success_message(
				"تم تعديل الوظيفة بنجاح.",
				"manage_jobs.php"
			);
		}
		else
		{
			// Get the content.
			$content = template(
				"views/edit_job.html",
				array(
					"id" => $job["id"],
					"title" => $job["title"],
					"en_title" => $job["en_title"],
					"description" => $job["description"],
					"responsibilities" => $job["responsibilities"],
					"qualifications" => $job["qualifications"],
					"desired_skills" => $job["desired_skills"]
				)
			);
			
			// Get the header.
			$header = website_header(
				"تعديل وظيفة $job[title]",
				"صفحة من أجل تعديل وظيفة $job[title]",
				array(
					"عائلة", "الزغيبي", "تعديل", "وظيفة"
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

	case "update_jobs":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$do = mysql_real_escape_string(@$_POST["do"]);
		$check = @$_POST["check"];
		
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
		if (count($check) > 0)
		{
			foreach ($check as $k => $v)
			{
				// Delete all related rows.
				$delete_member_job_query = mysql_query("DELETE FROM member_job WHERE job_id = '$k'");
			
				// Delete selected job.
				$delete_job_query = mysql_query("DELETE FROM job WHERE id = '$k'");
			}
		}
		else
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Done.
		echo success_message(
			"تم حذف الوظائف المحدّدة بنجاح.",
				"manage_jobs.php"
		);
		
		return;
	break;
	
	case "view_applied_members":
	
		$job_id = mysql_real_escape_string(@$_GET["job_id"]);
		
		// Check if the job does exist.
		$get_job_query = mysql_query("SELECT * FROM job WHERE id = '$job_id'");
		
		if (mysql_num_rows($get_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the job information.
		$job = mysql_fetch_array($get_job_query);
		
		// Check if the members who have applied are none.
		$get_applied_members_query = mysql_query("SELECT member_job.id AS id, member.fullname as member_fullname, member.id as member_id, member_job.status AS status FROM member, member_job WHERE member.id = member_job.member_id AND job_id = '$job[id]'")or die(mysql_error());
		$applied_members_count = mysql_num_rows($get_applied_members_query);
		
		// Now, do the checking.
		if ($applied_members_count == 0)
		{
			echo error_message("لا يوجد من تقدّم إلى هذه الوظيفة.");
			return;
		}
		
		// Otherwise, everything is beautiful.
		$applied_members_html = "";
		
		while ($applied_member = mysql_fetch_array($get_applied_members_query))
		{
			$accept_btn = "<a class='zoghiby_btn positive' href='manage_jobs.php?action=accept&id=$applied_member[id]'><i class='icon-ok icon-white'></i> قبول</a>";
			$reject_btn = "<a class='zoghiby_btn negative' href='manage_jobs.php?action=reject&id=$applied_member[id]'><i class='icon-remove icon-white'></i> رفض</a>";
			
			switch ($applied_member["status"])
			{
				case "accepted":
					$status = "مقبول";
					$actions = "$reject_btn";
				break;
				
				case "rejected":
					$status = "مرفوض";
					$actions = "$accept_btn";
				break;
				
				default:
					$status = "<i class='icon-time'></i> ينتظر";
					$actions = "$accept_btn $reject_btn";
				break;
			}
			
			$applied_members_html .= "<tr><td><a href='familytree.php?id=$applied_member[member_id]'>$applied_member[member_fullname]</a></td><td>($status)</td><td>$actions</td></tr>";
		}
		
		// Get the content.
		$content = template(
			"views/view_applied_members.html",
			array(
				"job_title" => $job["title"],
				"job_id" => $job["id"],
				"applied_members_count" => $applied_members_count,
				"applied_members" => $applied_members_html
			)
		);
		
		// Get the header.
		$header = website_header(
			"عرض المتقدمين لوظيفة $job[title]",
			"صفحة من أجل عرض المتقدمين لوظيفة $job[title].",
			array(
				"عرض", "المتقدمين", "وظيفة", "عائلة", "الزغيبي"
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
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the member job exists.
		$get_member_job_query = mysql_query("SELECT * FROM member_job WHERE id = '$id' AND status != 'accepted'");
		
		if (mysql_num_rows($get_member_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the member job information.
		$member_job = mysql_fetch_array($get_member_job_query);
		
		// Update it.
		$update_member_job_query = mysql_query("UPDATE member_job SET status = 'accepted' WHERE id = '$member_job[id]'");
		
		// Done.
		echo success_message(
			"تم قبول المتقدّم للوظيفة بنجاح.",
			"manage_jobs.php?action=view_applied_members&job_id=$member_job[job_id]"
		);
	
	break;
	
	case "reject":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the member job exists.
		$get_member_job_query = mysql_query("SELECT * FROM member_job WHERE id = '$id' AND status != 'rejected'");
		
		if (mysql_num_rows($get_member_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the member job information.
		$member_job = mysql_fetch_array($get_member_job_query);
		
		// Update it.
		$update_member_job_query = mysql_query("UPDATE member_job SET status = 'rejected' WHERE id = '$member_job[id]'");
		
		// Done.
		echo success_message(
			"تم رفض المتقدّم للوظيفة بنجاح.",
			"manage_jobs.php?action=view_applied_members&job_id=$member_job[job_id]"
		);
	
	break;


	default: case "view_jobs":

		// Get the jobs.
		$get_jobs_query = mysql_query("SELECT job.*, (SELECT COUNT(member_job.id) FROM member_job WHERE member_job.job_id = job.id) AS applied_count FROM job ORDER BY created ASC");
		$jobs_html = "";
		
		if (mysql_num_rows($get_jobs_query) == 0)
		{
			$jobs_html = "<tr><td colspan='4'>لا يوجد وظائف بعد.</td></tr>";
		}
		else
		{
			// Walk up-on these jobs.
			while ($job = mysql_fetch_array($get_jobs_query))
			{
				$jobs_html .= "<tr><td><input type='checkbox' name='check[$job[id]]' /></td><td><a href='jobs.php?action=view_job_details&id=$job[id]'>$job[title] <span class='hide-for-small'>($job[en_title])</span></a></td><td class='hide-for-small'><a href='manage_jobs.php?action=view_applied_members&job_id=$job[id]'>$job[applied_count]</a></td><td><a href='manage_jobs.php?action=edit_job&id=$job[id]' class='small button'>تعديل</a></td></tr>";
			}
		}
	
		// Get the content.
		$content = template(
			"views/manage_jobs.html",
			array(
				"jobs" => $jobs_html
			)
		);
		
		// Get the header.
		$header = website_header(
				"عرض الوظائف",
				"صفحة من أجل عرض وظائف موقع عائلة الزغيبي",
				array(
					"وظائف", "عرض", "الزغيبي", "عائلة"
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
