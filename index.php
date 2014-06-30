<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

// Set the values of Ramadan question.
$ramadan_question = "";

if ($user["group"] == "visitor")
{
	
	$inside = template("views/inside_not_logged.html");
}
else
{	
	// Get the media
	$media = media();

	// Get the current day.
	$time = time();
	$events_limit = 10;
			
	$miladi_day = date("d", $time);
	$miladi_month = date("m", $time);
	$miladi_year = date("Y", $time);

	$hijri_date = date_info($miladi_day, $miladi_month, $miladi_year);

	$current_hijri_day = $hijri_date["hijri_day"];
	$current_hijri_month = $hijri_date["hijri_month"];
	$current_hijri_year = $hijri_date["hijri_year"];
	
	// Set the date of today as an int.
	$now_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
		
	// Get the current events.
	$get_current_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) = $now_date_int ORDER BY id DESC LIMIT $events_limit");
	$current_events_count = mysql_num_rows($get_current_events_query);
		
	if ($current_events_count == 0)
	{
		$current_events = "<li>لا يوجد مناسبات حاليّة.</li>";
	}	
	else
	{
		$current_events = "<ul>";
		
		while ($current_event = mysql_fetch_array($get_current_events_query))
		{
			$current_events .= "<li><a href='calendar.php?action=view_event&id=$current_event[id]' class='whitelink'>$current_event[title]</a> <span class='datetime'>(في $current_event[day]/$current_event[month]/$current_event[year])</span></li>";
		}
		
		$current_events .= "</ul>";
	}
	
	// Get the future events.
	$get_future_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) > $now_date_int ORDER BY id DESC LIMIT $events_limit");
	$future_events_count = mysql_num_rows($get_future_events_query);
	
	if ($future_events_count == 0)
	{
		$future_events = "<li>لا يوجد مناسبات مستقبليّة.</li>";
	}	
	else
	{
		$future_events = "<ul>";
		
		while ($future_event = mysql_fetch_array($get_future_events_query))
		{
			$future_events .= "<li><a href='calendar.php?action=view_event&id=$future_event[id]' class='whitelink'>$future_event[title]</a> <span class='datetime'>(في $future_event[day]/$future_event[month]/$future_event[year])</span></li>";
		}
		
		$future_events .= "</ul>";
	}

	// Get the past events.
	$get_past_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) < $now_date_int ORDER BY id DESC LIMIT $events_limit");
	$past_events_count = mysql_num_rows($get_past_events_query);
		
	if ($past_events_count == 0)
	{
		$past_events = "<li>لا يوجد مناسبات ماضية.</li>";
	}	
	else
	{
		$past_events = "<ul>";
		
		while ($past_event = mysql_fetch_array($get_past_events_query))
		{
			$past_events .= "<li><a href='calendar.php?action=view_event&id=$past_event[id]' class='whitelink'>$past_event[title]</a> <span class='datetime'>(في $past_event[day]/$past_event[month]/$past_event[year])</span></li>";
		}
			
		$past_events .= "</ul>";
	}
	
	$inside = template(
		"views/dashboard.html",
		array(
			"media" => $media,
			"current_events" => $current_events,
			"future_events" => $future_events,
			"past_events" => $past_events
		)
	);
	
	//$family_council = "<li><a href='family_council.php' class='main_family_council'>مجلس العائلة <i class='icon-chevron-down icon-white'></i></a><ul class='popup_menu'><li><a href='secretariat.php'>أمانة المجلس</a></li> <li><a href='family_dean.php'>عميد العائلة</a></li> <li><a href='regulations.php'>اللائحة التظيمية</a></li> <li><a href='committees.php'>اللجان</a></li></ul></li>";
	// TODO: Uncomment when committees have been released. $congratulations = get_committees_nominee_congratulations();
	$congratulations = "";
	
	/*
	if ($current_hijri_month == 8)
	{
		// Get the not answered question.
		$get_ramadan_question_query = mysql_query("SELECT * FROM ramadan_question WHERE id NOT IN (SELECT question_id FROM member_question WHERE member_id = '$user[member_id]') AND (day <= $current_hijri_day) ORDER BY day DESC");
	
		if (mysql_num_rows($get_ramadan_question_query) > 0)
		{
			$fetch_question = mysql_fetch_array($get_ramadan_question_query);
			$answers = array();
	
			// Get the answers.		
			for ($i=1; $i<=4; $i++)
			{
				if (!empty($fetch_question["answer$i"]))
				{
					$answers[$i] = $fetch_question["answer$i"];
				}
			}
		
			$answers_html = "";
		
			// Walk up-on the answers.
			foreach ($answers as $key => $value)
			{
				$answers_html .= "<input type='radio' name='answer' value='$key' id='answer$key' /> $value<br />";
			}
	
			// Get Ramadan question(s).
			$ramadan_question = template(
				"views/ramadan_question.html",
				array(
					"question_id" => $fetch_question["id"],
					"question" => $fetch_question["question"],
					"answers" => $answers_html,
					"positive_message" => $fetch_question["positive_message"],
				)
			);
		}
	}
	*/
}

$ramadan_question = "";
$logged = logged_in_box();
$media = media();

// Get the template of the main page.
$content = template(
	"views/main_page.html",
	array(
		"logged" => $logged,
		"inside" => $inside,
		"ramadan_question" => $ramadan_question,
		// TODO: Uncomment when committees have been released. "family_council" => $family_council,
		"version" => version
	)
);

echo $content;

