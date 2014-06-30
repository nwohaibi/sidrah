<?php

session_start();

require_once("inc/functions.inc.php");
require_once("classes/twitteroauth/twitteroauth.php");

// Get the action.
$action = mysql_real_escape_string(@$_GET["action"]);

// Get the user information.
$user = user_information();

switch ($action)
{
	default: case "authorize":
	
		$callback = mysql_real_escape_string(@$_GET["callback"]);
		$oauth_callback = script_url . "/" . $callback;

		// Start TwitterOAuth.
		$twitteroauth = new TwitterOAuth(twitter_consumer_key, twitter_consumer_secret);
		$request_token = @$twitteroauth->getRequestToken($oauth_callback);
		
		if (count($request_token) == 0)
		{
			echo error_message("لا يمكن الإتصال بتويتر (Twitter).");
			return;
		}

		// Saving them into the session.
		$_SESSION["oauth_token"] = $request_token["oauth_token"];  
		$_SESSION["oauth_token_secret"] = $request_token["oauth_token_secret"];

		if ($twitteroauth->http_code == 200)
		{
			$url = $twitteroauth->getAuthorizeURL($request_token["oauth_token"]);
			header("location: $url");
		}
		else
		{
			echo error_message("لا يمكن الإتصال بتويتر (Twitter).");
			return;
		}
	break;
	
	case "login":
	
		$oauth_verifier = @$_GET["oauth_verifier"];
		$oauth_token = @$_SESSION["oauth_token"];
		$oauth_token_secret = @$_SESSION["oauth_token_secret"];
	
		if (!empty($oauth_verifier) && !empty($oauth_token) && !empty($oauth_token_secret))
		{
			$twitteroauth = new TwitterOAuth(twitter_consumer_key, twitter_consumer_secret, $oauth_token, $oauth_token_secret);
			$access_token = $twitteroauth->getAccessToken($oauth_verifier);
			$user_info = $twitteroauth->get("account/verify_credentials"); 
	
			// Search if there is a user with this id.
			if (empty($user_info->id))
			{
				echo error_message("لم يتم تسجيل الدخول.");
				return;
			}
	
			$md5_user_id = md5_salt(mysql_real_escape_string($user_info->id));
	
			// Search for the user id.
			$select_user_query = mysql_query("SELECT * FROM user WHERE twitter_userid = '$md5_user_id' LIMIT 1");
	
			// Check if the user has not been found.
			if (mysql_num_rows($select_user_query) == 0)
			{
				echo error_message("لم يتم العثور على ربط بين اسم مستخدم تويتر و مستخدم العائلة.");
				return;
			}
			else
			{
				$cookie_time = time()+21600;
				$user = mysql_fetch_array($select_user_query);
				$member = get_member_id($user["member_id"]);

				// Save the cookie of the user information.
				setcookie("zoghiby_username", $user["username"], $cookie_time);
				setcookie("zoghiby_password", $user["password"], $cookie_time);
		
				// Update the last login time.
				$now = time();
				$twitter_screen_name = $user_info->screen_name;
				
				$update_last_login_query = mysql_query("UPDATE user SET last_login_time = '$now', twitter_oauth_token = '{$access_token['oauth_token']}', twitter_oautho_secret = '{$access_token['oauth_token_secret']}' WHERE id = '$user[id]'");
				$update_member_twitter_query = mysql_query("UPDATE member SET twitter = '$twitter_screen_name' WHERE id = '$member[id]'");
		
				if ($user["first_login"] == 1 || $member["gender"] == 0)
				{
					$gender = ($member["gender"] == 1) ? "male" : "female";
					$redirect = "update_profile_{$gender}.php?id=$member[id]";
				}
				else
				{
					$redirect = "index.php";
				}
	
				// Show message and redirect to it.
				echo success_message(
					"تمت عملية تسجيل الدخول بنجاح.",
					$redirect
				);
	
				return;
			}
		}
		else
		{
			redirect("twitter.php?action=authorize");
		}
	break;
	
	case "link":
		
		// Check if the user is not logged in.
		if ($user["group"] == "visitor")
		{
			echo error_message("الرجاء تسجيل الدخول ليتمّ الربط.");
			return;
		}
		
		$oauth_verifier = @$_GET["oauth_verifier"];
		$oauth_token = @$_SESSION["oauth_token"];
		$oauth_token_secret = @$_SESSION["oauth_token_secret"];

		if (!empty($oauth_verifier) && !empty($oauth_token) && !empty($oauth_token_secret))
		{
			$twitteroauth = new TwitterOAuth(twitter_consumer_key, twitter_consumer_secret, $oauth_token, $oauth_token_secret);
			$access_token = $twitteroauth->getAccessToken($oauth_verifier);
			$user_info = $twitteroauth->get("account/verify_credentials"); 
	
			// Search if there is a user with this id.
			if (empty($user_info->id))
			{
				echo error_message("لم يتم تسجيل الدخول.");
				return;
			}

			$md5_salt = md5_salt($user_info->id);
			
			$get_user_query = mysql_query("SELECT * FROM user WHERE twitter_userid = '$md5_salt' LIMIT 1");
			
			if (mysql_num_rows($get_user_query) == 1)
			{
				echo error_message("حساب تويتر المدخل مربوط مسبقاً مع حساب آخر.");
				return;
			}
			
			$update_user_query = mysql_query("UPDATE user SET twitter_userid = '$md5_salt', twitter_oauth_token = '{$access_token['oauth_token']}', twitter_oautho_secret = '{$access_token['oauth_token_secret']}' WHERE id = '$user[id]'");
			
			echo success_message(
				"تم ربط حسابك في تويتر بنجاح.",
				"index.php"
			);
		}
		else
		{
			echo error_message("لا يمكن تسجيل الدخول.");
			return;
		}
		
	break;
	
	case "unlink":
	
		// Check if the user is not logged in.
		if ($user["group"] == "visitor")
		{
			echo error_message("الرجاء تسجيل الدخول ليتمّ الربط.");
			return;
		}
		
		// Update the user information.
		$update_user_query = mysql_query("UPDATE user SET twitter_userid = '', twitter_oauth_token = '', twitter_oautho_secret = '' WHERE id = '$user[id]'");
	
		// Done.
		echo success_message(
			"تم إلغاء الربط مع تويتر بنجاح.",
			"index.php"
		);
	
	break;
}

