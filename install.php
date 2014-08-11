<?php

// install file for familytree script.
// @author Hussam Al-Zughaibi <hossam_zee@yahoo.com>

define("mobile_test", "");
define("config_filename", "inc/config.inc.php");

$current_stage = install_get_current_stage();
$stage = addslashes(@$_GET["stage"]);

switch ($stage)
{
	case "login": case "view_member": case "delete_member": case "update_stage_to_sms_sending": case "send_sms_message": case "update_stage_to_launch":
		// DO NOT DO ANYTHING.
	break;
	
	default:
		$stage = $current_stage;
	break;
}

switch ($stage)
{
	default: case "configurations":
		
		$submit = addslashes(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			// If the user submitted the form.
			$db_server = trim(addslashes(@$_POST["db_server"]));
			$db_username = trim(addslashes(@$_POST["db_username"]));
			$db_password = trim(addslashes(@$_POST["db_password"]));
			$db_name = trim(addslashes(@$_POST["db_name"]));
			$sms_username = trim(addslashes(@$_POST["sms_username"]));
			$sms_password = trim(addslashes(@$_POST["sms_password"]));
			$sms_sender = trim(addslashes(@$_POST["sms_sender"]));
			$main_tribe_name = trim(addslashes(@$_POST["main_tribe_name"]));
			
			// Check the inputs first.
			if (empty($db_server) || empty($db_username) || empty($db_name) || empty($sms_username) || empty($sms_password) || empty($sms_sender) || empty($main_tribe_name))
			{
				echo install_error_message("الرجاء إكمال جميع الحقول.");
				return;
			}
			
			// Check if the database connection is correct.
			$db_link = @mysql_connect($db_server, $db_username, $db_password);
			
			if (!$db_link)
			{
				echo install_error_message("الرجاء التأكد من إعدادت خادم قاعدة البيانات.");
				return;
			}
			
			// Check if the database name is correct.
			$db_select = @mysql_select_db($db_name, $db_link);
			
			if (!$db_select)
			{
				echo install_error_message("الرجاء التأكد من إعدادات اسم قاعدة البيانات.");
				return;
			}
			
			// Check if the SMS configurations is correct.
			$sms_result = 1; // TODO: install_send_sms($sms_username, $sms_password, $sms_sender, 112233, array(mobile_test), "السلام عليكم");
			
			if ($sms_result != 1)
			{
				$sms_error_message = install_sms_messages($sms_result);
				echo install_error_message($sms_error_message);
				return;
			}
	
			// Everything is okay.
			// 1. Create config.inc.php file.
			install_create_config_inc(
				$db_server,
				$db_username,
				$db_password,
				$db_name,
				$sms_username,
				$sms_password,
				$sms_sender,
				$main_tribe_name
			);
			
			// 2. Create database tables.
			install_create_tables();
			
			// Redirect the user.
			echo install_success_message(
				"تم إنشاء ملف الإعدادت و جداول قاعدة البيانات بنجاح.",
				"install.php"
			);
		}
		else
		{
			// Get the content.
			$content = install_template(
				"views/install_configurations.html"
			);
			
			// Get the header.
			$header = install_header(
				"إعدادات شجرة العائلة"
			);
			
			// Get the footer.
			$footer = install_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
		
	break;
	
	case "admin_creation":
		
		$submit = addslashes(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$admin_username = trim(addslashes(@$_POST["admin_username"]));
			$admin_password = trim(addslashes(@$_POST["admin_password"]));
			
			if (empty($admin_username) || empty($admin_password))
			{
				echo install_error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			// Otherwise, create the (temp) admin.
			$username = $admin_username;
			$password = install_sha1_salt($admin_password);
			
			// Insert a new user into user table.
			$insert_user = mysql_query("INSERT INTO user (username, password, usergroup) VALUES ('$username', '$password', 'admin')");
			
			// Login.
			install_login($username, $password);

			// Update config variable.
			install_update_config_variable("family_tree_stage", "family_initialization");
			
			// Redirect the user.
			echo install_success_message(
				"تم إنشاء المدير المؤقت بنجاح.",
				"install.php"
			);
		}
		else
		{	
			// Get the content.
			$content = install_template(
				"views/install_temp_admin.html"
			);
			
			// Get the header.
			$header = install_header(
				"إعدادات المدير المؤقت"
			);
			
			// Get the footer.
			$footer = install_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
		
	break;
	
	case "family_initialization":
	
		install_redirect_to_login_or_not();
		
		$main_tribe_name = main_tribe_name;
		
		// Get the id of the member.
		$id = addslashes(@$_GET["id"]);
		$id = empty($id) ? "1" : $id;
		
		// Display the family tree.
		$members_json = install_get_member_children_json(main_tribe_id);

		// Get the javascript file.
		$js = install_template(
			"views/js/install_familytree_spacetree.js",
			array(
				"id" => $id,
				"members_json" => $members_json
			)
		);
		
		// Get the content.
		$content = install_template(
			"views/install_familytree.html"
		);
		
		// Get the header.
		$header = install_header(
			"شجرة عائلة $main_tribe_name",
			$js
		);
		
		// Get the footer.
		$footer = install_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "sms_sending":
		
		install_redirect_to_login_or_not();
		
		$submit = addslashes(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			echo "Sb";
		}
		else
		{
			// Get the content.
			$content = install_template(
				"views/install_send_sms.html",
				array(
					"main_tribe_name" => main_tribe_name
				)
			);
			
			// Get the header.
			$header = install_header(
				"إرسال رسائل العضويات"
			);
			
			// Get the footer.
			$footer = install_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "launch":
		echo install_success_message(
			"تهانينا، تم تنصيب شجرة العائلة بنجاح.",
			"index.php"
		);
	break;
	
	case "login":
	
		$submit = addslashes(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$username = trim(addslashes(@$_POST["username"]));
			$password = trim(addslashes(@$_POST["password"]));
		
			// Check if the username or the password is empty.
			if (empty($username) || empty($password))
			{
				echo install_error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			$sha1_password = install_sha1_salt($password);
			
			// Check if the user information is correct.
			$get_user_info_query = mysql_query("SELECT * FROM user WHERE username = '$username' AND password = '$sha1_password'");
	
			if (mysql_num_rows($get_user_info_query) == 0)
			{
				echo install_error_message("اسم المستخدم أو كلمة المرور غير صحيحة.");
				return;
			}
			
			// Login.
			install_login($username, $sha1_password);
			
			echo install_success_message(
				"تمت عملية تسجيل الدخول بنجاح.",
				"install.php"
			);
		}
		else
		{
			// Get the content.
			$content = install_template(
				"views/install_login.html"
			);
			
			// Get the header.
			$header = install_header(
				"تسجيل الدخول"
			);
			
			// Get the footer.
			$footer = install_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "view_member":

		$id = addslashes(@$_GET["id"]);
		$submit = addslashes(@$_POST["submit"]);
		
		// Search for the member id.
		$get_member_query = mysql_query("SELECT member.id AS id, member.name AS name, member.gender AS gender, member.fullname AS fullname, member.mobile AS mobile, user.usergroup AS usergroup, member.is_alive AS is_alive FROM member, user WHERE member.id = user.member_id AND member.id = '$id'");
		
		if (mysql_num_rows($get_member_query) == 0)
		{
			echo "Not Found.";
			return;
		}
		
		// Get the member information.
		$member = mysql_fetch_array($get_member_query);
		
		if (!empty($submit))
		{
			$is_alive = addslashes(@$_POST["is_alive"]);
			$usergroup = addslashes(@$_POST["usergroup"]);
			$mobile = addslashes(@$_POST["mobile"]);
			$sons = @$_POST["sons"];
			$daughters = @$_POST["daughters"];
			
			// Initialize a new variable for holding valid children.
			$children = array();
			
			// Start with sons.
			foreach ($sons as $son)
			{
				$normalized_name = install_normalize_name($son);
				
				if (!empty($normalized_name))
				{
					$children []= array(
						"name" => $normalized_name,
						"gender" => 1
					);
				}
			}
			
			// Then with daughters.
			foreach ($daughters as $daughter)
			{
				$normalized_name = install_normalize_name($daughter);
				
				if (!empty($normalized_name))
				{
					$children []= array(
						"name" => $normalized_name,
						"gender" => 0
					);
				}
			}

			foreach ($children as $child)
			{
				install_add_child($id, $child["name"], $child["gender"], $member["fullname"]);
			}
			
			// Update the values of the member.
			$update_member_query = mysql_query("UPDATE member SET is_alive = '$is_alive', mobile = '$mobile' WHERE id = '$id'");
			
			// Update the values of the user.
			$update_user_query = mysql_query("UPDATE user SET usergroup = '$usergroup' WHERE member_id = '$id'");
			
			// Done, redirect to the previous page.
			echo install_success_message(
				"تم تحديث بيانات الاسم بنجاح.",
				"install.php?id=$id"
			);
		}
		else
		{
			// Get the template of viewing the member.
			$content = install_template(
				"views/install_view_member.html",
				array(
					"id" => $member["id"],
					"gender" => $member["gender"],
					"name" => $member["name"],
					"fullname" => $member["fullname"],
					"mobile" => $member["mobile"],
					"usergroup" => $member["usergroup"],
					"is_alive" => $member["is_alive"],
				)
			);
		
			echo $content;
		}
		
	break;
	
	case "delete_member":
		echo "DELETE";
	break;
	
	case "update_stage_to_sms_sending":
	
		// Update config variable.
		install_update_config_variable("family_tree_stage", "sms_sending");
		install_redirect("install.php");
	break;
	
	case "send_sms_message":

		$message = addslashes(@$_POST["message"]);
		$method = addslashes(@$_POST["method"]);
		$offset = (int) addslashes(@$_POST["offset"]);
		$query = "SELECT id, name, mobile FROM member WHERE is_alive = '1' AND mobile != '0'";

		if ($method == "count")
		{
			$mysql_query = mysql_query($query);
			echo mysql_num_rows($mysql_query);
			return;
		}
		else if ($method == "offset")
		{
			// Send an SMS message.
			$mysql_query = mysql_query("$query LIMIT 1 OFFSET $offset");
			$member = mysql_fetch_array($mysql_query);

			// Generate a username and a password.
			$username = "$member[name]$member[id]";
			$password = install_generate_key();
			$sha1_password = install_sha1_salt($password);

			// Set the password for the current user.
			$set_user_password_query = mysql_query("UPDATE user SET username = '$username', password = '$sha1_password' WHERE member_id = '$member[id]'");

			// Replace the text with the password.
			$message = install_text_replace(
				$message,
				array(
					"username" => $username,
					"password" => $password
				)
			);
			
			// Save all passwords.
			$fopen = fopen("logs/passwords.log", "a+");
			fwrite($fopen, "$username => $password ($sha1_password)\n");
			fclose($fopen);

			$mobile = "966" . $member["mobile"];
			$status = install_send_sms(sms_username, sms_password, sms_sender, sms_delete_key, array($mobile), install_arabic_number($message));

			// Update sms received.
			if ($status == 1)
			{
				$update_sms_received_query = mysql_query("UPDATE user SET sms_received = 1 WHERE member_id = '$member[id]'");
			}

			echo $status;
			return;
		}
	break;
	
	case "update_stage_to_launch":
	
		// Update config variable.
		install_update_config_variable("family_tree_stage", "launch");
		install_redirect("install.php");
	break;
}

// functions.
// private
function install_get_current_stage()
{
	// Get the current stage to start from it.
	if (!file_exists(config_filename))
	{
		return "configurations";
	}
	else
	{
		require_once(config_filename);
	
		// Connect to the database.
		$link = mysql_connect(database_server, database_username, database_password);
		mysql_select_db(database_name, $link);
		
		return family_tree_stage;
	}
}

// private
function install_template($name, $replacements = null)
{
	$content = file_get_contents($name);

	if ($replacements != null && is_array($replacements))
	{
		foreach ($replacements as $key => $value)
		{
			$content = str_replace("{" . $key . "}", $value, $content);
		}
	}
	
	$content = str_replace("{main_tribe_name}", @main_tribe_name, $content);
	$content = str_replace("{family_tree_author}", @family_tree_author, $content);
	
	return $content;
}

// private
function install_header($title = "", $js = "")
{
	$js_html = "";

	if (!empty($js))
	{
		$js_html = '<script type="text/javascript">' . "\n$js\n" . '</script>';
	}

	$header = install_template(
		"views/install_header.html",
		array(
			"title" => $title,
			"js" => $js_html
		)
	);
	
	return $header;
}

// private
function install_footer()
{
	$footer = install_template(
		"views/install_footer.html"
	);
	
	return $footer;
}

// private
function install_error_message($message = "")
{
	// Get the referer.
	$referer = @$_SERVER["HTTP_REFERER"];

	$template = install_template(
		"views/error_message.html",
		array(
			"message" => $message,
			"referer" => $referer,
		)
	);
	
	return $template;
}

// private
function install_success_message($message = "", $redirect = "")
{
	$template = install_template(
		"views/success_message.html",
		array(
			"message" => $message,
			"redirect" => $redirect
		)
	);
	
	return $template;
}

// private
function install_redirect($page)
{
	header("location: $page");
}

// private
function install_sha1_salt($string)
{
	return sha1($string . familytree_salt);
}

// private
function install_login($username, $password)
{
	$cookie_time = time() + 21600;

	// Save the cookie of the user information.
	setcookie("install_familytree_username", $username, $cookie_time);
	setcookie("install_familytree_password", $password, $cookie_time);
}

function install_is_logged_in()
{
	if (!isset($_COOKIE["install_familytree_username"]) && !isset($_COOKIE["install_familytree_password"]))
	{
		return false;
	}
	
	// Escape the data, someone might attack server.
	$cookie_username = addslashes($_COOKIE["install_familytree_username"]);
	$cookie_password = addslashes($_COOKIE["install_familytree_password"]);
	
	// Otherwise, there is information.
	$get_user_info_query = mysql_query("SELECT * FROM user WHERE username = '$cookie_username' AND password = '$cookie_password'");
	
	if (mysql_num_rows($get_user_info_query) == 0)
	{
		return false;
	}
	else
	{
		return true;
	}
}

// private
function install_redirect_to_login_or_not()
{
	if (install_is_logged_in() == false)
	{
		install_redirect("install.php?stage=login");
	}
}

// private
function install_update_config_variable($config_variable, $to_stage)
{
	// Get the current family tree strage.
	$config_contents = file_get_contents(config_filename);
	preg_match('/define\("' . $config_variable . '", "(.*)"\);/isU', $config_contents, $match);
	$family_tree_stage = $match[1];
	
	// Set some variables.
	$old_variable_string = sprintf('define("%s", "%s");', $config_variable, $family_tree_stage);
	$new_variable_string = sprintf('define("%s", "%s");', $config_variable, $to_stage);
	
	// Replace the old with the new one.
	$config_contents = str_replace($old_variable_string, $new_variable_string, $config_contents);
	
	// Save the changes.
	$fhandler = fopen(config_filename, "w");
	fwrite($fhandler, $config_contents);
	fclose($fhandler);
}

// private
function install_sms_messages($result)
{
	$messages = array(
		"-2" => "لم يتم الاتصال بالخادم.",
		"-1" => "لم يتم الإتصال بقاعدة البيانات.",
		"1" => "تم الإرسال.",
		"2" => "لا يوجد رصيد.",
		"3" => "الرصيد غير كافي.",
		"4" => "رقم الجوّال غير متوفر.",
		"5" => "كلمة المرور غير صحيحة.",
		"6" => "صفحة الإنترنت غير فعّالة، حاول مرّة أخرى.",
		"13" => "اسم المرسل غير مقبول",
		"14" => "اسم المرسل المستخدم غير معرّف.",
		"15" => "الأرقام المُرسل لها غير صحيحة أو فارغة.",
		"16" => "اسم المرسل فارغ.",
		"17" => "نص الرسالة غير مشفّر بالشكل الصحيح."
	);
	
	return $messages[$result];
}

// private
function install_normalize_name($fullname)
{
	$fullname = preg_replace("/[^أاإآبتثجحخدذرزسشصضطظعغفقكلمنهوؤيئءىﻻﻵة ]/u", "", $fullname);

	// Remove [Ben, Bent]
	$fullname = preg_replace("/(بنت|بن) /", '', $fullname);

	// Normalize [Abd]
	$fullname = preg_replace("/عبد /", "عبد", $fullname);

	// Special names
	$fullname = preg_replace("/(إ|أ|ا)نس/", "أنس", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)يمن/", "أيمن", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)يوب/", "أيوب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)حمد/", "أحمد", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)ديب/", "أديب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)سامة/", "أسامة", $fullname); // Could cause a bug.
	$fullname = preg_replace("/(إ|أ|ا)هاب/", "إيهاب", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)ياد/", "إياد", $fullname);
	$fullname = preg_replace("/(أ|إ|ا)براهيم/", "إبراهيم", $fullname);
	$fullname = preg_replace("/(إ|أ|ا)لهام/", "إلهام", $fullname);

	// Remove [Al]
	//$fullname = preg_replace("/\s(ال)/", " ", $fullname);

	// Many spaces to one space
	$fullname = preg_replace("/[\s|\t]+/", " ", $fullname);
	$fullname = trim($fullname);
	
	return $fullname;
}

// private
function install_arabic_number($number)
{
	$number = str_replace("٠", "0", $number);
	$number = str_replace("۰", "0", $number);
	
	$number = str_replace("١", "1", $number);
	$number = str_replace("۱", "1", $number);
	
	$number = str_replace("٢", "2", $number);
	$number = str_replace("۲", "2", $number);
	
	$number = str_replace("٣", "3", $number);
	$number = str_replace("۳", "3", $number);
	
	$number = str_replace("٤", "4", $number);
	$number = str_replace("٥", "5", $number);
	$number = str_replace("٦", "6", $number);
	$number = str_replace("٧", "7", $number);
	
	$number = str_replace("٨", "8", $number);
	$number = str_replace("۸", "8", $number);
	
	$number = str_replace("٩", "9", $number);
	$number = str_replace("۹", "9", $number);
	
	return $number;
}

// private
function install_send_sms($sms_username, $sms_password, $sms_sender, $sms_delete_key, $to = array(), $content)
{	
	$message = install_string_to_unicode($content);

	$url = "http://www.mobily.ws/api/msgSend.php";
	
	$post = array(
		"mobile" => $sms_username,
		"password" => $sms_password,
		"numbers" => implode(",", $to),
		"sender" => $sms_sender,
		"msg" => $message,
		"timeSend" => 0,
		"dateSend" => 0,
		"applicationType" => 24,
		"domainName" => $_SERVER["SERVER_NAME"],
		"deleteKey" => $sms_delete_key
	);

	// Create a string containing the post variables.
	$post_string = http_build_query($post);

	// Create a new connection.
	$connection = curl_init();

	curl_setopt($connection, CURLOPT_URL, $url);
	curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($connection, CURLOPT_HEADER, 0);
	curl_setopt($connection, CURLOPT_TIMEOUT, 5);
	curl_setopt($connection, CURLOPT_POST, 1);
	curl_setopt($connection, CURLOPT_POSTFIELDS, $post_string);
	
	// Execute the options up-there.
	$result = curl_exec($connection);
	
	return (int)($result == 1);
}

// private
function install_string_to_unicode($string)
{
	// Replace string rather than La.
	$string = str_replace("ﻷ", "لأ", $string);
	$string = str_replace("ﻻ", "لا", $string);
	$string = str_replace("ﻵ", "لآ", $string);
	$string = str_replace("ﻹ", "لإ", $string);

	// Convert the string from utf-8 to windows-1256.
	$string = iconv("utf-8", "windows-1256", $string);
	
	$chars_unicodes = array(
		"،" => "060C",
		"؛" => "061B",
		"؟" => "061F",
		"ء" => "0621",
		"آ" => "0622",
		"أ" => "0623",
		"ؤ" => "0624",
		"إ" => "0625",
		"ئ" => "0626",
		"ا" => "0627",
		"ب" => "0628",
		"ة" => "0629",
		"ت" => "062A",
		"ث" => "062B",
		"ج" => "062C",
		"ح" => "062D",
		"خ" => "062E",
		"د" => "062F",
		"ذ" => "0630",
		"ر" => "0631",
		"ز" => "0632",
		"س" => "0633",
		"ش" => "0634",
		"ص" => "0635",
		"ض" => "0636",
		"ط" => "0637",
		"ظ" => "0638",
		"ع" => "0639",
		"غ" => "063A",
		"ف" => "0641",
		"ق" => "0642",
		"ك" => "0643",
		"ل" => "0644",
		"م" => "0645",
		"ن" => "0646",
		"ه" => "0647",
		"و" => "0648",
		"ى" => "0649",
		"ي" => "064A",
		"ـ" => "0640",
		"ً" => "064B",
		"ٌ" => "064C",
		"ٍ" => "064D",
		"َ" => "064E",
		"ُ" => "064F",
		"ِ" => "0650",
		"ّ" => "0651",
		"ْ" => "0652",
		"!" => "0021",
		"\"" => "0022",
		"#" => "0023",
		"$" => "0024",
		"%" => "0025",
		"&" => "0026",
		"'" => "0027",
		"(" => "0028",
		")" => "0029",
		"*" => "002A",
		"+" => "002B",
		"," => "002C",
		"-" => "002D",
		"." => "002E",
		"/" => "002F",
		"0" => "0030",
		"1" => "0031",
		"2" => "0032",
		"3" => "0033",
		"4" => "0034",
		"5" => "0035",
		"6" => "0036",
		"7" => "0037",
		"8" => "0038",
		"9" => "0039",
		":" => "003A",
		";" => "003B",
		"<" => "003C",
		"=" => "003D",
		">" => "003E",
		"?" => "003F",
		"@" => "0040",
		"A" => "0041",
		"B" => "0042",
		"C" => "0043",
		"D" => "0044",
		"E" => "0045",
		"F" => "0046",
		"G" => "0047",
		"H" => "0048",
		"I" => "0049",
		"J" => "004A",
		"K" => "004B",
		"L" => "004C",
		"M" => "004D",
		"N" => "004E",
		"O" => "004F",
		"P" => "0050",
		"Q" => "0051",
		"R" => "0052",
		"S" => "0053",
		"T" => "0054",
		"U" => "0055",
		"V" => "0056",
		"W" => "0057",
		"X" => "0058",
		"Y" => "0059",
		"Z" => "005A",
		"[" => "005B",
		"\\" => "005C",
		"]" => "005D",
		"^" => "005E",
		"_" => "005F",
		"`" => "0060",
		"a" => "0061",
		"b" => "0062",
		"c" => "0063",
		"d" => "0064",
		"e" => "0065",
		"f" => "0066",
		"g" => "0067",
		"h" => "0068",
		"i" => "0069",
		"j" => "006A",
		"k" => "006B",
		"l" => "006C",
		"m" => "006D",
		"n" => "006E",
		"o" => "006F",
		"p" => "0070",
		"q" => "0071",
		"r" => "0072",
		"s" => "0073",
		"t" => "0074",
		"u" => "0075",
		"v" => "0076",
		"w" => "0077",
		"x" => "0078",
		"y" => "0079",
		"z" => "007A",
		"{" => "007B",
		"|" => "007C",
		"}" => "007D",
		"~" => "007E",
		"©" => "00A9",
		"®" => "00AE",
		"÷" => "00F7",
		"×" => "00F7",
		"§" => "00A7",
		" " => "0020",
		"\n" => "000D",
		"\r" => "000A",
	);

	$windows_1256_chars_unicode = array();

	// Convert all keys to windows-1256.
	foreach ($chars_unicodes as $key => $value)
	{
		$new_key = iconv("utf-8", "windows-1256", $key);
		$windows_1256_chars_unicode[$new_key] = $value;
	}

	$string_length = strlen($string);
	$output = "";
	
	// Start to walk up-on the string.
	for ($i=0; $i<$string_length; $i++)
	{
		$output .= $windows_1256_chars_unicode[$string[$i]];
	}
	
	return $output;
}

// private
function install_add_child($father_id, $name, $gender, $fullname_postfix)
{
	$main_tribe_id = main_tribe_id;
	
	// Check if the member already exists.
	$get_member_query = mysql_query("SELECT * FROM member WHERE tribe_id = '$main_tribe_id' AND father_id = '$father_id' AND name = '$name' AND gender = '$gender'");
	
	if (mysql_num_rows($get_member_query) > 0)
	{
		return;
	}
	
	// Otherwise, insert a new member.
	$member_fullname = "$name $fullname_postfix";
	$insert_member_query = mysql_query("INSERT INTO member (tribe_id, father_id, name, gender, fullname) VALUES ('$main_tribe_id', '$father_id', '$name', '$gender', '$member_fullname')");
	$member_id = mysql_insert_id();
	
	// And, insert a new user.
	$insert_user_query = mysql_query("INSERT INTO user (member_id, username) VALUES ('$member_id', '$name{$member_id}')");
}

// private
function install_text_replace($text, $replacements = null)
{
	if ($replacements != null && is_array($replacements))
	{
		foreach ($replacements as $key => $value)
		{
			$text = str_replace("{" . $key . "}", $value, $text);
		}
	}
	
	return $text;
}

// private
function install_generate_key($length = 4, $use_numbers = true, $use_capital_letters = false, $use_small_letters = false, $use_symbols = false)
{
	$key_components = array();
	
	if ($use_numbers)
	{
		$key_components []= "1234567890";
	}
	
	if ($use_capital_letters)
	{
		$key_components []= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	}
	
	if ($use_small_letters)
	{
		$key_components []= "abcdefghijklmnopqrstuvwxyz";
	}

	if ($use_symbols)
	{
		$key_components []= "@$#!";
	}

	$components_count = count($key_components);

	// Do a bit of shuffling.
	shuffle($key_components);

	$key = "";

	for ($i=0; $i<$length; $i++)
	{
		$component_index = ($i % $components_count);
		$component_length = strlen($key_components[$component_index]);
		$random = rand(0, $component_length-1);
		$key .= $key_components[$component_index]{$random};
	}
	
	return $key;
}

// private
function install_create_config_inc($db_server, $db_username, $db_password, $db_name, $sms_username, $sms_password, $sms_sender, $main_tribe_name)
{
	$fhandler = fopen(config_filename, "w+");
	
	// Generate some keys.
	$sms_delete_key = install_generate_key(5);
	$family_tree_salt = install_generate_key(64, true, true, true);
	$script_url = "http://" . $_SERVER["SERVER_NAME"] . str_replace("/install.php", "", $_SERVER["REQUEST_URI"]);

	// Configurations.
	$configurations = array(

		"Database" => array(
			"database_server" => $db_server,
			"database_username" => $db_username,
			"database_password" => $db_password,
			"database_name" => $db_name
		),
		
		"SMS" => array(
			"sms_username" => $sms_username,
			"sms_password" => $sms_password,
			"sms_sender" => $sms_sender,
			"sms_delete_key" => $sms_delete_key
		),
		
		"Tribe" => array(
			"main_tribe_name" => $main_tribe_name,
			"main_tribe_id" => 1
		),
		
		"Salt" => array(
			"familytree_salt" => $family_tree_salt
		),
		
		"Url" => array(
			"script_url" => $script_url
		),
		
		"User" => array(
			"user_min_length" => 4,
			"user_max_length" => 40
		),
		
		"Member" => array(
			"member_min_names" => 4
		),
		
		"Version" => array(
			"version" => "1.0.0"
		),
		
		"Stage" => array(
			"family_tree_stage" => "admin_creation"
		),
		
		"Author" => array(
			"family_tree_author" => "حسام الزغيبي"
		),
	);
	
	$fconfig_content = "<?php\n";
	
	foreach ($configurations as $config_group => $configs)
	{
		$fconfig_content .= "\n// $config_group\n";
		
		foreach ($configs as $cvar => $cval)
		{
			$fconfig_content .= "define(\"$cvar\", \"$cval\");\n";
		}
	}
	
	// Write configs in the configuration file.
	fwrite($fhandler, $fconfig_content);
	fclose($fhandler);
}

// private
function install_get_member_children_json($tribe_id, $father_id = -1)
{
	$conditions = array();
	
	if ($tribe_id != null)
	{
		$conditions []= "tribe_id = '$tribe_id'";
	}

	$conditions []= "father_id = '$father_id'";

	$condition = implode("AND ", $conditions);
	$get_children_query = mysql_query("SELECT id, father_id, name, gender FROM member WHERE $condition ORDER BY id");
	$return = "";
	
	if (mysql_num_rows($get_children_query) > 0)
	{
		while ($child = mysql_fetch_array($get_children_query))
		{
			$return .= sprintf("{id:\t\"%s\",\nname:\t\"<div class='node'>%s</div>\",\nchildren:[", $child["id"], $child["name"]);		
			
			if ($child["gender"] == 1)
			{
				$return .= install_get_member_children_json(null, $child["id"]);
			}

			$return .= "]},";
		}
	}
	
	return substr($return, 0, strlen($return)-1);	
}

// private
function install_create_tables()
{
	require_once(config_filename);

	// Connect to database.
	$link = mysql_connect(database_server, database_username, database_password);
	mysql_select_db(database_name, $link);
	
	// Get tribe variables.
	$main_tribe_id = main_tribe_id;
	$main_tribe_name = main_tribe_name;
	
	// SQL.
	$sql = "
		CREATE TABLE IF NOT EXISTS company (
		  id int(8) NOT NULL AUTO_INCREMENT,
		  `type` int(8) NOT NULL,
		  `name` varchar(250) NOT NULL,
		  PRIMARY KEY (id),
		  KEY `name` (`name`)
		);

		CREATE TABLE IF NOT EXISTS feedback (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  `type` enum('idea','bug','praise') NOT NULL,
		  `page` varchar(300) NOT NULL,
		  content text NOT NULL,
		  user_agent varchar(300) NOT NULL,
		  http_referer varchar(300) NOT NULL,
		  created int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY `page` (`page`),
		  KEY user_agent (user_agent),
		  KEY http_referer (http_referer)
		);

		CREATE TABLE IF NOT EXISTS hobby (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(150) NOT NULL,
		  rank int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY `name` (`name`)
		);

		CREATE TABLE IF NOT EXISTS married (
		  id int(8) NOT NULL AUTO_INCREMENT,
		  husband_id int(8) NOT NULL,
		  wife_id int(8) NOT NULL,
		  marital_status enum('married','divorced','widow','widower') NOT NULL DEFAULT 'married',
		  PRIMARY KEY (id),
		  KEY husband_id (husband_id),
		  KEY wife_id (wife_id)
		);

		CREATE TABLE IF NOT EXISTS member (
		  id int(8) NOT NULL AUTO_INCREMENT,
		  tribe_id int(11) NOT NULL,
		  mother_id int(8) NOT NULL DEFAULT '-1',
		  father_id int(8) NOT NULL,
		  descenders int(8) NOT NULL,
		  alive_descenders int(8) NOT NULL,
		  `name` varchar(150) NOT NULL,
		  nickname varchar(100) NOT NULL,
		  fullname varchar(350) NOT NULL,
		  gender int(4) NOT NULL DEFAULT '1',
		  blood_type varchar(10) NOT NULL,
		  dob date NOT NULL,
		  age int(11) NOT NULL,
		  pob varchar(150) NOT NULL,
		  is_alive tinyint(1) NOT NULL DEFAULT '1',
		  dod date NOT NULL,
		  location varchar(150) NOT NULL,
		  living varchar(20) NOT NULL,
		  neighborhood varchar(100) NOT NULL,
		  education int(4) NOT NULL,
		  major varchar(200) NOT NULL,
		  company_id int(8) NOT NULL DEFAULT '-1',
		  job_title varchar(250) NOT NULL,
		  salary int(11) NOT NULL DEFAULT '0',
		  marital_status int(4) NOT NULL DEFAULT '0',
		  mobile int(11) NOT NULL,
		  phone_home int(11) NOT NULL,
		  phone_work int(11) NOT NULL,
		  fax int(11) NOT NULL,
		  email varchar(250) NOT NULL,
		  website varchar(300) NOT NULL,
		  facebook varchar(250) NOT NULL,
		  twitter varchar(100) NOT NULL,
		  linkedin varchar(200) NOT NULL,
		  flickr varchar(200) NOT NULL,
		  flag int(4) NOT NULL,
		  photo varchar(300) NOT NULL,
		  cv text NOT NULL,
		  notes text NOT NULL,
		  visible tinyint(1) NOT NULL DEFAULT '1',
		  privacy_mother enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
		  privacy_partners enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
		  privacy_daughters enum('all','members','related_circle','admins') NOT NULL DEFAULT 'related_circle',
		  privacy_mobile enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_phone_home enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_phone_work enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_fax enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_email enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_dob enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_pob enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_dod enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_age enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_education enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_major enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_company enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_job_title enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_marital_status enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_blood_type enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_location enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_living enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_neighborhood enum('all','members','related_circle','admins') NOT NULL DEFAULT 'members',
		  privacy_salary enum('all','members','related_circle','admins') NOT NULL DEFAULT 'admins',
		  privacy_website enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  privacy_facebook enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  privacy_twitter enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  privacy_linkedin enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  privacy_flickr enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  privacy_hobby enum('all','members','related_circle','admins') NOT NULL DEFAULT 'all',
		  created int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY `name` (`name`),
		  KEY nickname (nickname),
		  KEY fullname (fullname),
		  KEY email (email),
		  KEY job_title (job_title),
		  KEY gender (gender)
		);

		CREATE TABLE IF NOT EXISTS member_hobby (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  member_id int(11) NOT NULL,
		  hobby_id int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY member_id (member_id),
		  KEY hobby_id (hobby_id)
		);

		CREATE TABLE IF NOT EXISTS notification (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  `type` enum('request_receive','request_reject','request_accept','password_change','event_add','event_react_come','event_react_not_come','comment_response','comment_like') NOT NULL,
		  user_id int(11) NOT NULL,
		  content varchar(300) NOT NULL,
		  is_read tinyint(1) NOT NULL DEFAULT '0',
		  link varchar(300) NOT NULL,
		  created int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY user_id (user_id),
		  KEY content (content),
		  KEY link (link)
		);

		CREATE TABLE IF NOT EXISTS request (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  random_key varchar(10) NOT NULL,
		  title varchar(250) NOT NULL,
		  description text NOT NULL,
		  phpscript text NOT NULL,
		  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
		  reason text NOT NULL,
		  affected_id int(11) NOT NULL,
		  created_by int(11) NOT NULL,
		  assigned_to int(11) NOT NULL,
		  created int(11) NOT NULL,
		  executed int(11) NOT NULL,
		  executed_by int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY random_key (random_key),
		  KEY title (title)
		);

		CREATE TABLE IF NOT EXISTS tribe (
		  id int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(100) NOT NULL,
		  PRIMARY KEY (id),
		  KEY `name` (`name`)
		);

		CREATE TABLE IF NOT EXISTS `user` (
		  id int(8) NOT NULL AUTO_INCREMENT,
		  username varchar(100) NOT NULL,
		  `password` varchar(40) NOT NULL,
		  usergroup enum('user','moderator','admin') NOT NULL,
		  member_id int(11) NOT NULL,
		  assigned_root_id int(11) NOT NULL,
		  sms_received tinyint(1) NOT NULL,
		  first_login tinyint(1) NOT NULL DEFAULT '1',
		  last_login_time int(11) NOT NULL,
		  created int(11) NOT NULL,
		  PRIMARY KEY (id),
		  KEY username (username,`password`),
		  KEY username_2 (username),
		  KEY `password` (`password`),
		  KEY usergroup (usergroup)
		);

		CREATE TABLE IF NOT EXISTS `event` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `day` int(11) NOT NULL,
		  `month` int(11) NOT NULL,
		  `year` int(11) NOT NULL,
		  `title` varchar(350) NOT NULL,
		  `content` text NOT NULL,
		  `type` enum('meeting','wedding','death','baby_born','news') NOT NULL,
		  `location` varchar(350) NOT NULL,
		  `latitude` varchar(50) NOT NULL,
		  `longitude` varchar(50) NOT NULL,
		  `author_id` int(11) NOT NULL,
		  `time` varchar(20) NOT NULL,
		  `created` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);

		CREATE TABLE IF NOT EXISTS `event_reaction` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `event_id` int(11) NOT NULL,
		  `member_id` int(11) NOT NULL,
		  `reaction` enum('come','not_come') NOT NULL,
		  `created` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);

		CREATE TABLE IF NOT EXISTS `comment` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `event_id` int(11) NOT NULL,
		  `content` text NOT NULL,
		  `author_id` int(11) NOT NULL,
		  `created` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);

		CREATE TABLE IF NOT EXISTS `comment_like` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `comment_id` int(11) NOT NULL,
		  `member_id` int(11) NOT NULL,
		  `created` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		);
		
		INSERT INTO tribe (id, name) VALUES ('$main_tribe_id', '$main_tribe_name');
		INSERT INTO member (id, tribe_id, name, father_id, gender, fullname) VALUES (1, '$main_tribe_id', '$main_tribe_name', '-1', '1', '$main_tribe_name');
		INSERT INTO user (member_id, username) VALUES ('1', '{$main_tribe_name}1');
	";
	
	// Explode sql into many queries.
	$sql_queries = explode(";", $sql);
	
	foreach ($sql_queries as $sql_query)
	{
		$execute_query = mysql_query($sql_query);
	}
}

