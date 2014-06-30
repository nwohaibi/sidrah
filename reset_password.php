<?php

session_start();

require_once("inc/functions.inc.php");

$action = mysql_real_escape_string(@$_GET["action"]);

$session_verification_code = @$_SESSION["zoghiby_verification_code"];
$session_mobile = @$_SESSION["zoghiby_mobile"];

switch ($action)
{
	default: case "enter_mobile":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$mobile = mysql_real_escape_string(arabic_number(@$_POST["mobile"]));
			$captcha = mysql_real_escape_string(arabic_number(@$_POST["captcha"]));

			if (empty($mobile) || empty($captcha))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$md5_captcha = md5_salt($captcha);
	
			// Check if the captcha is missed up.
			if ($_SESSION["reset_password"] != $md5_captcha)
			{
				echo error_message("الرجاء إدخال رمز التحقّق بشكل صحيح.");
				return;
			}
			
			// Check if a user with the giving information does exist.
			$get_user_query = mysql_query("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = '$mobile'");
	
			if (mysql_num_rows($get_user_query) > 0)
			{
				$user_info = mysql_fetch_array($get_user_query);
				
				$verification_code = sprintf("%04d", rand(0, 9999));				
				$hashed_verification_code = md5_salt($verification_code);

				$_SESSION["zoghiby_verification_code"] = $hashed_verification_code;
				$_SESSION["zoghiby_mobile"] = $user_info["mobile"];

				// Send an sms.
				$content = "رمز التأكيد\n$verification_code";
				$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
				
				// Redirect to enter code page.
				redirect("reset_password.php?action=enter_code");
				return;
			}
			else
			{
				echo error_message("المعلومات المُدخلة غير صحيحة.");
				return;
			}
		}
		else
		{
			if (!empty($session_verification_code))
			{
				redirect("reset_password.php?action=enter_code");
				return;
			}
			
			// Get the header.
			$header = website_header(
				"نسيت كلمة المرور",
				"صفحة من أجل توليد كلمة مرور جديدة.",
				array(
					"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
				)
			);

			// Get the template.
			$content = template(
				"views/reset_password_mobile.html"
			);
	
			// Get the footer.
			$footer = website_footer();
	
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	
	break;
	
	case "enter_code":
	
		if (empty($session_verification_code) || empty($session_mobile))
		{
			redirect("reset_password.php?action=enter_mobile");
			return;
		}

		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$verification_code = mysql_real_escape_string(arabic_number(@$_POST["verification_code"]));
			$hashed_verification_code = md5_salt($verification_code);
			
			if ($hashed_verification_code != $session_verification_code)
			{
				echo error_message("رمز التأكيد غير صحيح.");
				return;
			}

			// Check if a user with the giving information does exist.
			$get_user_query = mysql_query("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = '$session_mobile'");
	
			if (mysql_num_rows($get_user_query) > 0)
			{
				// Get user information.
				$user_info = mysql_fetch_array($get_user_query);
		
				// Generate a new password.
				$password = generate_key();
				$hashed_password = md5_salt($password);
		
				// Update a password.
				$update_password_query = mysql_query("UPDATE user SET password = '$hashed_password' WHERE username = '$user_info[username]'");
		
				// Send an sms.
				$content = "اسم المستخدم\n$user_info[username]\n\nكلمة المرور الجديدة\n$password";
				$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
	
				// Update the value of sms received.
				$update_sms_received_query = mysql_query("UPDATE user SET sms_received = '$sms_received' WHERE id = '$user_info[user_id]'");
		
				unset($_SESSION["zoghiby_verification_code"]);
				unset($_SESSION["zoghiby_mobile"]);
		
				echo success_message(
					"تم توليد كلمة مرور جديدة.",
					"logout.php"
				);
			}
		}
		else
		{
			// Get the header.
			$header = website_header(
				"نسيت كلمة المرور",
				"صفحة من أجل توليد كلمة مرور جديدة.",
				array(
					"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
				)
			);

			// Get the template.
			$content = template(
				"views/reset_password_code.html"
			);
	
			// Get the footer.
			$footer = website_footer();
	
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	
	break;
}

/*
$submit = mysql_real_escape_string(@$_POST["submit"]);

if (!empty($submit))
{
	$username = trim(mysql_real_escape_string(@$_POST["username"]));
	$mobile = (int) trim(mysql_real_escape_string(arabic_number(@$_POST["mobile"])));
	
	// Check if the username is empty or mobile.
	if (empty($username) || empty($mobile))
	{
		echo error_message("اسم المستخدم أو كلمة المرور فارغة.");
		return;
	}

	// Check if a user with the giving information does exist.
	$get_user_query = mysql_query("SELECT member.mobile as mobile, user.username as username, user.id as user_id FROM member, user WHERE user.member_id = member.id AND member.mobile = '$mobile' AND user.username = '$username'");
	
	if (mysql_num_rows($get_user_query) > 0)
	{
		// Get user information.
		$user_info = mysql_fetch_array($get_user_query);
		
		// Generate a new password.
		$password = generate_key();
		$hashed_password = md5_salt($password);
		
		// Update a password.
		$update_password_query = mysql_query("UPDATE user SET password = '$hashed_password' WHERE username = '$user_info[username]'");
		
		// Send an sms.
		$content = "كلمة المرور الجديدة\n$password";
		$sms_received = send_sms(array("966" . $user_info["mobile"]), $content);
	
		// Update the value of sms received.
		$update_sms_received_query = mysql_query("UPDATE user SET sms_received = '$sms_received' WHERE id = '$user_info[user_id]'");
		
		echo success_message(
			"تم توليد كلمة مرور جديدة.",
			"familytree.php"
		);echo error_message("المعلومات المُدخلة غير صحيحة.");
		return;
	}
	else
	{
		echo error_message("المعلومات المُدخلة غير صحيحة.");
		return;
	}
}
else
{
	// Get the header.
	$header = website_header(
		"نسيت كلمة المرور",
		"صفحة من أجل توليد كلمة مرور جديدة.",
		array(
			"الزغيبي", "عائلة", "نسيت", "كلمة", "المرور"
		)
	);

	// Get the template.
	$content = template(
		"views/reset_password.html"
	);
	
	// Get the footer.
	$footer = website_footer();
	
	// Print the page.
	echo $header;
	echo $content;
	echo $footer;
}
*/
