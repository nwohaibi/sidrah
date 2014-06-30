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

/*
// Check if the moderator is accepted (if any).
$is_accepted_moderator = is_accepted_moderator($member["id"]);

// Check if the user is relative to the member.
$is_relative_user = is_relative_user($member["id"]);
*/

if (!$is_admin && !$is_me)
{
	echo error_message("تم رفض الوصول إلى هذه الصفحة.");
	return;
}

// If the user submitted the form.
if (!empty($submit))
{
	// Post variables.
	$phone_home = mysql_real_escape_string(trim(arabic_number(@$_POST["phone_home"])));
	$email = mysql_real_escape_string(trim(@$_POST["email"]));
	$phone_work = mysql_real_escape_string(trim(arabic_number(@$_POST["phone_work"])));
	$living = mysql_real_escape_string(trim(@$_POST["living"]));
	$neighborhood = mysql_real_escape_string(trim(@$_POST["neighborhood"]));
	$salary = mysql_real_escape_string(trim(@$_POST["salary"]));
	$blood_type = mysql_real_escape_string(trim(@$_POST["blood_type"]));
	
	$website = mysql_real_escape_string(trim(@$_POST["website"]));
	$facebook = mysql_real_escape_string(trim(@$_POST["facebook"]));
	$twitter = mysql_real_escape_string(trim(@$_POST["twitter"]));
	$linkedin = mysql_real_escape_string(trim(@$_POST["linkedin"]));
	$flickr = mysql_real_escape_string(trim(@$_POST["flickr"]));
	
	$hobbies = @$_POST["hobbies"];
	$new_hobbies = @$_POST["new_hobbies"];
	
	$pic = @$_FILES["pic"];

	// Check the uploaded file (if any).
	if (!empty($pic["name"]))
	{
		$uploaded_file = upload_file($pic);
		
		if ($uploaded_file)
		{
			$sql_optional []= "photo = '$uploaded_file'";
			
			if (!empty($member["photo"]))
			{
				unlink("views/pics/$member[photo]");
			}
		}
	}
	
	// Remove all hobbies for member
	remove_member_hobbies($member["id"]);
		
	// Add the selected hobbies.
	if (isset($hobbies))
	{
		foreach ($hobbies as $checked_hobby => $on_off)
		{
			add_hobby_to_member($checked_hobby, $member["id"]);
		}
	}
	
	foreach ($new_hobbies as $new_hobby)
	{
		if (!empty($new_hobby))
		{
			add_hobby_to_member($new_hobby, $member["id"]);
		}
	}
	
	if ($phone_home != $member["phone_home"])
	{
		$sql_optional []= "phone_home = '$phone_home'";	
	}
	
	if ($email != $member["email"])
	{
		$sql_optional []= "email = '$email'";
	}
	
	if ($phone_work != $member["phone_work"])
	{
	$sql_optional []= "phone_work = '$phone_work'";	
	}
	
	if ($blood_type != $member["blood_type"])
	{
		$sql_optional []= "blood_type = '$blood_type'";
	}

	if ($living != $member["living"])
	{
		$sql_optional []= "living = '$living'";
	}

	if ($neighborhood != $member["neighborhood"])
	{
		$sql_optional []= "neighborhood = '$neighborhood'";
	}
	if ($salary != $member["salary"])
	{
		$sql_optional []= "salary = '$salary'";
	}
	
	if ($website != $member["website"])
	{
		$sql_optional []= "website = '$website'";
	}
		
	if ($facebook != $member["facebook"])
	{
		$sql_optional []= "facebook = '$facebook'";
	}
		
	if ($twitter != $member["twitter"])
	{
		$sql_optional []= "twitter = '$twitter'";
	}
	
	if ($linkedin != $member["linkedin"])
	{
		$sql_optional []= "linkedin = '$linkedin'";
	}

	if ($flickr != $member["flickr"])
	{
		$sql_optional []= "flickr = '$flickr'";
	}

	if (isset($sql_optional))
	{
		if (count($sql_optional) > 0)
		{
			$sql_optional_query = implode(", ", $sql_optional);
			$update_optional_query = mysql_query("UPDATE member SET $sql_optional_query WHERE id = '$member[id]'")or die(mysql_error());
		}
	}

	echo success_message(
		"تم تحديث المعلومات الاختيارية بنجاح.",
		"update_optional.php?id=$member[id]"
	);
	
	return;
}
else
{
	// Get hobbies.
	$hobbies = "";
	
	$member_hobbies = get_member_hobbies($member["id"]);

	if (count($member_hobbies) > 0)
	{
		$hobbies = "<h4><i class='icon-star'></i> هواياتك</h4>";
	
		foreach ($member_hobbies as $hkey => $hname)
		{
			$hobbies .= "<input type='checkbox' name='hobbies[$hname]' checked/> $hname ";
		}
	
		$hobbies .= "<p></p>";
	}

	$recommended_hobbies = get_recommended_hobbies($member["id"]);

	if (count($recommended_hobbies) > 0)
	{
		$hobbies .= "<h4><i class='icon-thumbs-up'></i> هوايات مقترحة</h4>";
	
		foreach($recommended_hobbies as $hkey => $hname)
		{
			$hobbies .= "<input type='checkbox' name='hobbies[$hname]'/> $hname ";
		}
	
		$hobbies .= "<p></p>";
	}

	// Get photo.
	$photo = rep_photo($member["photo"], $member["gender"]);

	// Get the header.
	$header = website_header(
		"تحديث المعلومات الاختيارية للعضو $member[fullname]",
		"صفحة من أجل تحديث المعلومات الاختيارية.",
		array(
			"الزغيبي", "عائلة", "شجرة", "تحديث", "معلومات", "اختيارية"
		)
	);

	// Get the content.
	$content = template(
		"views/update_optional_member.html",
		array(
			"salary" => $member["salary"], "blood_type" => $member["blood_type"],
			"living" => $member["living"], "phone_home" => $member["phone_home"],
			"email" => $member["email"], "phone_work" => $member["phone_work"],
			"neighborhood" => $member["neighborhood"], "website" => $member["website"],
			"facebook" => $member["facebook"], "twitter" => $member["twitter"],
			"linkedin" => $member["linkedin"], "flickr" => $member["flickr"],
			"hobbies" => $hobbies, "photo" => $photo, "id" => $member["id"]
		)
	);
	
	// Get the footer.
	$footer = website_footer();
	
	echo $header;
	echo $content;
	echo $footer;
}
