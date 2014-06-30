<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

$member = get_member_id($user["member_id"]);
$action = mysql_real_escape_string(@$_GET["action"]);

// Get the current day.
$time = time();
			
$miladi_day = date("d", $time);
$miladi_month = date("m", $time);
$miladi_year = date("Y", $time);

$hijri_date = date_info($miladi_day, $miladi_month, $miladi_year);

$current_hijri_day = $hijri_date["hijri_day"];
$current_hijri_month = $hijri_date["hijri_month"];
$current_hijri_year = $hijri_date["hijri_year"];

$events_limit = 10;
$members_limit = 10;

$arabic_days = array("سبت", "أحد", "أثنين", "ثلاثاء", "أربعاء", "خميس", "جمعة");

// Default types that are accepted. 
$accepted_types = array(
	"meeting" => "اجتماع",
	"wedding" => "زواج",
	"news" => "خبر",
	"death" => "وفاة",
	"baby_born" => "مولود",
);

switch ($action)
{
	default: case "view_one_month":
		
		$hijri_months = array(
			"محرّم", "صفر", "ربيع الأوّل", "ربيع الثاني", "جمادى الأوّل", "جمادى الثاني",
			"رجب", "شعبان", "رمضان", "شوّال", "ذو القعدة", "ذو الحجّة"
		);
		
		$hijri_month = mysql_real_escape_string(@$_GET["hijri_month"]);
		$hijri_year = mysql_real_escape_string(@$_GET["hijri_year"]);
		
		// Get the current month and year.
		if (empty($hijri_month) || empty($hijri_year))
		{
			$hijri_month = $hijri_date["hijri_month"];
			$hijri_year = $hijri_date["hijri_year"];		
		}

		$min_hijri_year = $current_hijri_year - 100;
		$max_hijri_year = $current_hijri_year + 100;
		
		// Check if the month is in the correct scope.
		if ($hijri_month < 1 || $hijri_month > 12)
		{
			echo error_message("الرجاء إدخال رقم شهر صحيح.");
			return;
		}
		
		// Check if the year is in the correct scope.
		if ($hijri_year < $min_hijri_year || $hijri_year > $max_hijri_year)
		{
			echo error_message("الرجاء إدخال رقم سنة صحيح.");
			return;
		}

		// Start printing the calendar of selected month.
		$miladi = hijri_to_miladi(1, $hijri_month, $hijri_year);
		$date_info = date_info($miladi["day"], $miladi["month"], $miladi["year"]);
		$hijri_day = $date_info["hijri_day"];

		// Convert Julian day to Unix timestamp.
		$start_time = jdtounix($date_info["julian_day"]);

		// Check if the hijri day is not the first.
		if ($hijri_day > 1)
		{
			$start_time = $start_time - (($hijri_day-1) * (24*60*60)); 
			$date_info = date_info(date("d", $start_time), date("m", $start_time), date("Y", $start_time));
		}

		$hijri_month_name = $hijri_months[$hijri_month-1];
		$weekday = $date_info["hijri_weekday"];
	
		$table  = "<h3>$hijri_month_name - $hijri_year</h3><table class='large-12 columns'>\n";
		$table .= "<tbody>";

		// Start looping about it.
		for ($day=1; $day<=$date_info["hijri_month_length"]; $day++)
		{
			$this_time = $start_time + (($day-1) * (24*60*60));
			$this_miladi_date = date("dM", $this_time);
			
			$node_additional_class = "";
			$this_today = "";
			$events = "";

			// Check if the day is equal to the current day.
			if ($day == $current_hijri_day && $hijri_month == $current_hijri_month && $hijri_year == $current_hijri_year)
			{
				$this_today = "panel";
			}
			
			// Get the events for this day.
			$get_this_day_events_query = mysql_query("SELECT COUNT(id) AS events_count FROM event WHERE day = '$day' AND month = '$hijri_month' AND year = '$hijri_year'");
			$fetch_this_day_events = mysql_fetch_array($get_this_day_events_query);
			$events_count = $fetch_this_day_events["events_count"];
			
			/*
			if ($events_count > 0)
			{
				$js_balloon = "<script type='text/javascript'>$(function(){ $('#day_balloon_$day').balloon({position: 'left', url: 'calendar.php?action=view_one_day&hijri_day=$day&hijri_month=$hijri_month&hijri_year=$hijri_year'}); });</script>";
				$events = "$js_balloon<a id='day_balloon_$day' href='javascript:void(0)' title='عرض $events_count مناسبة'><i class='icon-bell'></i> $events_count</a>";
				$node_additional_class = "calendar_node_special";
			}
			
			
			$table .= "<td class='$this_today'><div class='calendar_node $node_additional_class'><h3>$day</h3><a id='day_$day' class='button alert tiny' href='calendar.php?action=add_event&hijri_day=$day&hijri_month=$hijri_month&hijri_year=$hijri_year' title='إضافة مناسبة'>إضافة</a><div class='calendar_events'>$events&nbsp;</div></div></td>\n";
			*/
			
			$events_html = "<ul class='side-nav'>";
			
			if ($events_count == 0)
			{
				$events_html .= "<li>لا يوجد مناسبات في هذا اليوم.</li>";
			}
			else
			{
				$get_this_day_events_query = mysql_query("SELECT * FROM event WHERE day = '$day' AND month = '$hijri_month' AND year = '$hijri_year'");
				
				while ($event = mysql_fetch_array($get_this_day_events_query))
				{
					$events_html .= "<li><a href='calendar.php?action=view_event&id=$event[id]'>$event[title]</a></li>";
				}
			}

			$events_html .= "</ul>";
		
			$table .= "<tr><td class='$this_today'><div class='row'><div class='large-2 small-3 columns'><h4>$day <small>$arabic_days[$weekday]</small></h4><a class='button alert small' href='calendar.php?action=add_event&hijri_day=$day&hijri_month=$hijri_month&hijri_year=$hijri_year' title='إضافة مناسبة'>إضافة</a></div><div class='large-10 small-9 columns'>$events_html</div></div></td></tr>";
		
			// Do not pass 6, ever.
			$weekday = ($weekday+1)%7;
		}
	
		// Check if there is a previous and next month.
		$next_hijri_month = $hijri_month+1;
		$next_hijri_year = $hijri_year;
		
		if ($next_hijri_month > 12)
		{
			$next_hijri_month = 1;
			$next_hijri_year++;
		}
		
		$prev_hijri_month = $hijri_month-1;
		$prev_hijri_year = $hijri_year;
		
		if ($prev_hijri_month < 1)
		{
			$prev_hijri_month = 12;
			$prev_hijri_year--;
		}
		
		// Set the 
		$prev = ""; $next = "";
		
		if ($prev_hijri_year >= $min_hijri_year)
		{
			$prev = "<li><a class='small button' href='calendar.php?hijri_month=$prev_hijri_month&hijri_year=$prev_hijri_year'>الشهر السابق</a></li>";
		}
		
		if ($next_hijri_year <= $max_hijri_year)
		{
			$next = "<li><a class='small button' href='calendar.php?hijri_month=$next_hijri_month&hijri_year=$next_hijri_year'>الشهر التالي</a><li>";
		}

		$table .= "</tbody></table>";

		// Set the date of today as an int.
		$now_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
		
		// Get the current events.
		$get_current_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) = $now_date_int ORDER BY id DESC LIMIT $events_limit");
		$current_events_count = mysql_num_rows($get_current_events_query);
		
		if ($current_events_count == 0)
		{
			$current_events = "لا يوجد مناسبات حاليّة.";
		}	
		else
		{	
			$current_events = "";
		
			while ($current_event = mysql_fetch_array($get_current_events_query))
			{
				$current_events .= "<li><i class='icon-calendar'></i> <a href='calendar.php?action=view_event&id=$current_event[id]'>$current_event[title]</a> <small>(في $current_event[day]/$current_event[month]/$current_event[year])</small></li>";
			}
			
		}
		
		// Get the future events.
		$get_future_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) > $now_date_int ORDER BY id DESC LIMIT $events_limit");
		$future_events_count = mysql_num_rows($get_future_events_query);
		
		if ($future_events_count == 0)
		{
			$future_events = "لا يوجد مناسبات مستقبليّة.";
		}	
		else
		{	
			$future_events = "";
			
			while ($future_event = mysql_fetch_array($get_future_events_query))
			{
				$future_events .= "<li><i class='icon-calendar'></i> <a href='calendar.php?action=view_event&id=$future_event[id]'>$future_event[title]</a> <small>(في $future_event[day]/$future_event[month]/$future_event[year])</small></li>";
			}
		}

		// Get the past events.
		$get_past_events_query = mysql_query("SELECT * FROM event WHERE (day + month*29 + year*355) < $now_date_int ORDER BY id DESC LIMIT $events_limit");
		$past_events_count = mysql_num_rows($get_past_events_query);
		
		if ($past_events_count == 0)
		{
			$past_events = "لا يوجد مناسبات ماضية.";
		}	
		else
		{	
			$past_events = "";
		
			while ($past_event = mysql_fetch_array($get_past_events_query))
			{
				$past_events .= "<li><a href='calendar.php?action=view_event&id=$past_event[id]'>$past_event[title]</a> <small>(في $past_event[day]/$past_event[month]/$past_event[year])</small></li>";
			}
		}

		// Get the content.
		$content = template(
			"views/calendar.html",
			array(
				"table" => $table,
				"current_events_count" => $current_events_count,
				"current_events" => $current_events,
				"future_events_count" => $future_events_count,
				"future_events" => $future_events,
				"past_events_count" => $past_events_count,
				"past_events" => $past_events,
				"prev" => $prev,
				"next" => $next
			)
		);
		
		// Get the header.
		$header = website_header(
				"عرض المناسبات لشهر $hijri_month_name $hijri_year",
				"صفحة من أجل إضافة مناسبة في تاريخ $hijri_day/$hijri_month/$hijri_year.",
				array("عائلة", "الزغيبي", "إضافة", "مناسبة")
		);
			
		// Get the footer.
		$footer = website_footer();
			
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
		
	break;
	
	case "view_one_day":
	
		$hijri_day = mysql_real_escape_string(@$_GET["hijri_day"]);
		$hijri_month = mysql_real_escape_string(@$_GET["hijri_month"]);
		$hijri_year = mysql_real_escape_string(@$_GET["hijri_year"]);
		
		// Check if the day does exist.
 		$get_this_day_events_query = mysql_query("SELECT event.id AS id, event.title AS title, event.author_id AS author_id, event.day AS day, event.month AS month, event.year AS year, user.username AS author_username, member.fullname AS author_fullname FROM event, member, user WHERE event.author_id = member.id AND member.id = user.member_id AND event.day = '$hijri_day' AND event.month = '$hijri_month' AND event.year = '$hijri_year'");
 		$this_day_events_count = mysql_num_rows($get_this_day_events_query);
 		
 		if ($this_day_events_count == 0)
 		{
 			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
 			return;
 		}
 		
 		$this_day_events = "";
 		
 		while ($event = mysql_fetch_array($get_this_day_events_query))
 		{
 			$author_shorten_name = shorten_name($event["author_fullname"]);
	 		$this_day_events .= "<li><i class='icon-calendar'></i> <a href='calendar.php?action=view_event&id=$event[id]'>$event[title]</a> <span class='datetime'>(بواسطة <a href='familytree.php?id=$event[author_id]' title='$author_shorten_name'>$event[author_username]</a>)</span></li>";
 		}

 		
 		// Get the content.
		$content = template(
			"views/one_day_events.html",
			array(
				"this_day_events" => $this_day_events,
			)
		);
			
		// Print the page.
		echo $content;
	break;
	
	case "view_event":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the event does exist.
		$get_event_query = mysql_query("SELECT event.id AS id, event.type AS type, event.title AS title, event.content AS content, event.location AS location, event.latitude AS latitude, event.longitude AS longitude, event.created AS created, event.time AS time, event.author_id AS author_id, event.day AS day, event.month AS month, event.year AS year, user.username AS author_username, member.fullname AS author_fullname, member.photo AS author_photo, member.gender AS author_gender FROM event, member, user WHERE event.author_id = member.id AND member.id = user.member_id AND event.id = '$id'");
		
		if (mysql_num_rows($get_event_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$event = mysql_fetch_array($get_event_query);
		
		if ($event["type"] == "meeting" || $event["type"] == "wedding")
		{			
			// Get the count of the members who said will come.
			$get_all_members_come_query = mysql_query("SELECT COUNT(id) AS come_count FROM event_reaction WHERE event_id = '$event[id]' AND reaction = 'come'");
			$fetch_all_members_come = mysql_fetch_array($get_all_members_come_query);
			$come_count = $fetch_all_members_come["come_count"];
			
			if ($come_count == 0)
			{
				$come_members = "لم يقرّر أحدٌ الحضور، كُن أول من يقرّر.";
			}
			else
			{
				// Get a bunch of the members who said will come.
				$get_bunch_members_come_query = mysql_query("SELECT user.username AS username, member.fullname AS fullname, member.id AS id FROM user, member, event_reaction WHERE user.member_id = member.id AND event_reaction.member_id = member.id AND event_reaction.event_id = '$event[id]' AND reaction = 'come' ORDER BY RAND() LIMIT $members_limit");
				$bunch_members_come_count = mysql_num_rows($get_bunch_members_come_query);
			
				// To hold members.
				$come_members_array = array();
				
				while ($come_member = mysql_fetch_array($get_bunch_members_come_query))
				{
					$come_members_array []= "<a href='familytree.php?id=$come_member[id]' title='$come_member[fullname]'>$come_member[username]</a>، ";
				}
				
				$come_members = implode(" ", $come_members_array);
				
				if ($come_count > $bunch_members_come_count)
				{
					$come_diff = $come_count - $bunch_members_come_count;
					$come_members .= "و $come_diff آخرون.";
				}

			}
			
			$event_reactions = "<h5>ينوون الحضور <small>($come_count)</small></h5><p>$come_members</p>";
			
			// Get the count of the members who said will not come.
			$get_all_members_not_come_query = mysql_query("SELECT COUNT(id) AS not_come_count FROM event_reaction WHERE event_id = '$event[id]' AND reaction = 'not_come'");
			$fetch_all_members_not_come = mysql_fetch_array($get_all_members_not_come_query);
			$not_come_count = $fetch_all_members_not_come["not_come_count"];
			
			if ($not_come_count > 0)
			{
				$event_reactions .= "<h5>لا ينوون الحضور <small>($not_come_count)</small></h5><p>عسى المانع خير إن شاء الله.</p>";
			}
			
			// Set the condition for the people who have not reacted.
			$no_react_condition = "NOT IN (SELECT member_id FROM event_reaction WHERE event_id = '$event[id]')";
	
			// Get the members who did not decide yet.
			$get_all_members_no_react_query = mysql_query("SELECT COUNT(user.id) AS no_react_count FROM user, member WHERE user.member_id = member.id AND member.id AND member.gender = 1 AND member.id $no_react_condition");
			$fetch_all_members_no_react = mysql_fetch_array($get_all_members_no_react_query);
			$no_react_count = $fetch_all_members_no_react["no_react_count"];
			
			if ($no_react_count > 0)
			{
				// Get a bunch of the members who did not react.
				$get_bunch_members_no_react_query = mysql_query("SELECT user.username AS username, member.fullname AS fullname, member.id AS id FROM user, member WHERE user.member_id = member.id AND member.id AND member.gender = 1 AND member.id $no_react_condition ORDER BY RAND() LIMIT $members_limit");
				$bunch_members_no_react_count = mysql_num_rows($get_bunch_members_no_react_query);
			
				// To hold members.
				$no_react_members_array = array();
				
				while ($no_react_member = mysql_fetch_array($get_bunch_members_no_react_query))
				{
					$no_react_members_array []= "<a href='familytree.php?id=$no_react_member[id]' title='$no_react_member[fullname]'>$no_react_member[username]</a>، ";
				}
				
				$no_react_members = implode(" ", $no_react_members_array);
				
				if ($no_react_count > $bunch_members_no_react_count)
				{
					$no_react_diff = $no_react_count - $bunch_members_no_react_count;
					$no_react_members .= "و $no_react_diff آخرون.";
				}

				$event_reactions .= "<h5>لم يقرّروا حتى الآن <small>($no_react_count)</small></h5><p>$no_react_members</p>";
			}
		}
		else
		{
			$event_reactions = "";
		}
		
		// Get the time created.
		$created = arabic_date(date("d M Y, H:i:s", $event["created"]));
		
		// Get time information.
		$event_time = $event["time"];
		$event_time_array = sscanf($event_time, "%d:%d %s");
		
		// Set time.
		$hour = sprintf("%02d", $event_time_array[0]);
		$minute = sprintf("%02d", $event_time_array[1]);
		$am_pm = $event_time_array[2];
		
		// Get the time different between now and then.
		$time_diff_string = "";
		
		$event_date_int = $event["day"] + ($event["month"]*29) + ($event["year"]*355);
		$now_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
		$time_diff = abs($now_date_int - $event_date_int);
		
		if ($event_date_int > $now_date_int)
		{
			$time_diff_string = "تبقّى $time_diff يوم";
		}
		else if ($event_date_int < $now_date_int)
		{
			$time_diff_string = "مضى $time_diff يوم";
		}
		else
		{
			$time_diff_string = "جاري حالياً";
		}
		
		// Default value of actions link.
		$edit = "";
		$delete = "";
		
		// Check if the user is admin.
		if ($user["group"] == "admin")
		{
			$delete = "<a href='calendar.php?action=delete_event&id=$event[id]' class='small alert button'>حذف</a>";
		}
		
		if ($user["group"] == "admin" || $user["group"] == "moderator" || $user["id"] == $event["author_id"])
		{
			$edit = "<a href='calendar.php?action=edit_event&id=$event[id]' class='small success button'>تعديل</a>";
		}
		
		// Get the past reaction of this person, or a space to react.
		$this_event_reaction = get_event_reaction($event["id"], $member["id"], $hijri_date);
		
		if (!empty($this_event_reaction))
		{
			$this_event_reaction = "<tr>$this_event_reaction</tr>";
		}
		
		// Display map or not
		if ($event["type"] == "meeting" || $event["type"] == "wedding")
		{
			$map_comment = ""; $map_end_comment = "";
		}
		else
		{
			$map_comment = "<!--"; $map_end_comment = "-->";
		}
		
		// Replace the content of the event (if any).
		$event["content"] = replace_event_content($event["content"]);
		
		// Get the medias related.
		$media = media($event["id"]);
		
		// Get the content.
		$content = template(
			"views/view_event.html",
			array(
				"id" => $event["id"],
				"title" => $event["title"],
				"edit" => $edit,
				"delete" => $delete,
				"created" => $created,
				"content" => $event["content"],
				"location" => $event["location"],
				"latitude" => $event["latitude"],
				"longitude" => $event["longitude"],
				"day" => $event["day"],
				"month" => $event["month"],
				"year" => $event["year"],
				"hour" => $hour,
				"minute" => $minute,
				"am_pm" => $am_pm,
				"diff" => $time_diff_string,
				"author_id" => $event["author_id"],
				"author_username" => $event["author_username"],
				"author_shorten_name" => shorten_name($event["author_fullname"]),
				"author_photo" => rep_photo($event["author_photo"], $event["author_gender"]),
				"reaction" => $this_event_reaction,
				"comments" => get_event_comments($event["id"], $comments_count, $member["id"]),
				"comments_count" => $comments_count,
				"event_reactions" => $event_reactions,
				"map_comment" => $map_comment,
				"map_end_comment" => $map_end_comment,
				"media" => $media
			)
		);
		
		// Get the header.
		$header = website_header(
				$event["title"],
				"صفحة من أجل عرض مناسبة $event[title]",
				array("عائلة", "الزغيبي", "عرض", "مناسبة")
		);
			
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "add_event":
	
		// TODO: Check if the user can add an event.
		
		// Get.
		$hijri_day = mysql_real_escape_string(@$_GET["hijri_day"]);
		$hijri_month = mysql_real_escape_string(@$_GET["hijri_month"]);
		$hijri_year = mysql_real_escape_string(@$_GET["hijri_year"]);
		
		// Check if the values of the hijri are incorrect.
		$min_hijri_year = $current_hijri_year - 100;
		$max_hijri_year = $current_hijri_year + 100;
		
		// Check if the day is in the correct scope.
		if ($hijri_day < 1 || $hijri_day > 30)
		{
			echo error_message("الرجاء إدخال رقم يوم صحيح.");
			return;
		}
		
		// Check if the month is in the correct scope.
		if ($hijri_month < 1 || $hijri_month > 12)
		{
			echo error_message("الرجاء إدخال رقم شهر صحيح.");
			return;
		}
		
		// Check if the year is in the correct scope.
		if ($hijri_year < $min_hijri_year || $hijri_year > $max_hijri_year)
		{
			echo error_message("الرجاء إدخال رقم سنة صحيح.");
			return;
		}
		
		// Check if the event date is after current date.
		$current_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
		$event_date_int = $hijri_day + ($hijri_month*29) + ($hijri_year*355);
		
		if ($event_date_int > $current_date_int)
		{
			unset($accepted_types["death"]);
			unset($accepted_types["baby_born"]);
		}
		
		// Post.
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			// Do some actions.
			$type = mysql_real_escape_string(@$_POST["type"]);
			$title = trim(mysql_real_escape_string(@$_POST["title"]));
			
			$location = trim(mysql_real_escape_string(@$_POST["location"]));
			$latitude = mysql_real_escape_string(@$_POST["latitude"]);
			$longitude = mysql_real_escape_string(@$_POST["longitude"]);
			
			$hour = trim(arabic_number(mysql_real_escape_string(@$_POST["hour"])));
			$minute = trim(arabic_number(mysql_real_escape_string(@$_POST["minute"])));
			$am_pm = trim(mysql_real_escape_string(@$_POST["am_pm"]));
			$time = "";
			
			$content = trim(mysql_real_escape_string(@$_POST["content"]));
			
			$notify_all = mysql_real_escape_string(@$_POST["notify_all"]);
			
			// Check mandatory fields.
			if (empty($title) || empty($content))
			{
				echo error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			// Check if the event type is accepted.
			if (!array_key_exists($type, $accepted_types))
			{
				echo error_message("خطأ في نوع المناسبة المدخلة.");
				return;
			}
			
			if (!empty($hour))
			{
				$hour = sprintf("%02d", $hour);
				$minute = sprintf("%02d", $minute);
				$time = "$hour:$minute $am_pm";
			}
			
			// Everything is sweet.
			$now = time();
			
			$insert_event_query = mysql_query("INSERT INTO event (day, month, year, title, content, type, location, latitude, longitude, time, author_id, created) VALUES ('$hijri_day', '$hijri_month', '$hijri_year', '$title', '$content', '$type', '$location', '$latitude', '$longitude', '$time', '$member[id]', '$now')");
			$inserted_event_id = mysql_insert_id();
			
			$event_link = "calendar.php?action=view_event&id=$inserted_event_id";
			$event_desc = "تم إضافة مناسبة جديدة: $title.";
			
			if (!empty($notify_all))
			{
				// Notify all.
				notify_all("event_add", $event_desc, $event_link);
			}

			// Done.
			echo success_message(
				"تم إضافة المناسبة بنجاح، شكراً لك.",
				$event_link
			);
		}
		else
		{
			$accepted_types_string = "";
			
			foreach ($accepted_types as $accepted_type_value => $accepted_type_name)
			{
				$accepted_types_string .= "<option value='$accepted_type_value'>$accepted_type_name</option>";
			}
		
			// Get the content.
			$content = template(
				"views/add_event.html",
				array(
					"hijri_day" => $hijri_day,
					"hijri_month" => $hijri_month,
					"hijri_year" => $hijri_year,
					"accepted_types" => $accepted_types_string
				)
			);
			
			// Get the header.
			$header = website_header(
				"إضافة مناسبة في تاريخ $hijri_day/$hijri_month/$hijri_year",
				"صفحة من أجل إضافة مناسبة في تاريخ $hijri_day/$hijri_month/$hijri_year.",
				array("عائلة", "الزغيبي", "إضافة", "مناسبة")
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "edit_event":

		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the event is correct.
		$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$id'");
		
		if (mysql_num_rows($get_event_query) == 0)
		{
			echo error_message("لم يتم العثور على المناسبة.");
			return;
		}
		
		// Get the event details.
		$event = mysql_fetch_array($get_event_query);
		
		// Post.
		$submit = mysql_real_escape_string(@$_POST["submit"]);

		if (!empty($submit))
		{
			$hijri_day = trim(arabic_number(mysql_real_escape_string(@$_POST["day"])));
			$hijri_month = trim(arabic_number(mysql_real_escape_string(@$_POST["month"])));
			$hijri_year = trim(arabic_number(mysql_real_escape_string(@$_POST["year"])));

			// Check if the values of the hijri are incorrect.
			$min_hijri_year = $current_hijri_year - 100;
			$max_hijri_year = $current_hijri_year + 100;
		
			// Check if the day is in the correct scope.
			if ($hijri_day < 1 || $hijri_day > 30)
			{
				echo error_message("الرجاء إدخال رقم يوم صحيح.");
				return;
			}
		
			// Check if the month is in the correct scope.
			if ($hijri_month < 1 || $hijri_month > 12)
			{
				echo error_message("الرجاء إدخال رقم شهر صحيح.");
				return;
			}
		
			// Check if the year is in the correct scope.
			if ($hijri_year < $min_hijri_year || $hijri_year > $max_hijri_year)
			{
				echo error_message("الرجاء إدخال رقم سنة صحيح.");
				return;
			}
		
			// Check if the event date is after current date.
			$current_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
			$event_date_int = $hijri_day + ($hijri_month*29) + ($hijri_year*355);
		
			if ($event_date_int > $current_date_int)
			{
				unset($accepted_types["death"]);
				unset($accepted_types["baby_born"]);
			}

			// Do some actions.
			$type = mysql_real_escape_string(@$_POST["type"]);
			$title = trim(mysql_real_escape_string(@$_POST["title"]));
			
			$location = trim(mysql_real_escape_string(@$_POST["location"]));
			$latitude = mysql_real_escape_string(@$_POST["latitude"]);
			$longitude = mysql_real_escape_string(@$_POST["longitude"]);
			
			$hour = trim(arabic_number(mysql_real_escape_string(@$_POST["hour"])));
			$minute = trim(arabic_number(mysql_real_escape_string(@$_POST["minute"])));
			$am_pm = trim(mysql_real_escape_string(@$_POST["am_pm"]));
			$time = "";
			
			$content = trim(mysql_real_escape_string(@$_POST["content"]));
			
			// Check mandatory fields.
			if (empty($title) || empty($content))
			{
				echo error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			// Check if the event type is accepted.
			if (!array_key_exists($type, $accepted_types))
			{
				echo error_message("خطأ في نوع المناسبة المدخلة.");
				return;
			}
			
			if (!empty($hour))
			{
				$hour = sprintf("%02d", $hour);
				$minute = sprintf("%02d", $minute);
				$time = "$hour:$minute $am_pm";
			}
			
			$update_event_query = mysql_query("UPDATE event SET day = '$hijri_day', month = '$hijri_month', year = '$hijri_year', title = '$title', content = '$content', type = '$type', location = '$location', latitude = '$latitude', longitude = '$longitude', time = '$time' WHERE id = '$event[id]'")or die(mysql_error());
			
			// Done.
			echo success_message(
				"تم تحديث المناسبة بنجاح، شكراً لك.",
				"calendar.php?action=view_event&id=$event[id]"
			);
		}
		else
		{
			$accepted_types_string = "";
			
			foreach ($accepted_types as $accepted_type_value => $accepted_type_name)
			{
				$accepted_types_string .= "<option value='$accepted_type_value'>$accepted_type_name</option>";
			}
		
			$time = sscanf($event["time"], "%d:%d %s");
			
			$hour = $time[0];
			$minute = $time[1];
			$am_pm = $time[2];
		
			$latitude = empty($event["latitude"]) ? 24.6500 : $event["latitude"];
			$longitude = empty($event["longitude"]) ? 46.7100 : $event["longitude"];
		
			// Get the content.
			$content = template(
				"views/edit_event.html",
				array(
					"id" => $event["id"],
					"hijri_day" => $event["day"],
					"hijri_month" => $event["month"],
					"hijri_year" => $event["year"],
					"accepted_types" => $accepted_types_string,
					"event_type" => $event["type"],
					"latitude" => $latitude,
					"longitude" => $longitude,
					"title" => $event["title"],
					"location" => $event["location"],
					"content" => $event["content"],
					"hour" => $hour,
					"minute" => $minute,
					"am_pm" => $am_pm
				)
			);
			
			// Get the header.
			$header = website_header(
				"تعديل مناسبة :$event[title].",
				"صفحة من أجل تعديل مناسبة :$event[title].",
				array("عائلة", "الزغيبي", "إضافة", "مناسبة")
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	
	break;
	
	case "delete_event":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the user is an admin.
		if ($user["group"] != "admin")
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Check if the event does exist.
		$get_event_query = mysql_query("SELECT event.id AS id, event.title AS title, event.content AS content, event.location AS location, event.latitude AS latitude, event.longitude AS longitude, event.created AS created, event.time AS time, event.author_id AS author_id, event.day AS day, event.month AS month, event.year AS year, user.username AS author_username, member.fullname AS author_fullname, member.photo AS author_photo, member.gender AS author_gender FROM event, member, user WHERE event.author_id = member.id AND member.id = user.member_id AND event.id = '$id'");
		
		if (mysql_num_rows($get_event_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$event = mysql_fetch_array($get_event_query);
		
		// Delete the event.
		$delete_event_query = mysql_query("DELETE FROM event WHERE id = '$event[id]'");
		
		// Delete comment likes.
		$delete_comment_likes_query = mysql_query("DELETE FROM comment_like WHERE comment_id IN (SELECT id FROM comment WHERE event_id = '$event[id]')");
		
		// Delete comments related.
		$delete_comments_query = mysql_query("DELETE FROM comment WHERE event_id = '$event[id]'");
		
		// Delete reactions related.
		$delete_reactions_query = mysql_query("DELETE FROM event_reaction WHERE event_id = '$event[id]'");
		
		// Done.
		echo success_message(
			"تم حذف المناسبة بنجاح.",
			"calendar.php"
		);
	break;
	
	case "add_comment":
	
		$event_id = mysql_real_escape_string(@$_GET["event_id"]);
		
		// Check if the event does exist.
		$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$event_id'");
		
		if (mysql_num_rows($get_event_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$event = mysql_fetch_array($get_event_query);
		
		// TODO: Check if the commenting on the event is available.
		
		// Post.
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			// Do some cleaning for the comment (XSS stuff).
			$content = trim(mysql_real_escape_string(@strip_tags($_POST["content"])));

			// Insert the comment.
			$now = time();
			$insert_event_comment_query = mysql_query("INSERT INTO comment (event_id, content, author_id, created) VALUES ('$event[id]', '$content', '$member[id]', '$now')");
			$inserted_comment_id = mysql_insert_id();
			
			// Set a variable to hold notify/not-notify user ids.
			$notify_user_ids = array();
			$not_notify_user_ids = array();
		
			// Do not notify the author of the comment.
			//$not_notify_user_ids []= $user["id"];
		
			// Get the author id of the event.
			$get_event_author_query = mysql_query("SELECT id FROM user WHERE member_id = '$event[author_id]'");
			$fetch_event_author = mysql_fetch_array($get_event_author_query);
			$event_author_user_id = $fetch_event_author["id"];
			
			// Check if the author of the event is not the same with the author of the comment.
			if ($event_author_user_id != $user["id"])
			{
				$notify_user_ids []= $event_author_user_id;
			}
			
			// Set the other condition.
			$not_in_users_condition = "";
			
			// Do not notify these people.
			$not_notify_user_ids = $notify_user_ids;
			$not_notify_user_ids []= $user["id"];
			
			if (count($not_notify_user_ids) > 0)
			{
				$not_in_users = implode(", ", $not_notify_user_ids);
				$not_in_users_condition = "AND user.id NOT IN ($not_in_users)";
			}
			
			// Get the comments before this comment.
			$get_users_before_query = mysql_query("SELECT DISTINCT user.id AS id FROM comment, user WHERE comment.author_id = user.member_id AND comment.event_id = '$event[id]' AND comment.created < $now $not_in_users_condition");
			$users_before_count = mysql_num_rows($get_users_before_query);
			
			if ($users_before_count > 0)
			{
				while ($users_before = mysql_fetch_array($get_users_before_query))
				{
					$notify_user_ids []= $users_before["id"];
				}
			}
			
			// Set the notification.
			$desc = "تعليق جديد على مناسبة: $event[title]";
			$link = "calendar.php?action=view_event&id=$event[id]#comment_$inserted_comment_id";
			
			// Notify related users.
			notify_many("comment_response", $desc, $link, $notify_user_ids);
			
			// Done.
			echo success_message(
				"تم إضافة التعليق بنجاح، شكراً لك.",
				"calendar.php?action=view_event&id=$event[id]"
			);
		}
		else
		{
			// Only post page.
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
	break;
	
	case "like_comment":
	
		$comment_id = mysql_real_escape_string(@$_GET["comment_id"]);
		
		// Check if the comment does exist.
		$get_comment_query = mysql_query("SELECT comment.*, event.title AS event_title FROM comment, event WHERE event.id = comment.event_id AND comment.id = '$comment_id'");
		
		if (mysql_num_rows($get_comment_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$comment = mysql_fetch_array($get_comment_query);
		
		// Check if the member has liked this comment before.
		$get_member_likes_query = mysql_query("SELECT * FROM comment_like WHERE comment_id = '$comment[id]' AND member_id = '$member[id]'");
		
		if (mysql_num_rows($get_member_likes_query) > 0)
		{
			echo error_message("لا يمكنك أن تسجل إعجابك على التعليق مرّة أخرى.");
			return;
		}
		
		// Check if the member is the author of the comment.
		if ($member["id"] == $comment["author_id"])
		{
			echo error_message("لا يمكنك أن تسجل إعجابك بتعليقك.");
			return;
		}
		
		$now = time();
		$like_comment_query = mysql_query("INSERT INTO comment_like (comment_id, member_id, created) VALUES ('$comment[id]', '$member[id]', '$now')");
		
		// Set the notification.
		$desc = "$user[username] أُعجب بتعليقك على مناسبة: $comment[event_title].";
		$link = "calendar.php?action=view_event&id=$comment[event_id]#comment_$comment[id]";
		
		// Get the user id of the comment author.
		$get_comment_user_query = mysql_query("SELECT id FROM user WHERE member_id = '$comment[author_id]'");
		$fetch_comment_user = mysql_fetch_array($get_comment_user_query);
		
		// Notify the commenter.
		notify("comment_like", $fetch_comment_user["id"], $desc, $link);
		
		// Done.
		echo success_message(
			"تم تسجيل إعجابك بالتعليق، شكراً لك.",
			"calendar.php?action=view_event&id=$comment[event_id]"
		);

	break;
	
	case "react_event":
	
		$hijri_date_int = $current_hijri_day + ($current_hijri_month*29) + ($current_hijri_year*355);
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		$reaction = mysql_real_escape_string(@$_GET["reaction"]);
		
		// Check if the event does exist.
		$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$id' AND (day+month*29+year*355) >  $hijri_date_int");
		
		if (mysql_num_rows($get_event_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$event = mysql_fetch_array($get_event_query);
		
		// Check if the member has already react.
		$get_member_reaction_query = mysql_query("SELECT * FROM event_reaction WHERE event_id = '$event[id]' AND member_id = '$member[id]'");
		
		if (mysql_num_rows($get_member_reaction_query) > 0)
		{
			echo error_message("لقد اتخذت القرار مسبقاً.");
			return;
		}
		
		$desc = "";
		
		// Get the user id of the event author.
		$get_user_id_query = mysql_query("SELECT * FROM user WHERE member_id = '$event[author_id]'");
		$author_user = mysql_fetch_array($get_user_id_query);
		
		switch ($reaction)
		{
			case "come":
				$reaction = "come";
				$desc = "$user[username] ينوي الحضور إلى مناسبة: $event[title]";
			break;
		
			default: case "not_come":
				$reaction = "not_come";
				$desc = "$user[username] لا ينوي الحضور إلى مناسبة: $event[title]";
			break;
		}

		// React.
		$now = time();
		
		// Insert event reaction.
		$insert_event_reaction_query = mysql_query("INSERT INTO event_reaction (event_id, member_id, reaction, created) VALUES ('$event[id]', '$member[id]', '$reaction', '$now')");

		// Notify the author of the event.
		$link = "calendar.php?action=view_event&id=$event[id]";
		notify("event_react_$reaction", $author_user["id"], $desc, $link);

		// Done.
		echo success_message(
			"تم تسجيل قرارك بنجاح، شكراً لك.",
			"calendar.php?action=view_event&id=$event[id]"
		);
	break;
}
