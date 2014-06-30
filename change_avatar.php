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
	$photo = @$_FILES["photo"];

	if (!empty($photo))
	{
		$error = $photo["error"];

		if ($error == UPLOAD_ERR_OK)
		{
			$tmp_name = $photo["tmp_name"];
			$name = $photo["name"];
			$size = filesize($tmp_name);
			$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
			$uniqename = $member["id"] . ".$extension";
				
			// Check media extension.
			if (!in_array($extension, array("jpg", "jpeg", "png", "gif")))
			{
				echo error_message("الرجاء اختيار ملف بامتداد صورة.");
				return;
			}
			else
			{
				// Check media size.
				if ($size > media_max_size * 1024)
				{
					echo error_message("حجم الصورة كبير جداً.");
					return;
				}
				else
				{
					if ($extension == "jpg" || $extension == "jpeg")
					{
						$src = imagecreatefromjpeg($tmp_name);
					}
					else if ($extension == "png")
					{
						$src = imagecreatefrompng($tmp_name);
					}
					else
					{
						$src = imagecreatefromgif($tmp_name);
					}
						
					list($width, $height) = getimagesize($tmp_name);
						
					$thumb = imagecreatetruecolor(media_thumb_width, media_thumb_height);
			
					// Resample thumb photo.
					imagecopyresampled($thumb, $src, 0, 0, 0, 0, media_thumb_width, media_thumb_height, $width, $height);
		
					// Create medias.
					@unlink("views/pics/{$uniqename}");
					imagepng($thumb, "views/pics/{$uniqename}", 9);

					//  Update the profile image.
					$save_photo_query = mysql_query("UPDATE member SET photo = '$uniqename' WHERE id = '$member[id]'");
						
					// Destroy some.
					imagedestroy($src);
					imagedestroy($thumb);

					// Done.
					echo success_message(
			"تم تغيير الصورة الرمزيّة بنجاح.",
					"change_avatar.php?id=$member[id]"
					);
				}
			}
		}
		else
		{
			echo error_message("لا يمكن رفع الصورة.");
			return;
		}
	}
	else
	{
		echo error_message("الرجاء اختيار صورة ليتم رفعها.");
		return;
	}
}
else
{
	// Get the header.
	$header = website_header(
		"تغيير الصورة الرمزيّة",
		"صفحة من أجل تغيير الصورة الرمزيّة.",
		array("تغيير", "الصورة", "الرمزية", "عائلة", "الزغيبي")
	);
	
	// Get the content.
	$content = template(
		"views/change_avatar.html",
		array(
			"id" => $member["id"],
			"photo" => rep_photo($member["photo"], $member["gender"]),
			"max_size" => media_max_size
		)
	);
	
	// Get the footer.
	$footer = website_footer();
	
	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
