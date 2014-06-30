<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Get the member information.
$member = get_member_id($user["member_id"]);
$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	case "view_job_details":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the job does exist.
		$get_job_query = mysql_query("SELECT * FROM job WHERE id = '$id'");
		
		if (mysql_num_rows($get_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the job details.
		$job = mysql_fetch_array($get_job_query);
		
		$responsibilities_html = "";
		$qualifications_html = "";
		$desired_skills_html = "";
		
		if (!empty($job["responsibilities"]))
		{
			$responsibilities_html = "<ul>" . parse_string_or_list($job["responsibilities"]) . "</ul>";
		}
		
		if (!empty($job["qualifications"]))
		{
			$qualifications_html = "<ul>" . parse_string_or_list($job["qualifications"]) . "</ul>";
		}
		
		if (!empty($job["desired_skills"]))
		{
			$desired_skills_html = "<ul>" . parse_string_or_list($job["desired_skills"]) . "</ul>";
		}
		
		$created = arabic_date(date("d M Y", $job["created"]));
		$apply_html = "";
		
		// Check if the member has already applied.
		$member_already_applied_query = mysql_query("SELECT * FROM member_job WHERE member_id = '$member[id]' AND job_id = '$job[id]'");
		
		if (mysql_num_rows($member_already_applied_query) == 0)
		{
			$apply_html = "<a href='jobs.php?action=apply_for_job&id=$job[id]'>تقدّم.</a>";
		}
		else
		{
			$apply_html = "لقد تقدّمت إلى هذه الوظيفة.";
		}
		
		// Get the contnet.
		$content = template(
			"views/view_job_details.html",
			array(
				"id" => $id["id"],
				"title" => $job["title"],
				"en_title" => $job["en_title"],
				"description" => $job["description"],
				"responsibilities" => $responsibilities_html,
				"qualifications" => $qualifications_html,
				"desired_skills" => $desired_skills_html,
				"created" => $created,
				"apply" => $apply_html
			)
		);
		
		// Get the header.
		$header = website_header(
			"وظيفة $job[title]",
			"صفحة من أجل عرض تفاصيل وظيفة $job[title]",
			array(
				"عائلة", "الزغيبي", "تفاصيل", "وظيفة"
			)
		);
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the content.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "apply_for_job":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the job does exist.
		$get_job_query = mysql_query("SELECT * FROM job WHERE id = '$id'");
		
		if (mysql_num_rows($get_job_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the job details.
		$job = mysql_fetch_array($get_job_query);
		
		// Check if the member applicable for applying for this job.
		if ($user["first_login"] == 1)
		{
			echo error_message("الرجاء تحديث البيانات الأساسيّة لتتمكّن من عرض هذه الصغحة.");
			return;
		}
		
		// Check if the age is applicable.
		if ($member["age"] < 15)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Check if the member has already applied.
		$member_already_applied_query = mysql_query("SELECT * FROM member_job WHERE member_id = '$member[id]' AND job_id = '$job[id]'");
		
		if (mysql_num_rows($member_already_applied_query) > 0)
		{
			echo error_message("لقد تقدّمت بالفعل لهذه الوظيفة.");
			return;
		}
		
		// Everything is alright.
		$now = time();
		$insert_member_job_query = mysql_query("INSERT INTO member_job (member_id, job_id, created) VALUES ('$member[id]', '$job[id]', '$now')");
		
		echo success_message(
			"شكراً لك، تم استقبال طلبك، و سيتم التواصل معك قريباً.",
			"jobs.php?action=view_job_details&id=$job[id]"
		);
		
	break;
	
	default: case "view_jobs":
	
		// Get the jobs.
		$get_jobs_query = mysql_query("SELECT * FROM job WHERE hired = '0' ORDER BY created ASC");
		$jobs_html = "";
		
		if (mysql_num_rows($get_jobs_query) == 0)
		{
			$jobs_html = "لا يوجد وظائف مُضافة.";
		}
		else
		{
			while ($job = mysql_fetch_array($get_jobs_query))
			{
				$jobs_html .= "<li><a href='jobs.php?action=view_job_details&id=$job[id]'>$job[title] ($job[en_title])</a></li>";
			}
		}
	
		// Get the content.
		$content = template(
			"views/jobs.html",
			array(
				"jobs" => $jobs_html
			)
		);

		// Get the header.
		$header = website_header(
			"وظائف",
			"صفحة من أجل عرض وظائف.",
			array(
				"عائلة", "الزغيبي", "وظائف"
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

