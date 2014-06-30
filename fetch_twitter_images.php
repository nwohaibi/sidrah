<?php

require_once("inc/functions.inc.php");
require_once("classes/codebird.php");

$consumerKey = "j4cA71mJZDPcmaomCyMyA";
$consumerSecret = "8vOjAuoJRJTmKOsdRhQKakCqGWZfU0kb72CdDAXuiI";

$accessToken = "1124206537-rVnm4vRARaaBm9h2WnMaNwOCRQi3OKI4ocIunXg";
$accessTokenSecret = "FqzR98ymfpGdAGHROiCu4tja3H6xGpm1ThWpbpw";

// Set the keys.
Codebird::setConsumerKey($consumerKey, $consumerSecret);

$cb = Codebird::getInstance();

// Set the token.
$cb->setToken($accessToken, $accessTokenSecret);

$get_twitter_members_query = mysql_query("SELECT id, fullname, twitter FROM member WHERE twitter != ''");

echo "<form action='upload_many_images.php' method='post'>";

if (mysql_num_rows($get_twitter_members_query) > 0)
{
	while ($member = mysql_fetch_array($get_twitter_members_query))
	{
		$twitter = $member["twitter"];
		$twitter = str_replace("https://twitter.com/", "", $twitter);
		$twitter = str_replace("@", "", $twitter);
	
		if (preg_match("/[a-z]+/i", $twitter))
		{
			$params = array("screen_name" => $twitter);
			$response = $cb->users_show($params);
			
			if (!property_exists($response, "profile_image_url"))
			{
				continue;
			}

			$fullname = $member["fullname"];
			
			$image = $response->profile_image_url;
			$image = str_replace("_normal", "_bigger", $image);
			
			echo "<h3>$fullname - $twitter</h3>";
			echo "<pre>$image</pre>";
			
			echo "<input type='hidden' name='image[$member[id]]' value='$image' />";
			echo "<input type='checkbox' name='check[$member[id]]' checked />";
			
			echo "<img src='$image' />";
			
			flush();
		}
	}
}

echo "<br /><br /><input type='submit' name='submit' value='OK' />";
echo "</form>";

