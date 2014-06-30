<?php

require_once("inc/functions.inc.php");

$user = user_information();

// If the user is a visitor.
if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Global variables
$id = mysql_real_escape_string(@$_GET["id"]);
$submit = mysql_real_escape_string(@$_POST["submit"]);

if (empty($id))
{
	echo error_message("لم يتم العثور على العضو المطلوب.");
	return;
}

$member = get_member_id($id);

if ($member == false)
{
	echo error_message("لم يتم العثور على العضو المطلوب.");
	return;
}

// Check if the user is able to update member's optional.
$is_admin = ($user["group"] == "admin");

// Check if the user is seeing his/her optional.
$is_me = ($member["id"] == $user["member_id"]);

if (!$is_admin && !$is_me)
{
	echo error_message("تم رفض الوصول إلى هذه الصفحة.");
	return;
}

// If the user submitted the form.
if (!empty($submit))
{
	// Set accepted privacy names.
	$accepted_privacy_names = array(
		"mother",
		"partners",
		"daughters",
		"marital_status",
		"dob",
		"pob",
		"age",
		"mobile",
		"phone_home",
		"email",
		"phone_work",
		"location",
		"living",
		"neighborhood",
		"education",
		"major",
		"company",
		"job_title",
		"blood_type"
	);
	
	// Set accepted privacy values.
	$accepted_privacy_values = array(
		"all", "members", "related_circle"
	);

	// Post variables.
	$privacy = $_POST["privacy"];
	
	// Start walking up-on privacies.
	foreach ($privacy as $one_privacy_name => $one_privacy_value)
	{
		//echo "$one_privacy_name => $one_privacy_value\n";
		// Check if the name is correct.
		if (!in_array($one_privacy_name, $accepted_privacy_names))
		{
			echo error_message("الرجاء إدخال معلومات خصوصيّة صحيحة.");
			return;
		}
		else
		{
			// Check if the value is correct.
			if (!in_array($one_privacy_value, $accepted_privacy_values))
			{
				echo error_message("الرجاء إدخال قيمة خصوصيّة صحيحة.");
				return;
			}
			else
			{
				// Update the privacy value for the member.
				$update_privacy = mysql_query("UPDATE member SET privacy_{$one_privacy_name} = '$one_privacy_value' WHERE id = '$member[id]'");
			}
		}
	}
	
	echo success_message(
		"تم تحديث الخصوصيّة بنجاح.",
		"manage_privacy.php?id=$member[id]"
	);
}
else
{
	// Set the privacy.
	$privacies = array(
			// Females Information.
			array(
				"معلومات ذوي القرابة",
				array(
					// Mother
					array(
						"mother",
						"إظهار اسم الأم",
						$member["privacy_mother"]
					),
					
					// Wives/Husbands
					array(
						"partners",
						"إظهار أسماء الزوجات/الأزواج  (إن وُجد)",
						$member["privacy_partners"]
					),
					
					// Daughters
					array(
						"daughters",
						"إظهار أسماء البنات (إن وُجد)",
						$member["privacy_daughters"]
					)
				),
			),
			
			// Marital Status Information.
			array(
				"معلومات الحالّة الاجتماعيّة",
				array(
					// Marital Status
					array(
						"marital_status",
						"إظهار الحالّة الاجتماعيّة",
						$member["privacy_marital_status"]
					)
				)
			),
			
			// History Information.
			array(
				"معلومات تاريخيّة",
				array(
					// DOB
					array(
						"dob",
						"إظهار تاريخ الميلاد",
						$member["privacy_dob"]
					),
					
					// POB
					array(
						"pob",
						"إظهار مكان الميلاد",
						$member["privacy_pob"]
					),
					
					// Age
					array(
						"age",
						"إظهار العمر",
						$member["privacy_age"]
					)
				)
			),
			
			// Contacts Information.
			array(
				"معلومات الإتّصال",
				array(
					// Mobile
					array(
						"mobile",
						"إظهار الجوّال (إن وُجد)",
						$member["privacy_mobile"]
					),
					
					// Phone Home
					array(
						"phone_home",
						"إظهار الهاتف (إن وُجد)",
						$member["privacy_phone_home"]
					),
					
					// Email
					array(
						"email",
						"إظهار البريد الإلكتروني",
						$member["privacy_email"]
					),
					
					// Phone Work
					array(
						"phone_work",
						"إظهار هاتف العمل (إن وُجد)",
						$member["privacy_phone_work"]
					)
				)
			),
			
			// Location Information.
			array(
				"معلومات المكان",
				array(
					// Location
					array(
						"location",
						"إظهار مكان الإقامة",
						$member["privacy_location"]
					),
					
					// Living
					array(
						"living",
						"إظهار نوع السكن",
						$member["privacy_living"]
					),
					
					// Neighborhood
					array(
						"neighborhood",
						"إظهار الحيّ",
						$member["privacy_neighborhood"]
					)
				)
			),
			
			// Education Information.
			array(
				"معلومات التعليم",
				array(
					// Education
					array(
						"education",
						"إظهار المستوى التعليمي",
						$member["privacy_education"]
					),
					
					// Major
					array(
						"major",
						"إظهار التخصّص",
						$member["privacy_major"]
					)
				)
			),
			
			// Career Information.
			array(
				"معلومات العمل",
				array(
					// Company
					array(
						"company",
						"إظهار جهة العمل (إن وُجد)",
						$member["privacy_company"]
					),
					
					// Job Title
					array(
						"job_title",
						"إظهار المسمّى الوظيفي (إن وُجد)",
						$member["privacy_job_title"]
					)
				)
			),
			
			// Structure Information.
			array(
				"معلومات التكوين",
				array(
					// Blood Type
					array(
						"blood_type",
						"إظهار فصيلة الدم",
						$member["privacy_blood_type"]
					)
				)
			),
	);
	
	// Privacy html content.
	$privacy_html = "";
	
	// Start to walk up-on privacies.
	foreach ($privacies as $privacy)
	{
		$category = $privacy[0];
		$sub_privacies = $privacy[1];
		
		$privacy_html .= "<h4 class='subheader'>$category</h4>\n";
		
		// Start walking up-on sub-privacies.
		foreach ($sub_privacies as $sub_privacy)
		{
			$privacy_name = $sub_privacy[0];
			$privacy_label = $sub_privacy[1];
			$privacy_default_value = $sub_privacy[2];
			
			$privacy_select = privacy_select($privacy_name, $privacy_default_value);
			$privacy_html .= "<div class='row'><div class='large-12 columns'><label>$privacy_label</label>$privacy_select</div></div>\n";
		}
	}

	// Set the content.
	$content = template(
		"views/manage_privacy.html",
		array(
			"id" => $member["id"],
			"privacy" => $privacy_html
		)
	);

	// Get the header.
	$header = website_header(
		"إدارة الخصوصيّة",
		"صفحة من أجل إدارة الخصوصيّة للعضو $member[fullname]",
		array(
			"عائلة","الزغيبي", "إدارة", "الخصوصيّة"
		)
	);
	
	// Get the footer.
	$footer = website_footer();
	
	echo $header;
	echo $content;
	echo $footer;
}
