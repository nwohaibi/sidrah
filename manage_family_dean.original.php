<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();
$action = mysql_real_escape_string(@$_GET["action"]);

if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

switch ($action)
{
	default: case "view_deanship_periods":

		// Get all deanship periods.
		$get_all_deanship_periods_query = mysql_query("SELECT * FROM deanship_period");
		$deanship_periods_html = "";
	
		if (mysql_num_rows($get_all_deanship_periods_query) == 0)
		{
			$deanship_periods_html = "<tr><td colspan='5' class='error'><i class='icon-exclamation-sign'></i> لا يوجد فترات عمادة بعد.</td></tr>";
		}
		else
		{
			// Start walking up-on deanship periods.
			while ($deanship_period = mysql_fetch_array($get_all_deanship_periods_query))
			{
				$status = rep_deanship_period_status($deanship_period["status"]);
				
				switch ($deanship_period["status"])
				{
					case "nomination":
						$change_to = "<a href='manage_family_dean.php?action=start_deanship_period_voting&deanship_period_id=$deanship_period[id]' class='sidrah_btn positive'><i class='icon-thumbs-up icon-white'></i> البدء في مرحلة التصويت</a>";
					break;
					
					case "voting":
						$change_to = "<a href='manage_family_dean.php?action=finish_deanship_period_voting&deanship_period_id=$deanship_period[id]' class='sidrah_btn positive'><i class='icon-star icon-white'></i> إنهاء مرحلة التصويت</a>";
					break;
					
					case "ongoing":
						$change_to = "<a href='manage_family_dean.php?action=finish_deanship_period&deanship_period_id=$deanship_period[id]' class='sidrah_btn positive'><i class='icon-ok-circle icon-white'></i> إنهاء فترة العمادة</a>";
					break;
					
					case "finished":
						$change_to = "";
					break;
				}
				
				$deanship_periods_html .= "<tr><td><input type='checkbox' name='check[$deanship_period[id]]' /></td><td><i class='icon-calendar'></i> $deanship_period[from_period]</td><td><i class='icon-calendar'></i> $deanship_period[to_period]</td><td><b>($status)</b></td><td><ul class='ul_inline'><li><a href='manage_family_dean.php?action=edit_deanship_period&id=$deanship_period[id]' class='sidrah_btn'><i class='icon-pencil'></i> تعديل</a></li> <li>$change_to</li></ul></td></tr>";
			}
		}
		
		// Set the content of the template.
		$content = template(
			"views/view_deanship_periods.html",
			array(
				"deanship_periods" => $deanship_periods_html
			)
		);
		
		// Set the header.
		$header = website_header(
			"إدارة فترات عمادة العائلة",
			"صفحة من أجل إدارة فترات عمادة العائلة",
			array(
				"عائلة", "الزغيبي", "إدارة", "فترات", "العمادة"
			)
		);
		
		// Set the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "add_deanship_period":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		$from_d = lz(mysql_real_escape_string(trim(@$_POST["from_d"])));
		$from_m = lz(mysql_real_escape_string(trim(@$_POST["from_m"])));
		$from_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["from_y"]))), 4);

		$to_d = lz(mysql_real_escape_string(trim(@$_POST["to_d"])));
		$to_m = lz(mysql_real_escape_string(trim(@$_POST["to_m"])));
		$to_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["to_y"]))), 4);
		
		// If the admin submitted the form.
		if (!empty($submit))
		{
			// Check if the user did not specify the dates.
			if ((empty($from_d) || intval($from_d) == 0) || (empty($from_m) || intval($from_m) == 0) || (empty($from_y) || intval($from_y) == 0))
			{
				echo error_message("الرجاء إدخال تاريخ بدء فترة العمادة.");
				return;
			}
			
			if ((empty($to_d) || intval($to_d) == 0) || (empty($to_m) || intval($to_m) == 0) || (empty($to_y) || intval($to_y) == 0))
			{
				echo error_message("الرجاء إدخال تاريخ نهاية فترة العمادة.");
				return;
			}
			
			$from = "$from_y-$from_m-$from_d";
			$to = "$to_y-$to_m-$to_d";
			$now = time();
			
			// Insert a new deanship period.
			$insert_deanship_period = mysql_query("INSERT INTO deanship_period (from_period, to_period, status, created) VALUES ('$from', '$to', 'nomination', '$now');");
			
			echo success_message(
				"تم إضافة فترة العمادة بنجاح.",
				"manage_family_dean.php"
			);
		}
		
	break;
	
	case "edit_deanship_period":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		// Check if the deanship period exists.
		$get_deanship_period_query = mysql_query("SELECT * FROM deanship_period WHERE id = '$id'");
		
		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		$deanship_period = mysql_fetch_array($get_deanship_period_query);
		
		// If the admin submitted the form.
		if (!empty($submit))
		{
			$from_d = lz(mysql_real_escape_string(trim(@$_POST["from_d"])));
			$from_m = lz(mysql_real_escape_string(trim(@$_POST["from_m"])));
			$from_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["from_y"]))), 4);

			$to_d = lz(mysql_real_escape_string(trim(@$_POST["to_d"])));
			$to_m = lz(mysql_real_escape_string(trim(@$_POST["to_m"])));
			$to_y = lz(mysql_real_escape_string(trim(arabic_number(@$_POST["to_y"]))), 4);
		
			// Check if the user did not specify the dates.
			if ((empty($from_d) || intval($from_d) == 0) || (empty($from_m) || intval($from_m) == 0) || (empty($from_y) || intval($from_y) == 0))
			{
				echo error_message("الرجاء إدخال تاريخ بدء فترة العمادة.");
				return;
			}
			
			if ((empty($to_d) || intval($to_d) == 0) || (empty($to_m) || intval($to_m) == 0) || (empty($to_y) || intval($to_y) == 0))
			{
				echo error_message("الرجاء إدخال تاريخ نهاية فترة العمادة.");
				return;
			}
			
			$from = "$from_y-$from_m-$from_d";
			$to = "$to_y-$to_m-$to_d";
			
			// Update the deanship period.
			$update_deanship_period = mysql_query("UPDATE deanship_period SET from_period = '$from', to_period = '$to' WHERE id = '$deanship_period[id]'");
			
			echo success_message(
				"تم تعديل فترة العمادة بنجاح.",
				"manage_family_dean.php"
			);
		}
		else
		{
		
			list($from_y, $from_m, $from_d) = sscanf($deanship_period["from_period"], "%d-%d-%d");
			list($to_y, $to_m, $to_d) = sscanf($deanship_period["to_period"], "%d-%d-%d");
		
			// Get the content.
			$content = template(
				"views/edit_deanship_period.html",
				array(
					"id" => $deanship_period["id"],
					"from" => $deanship_period["from_period"],
					"to" => $deanship_period["to_period"],
					"from_d" => (int) $from_d,
					"from_m" => (int) $from_m,
					"from_y" => (int) $from_y,
					"to_d" => (int) $to_d,
					"to_m" => (int) $to_m,
					"to_y" => (int) $to_y
				)
			);
		
			// Get the header.
			$header = website_header(
				"تعديل فترة العمادة (من $deanship_period[from_period] إلى $deanship_period[to_period])",
				"صفحة من أجل تعديل فترة عمادة.",
				array(
					"عائلة", "الزغيبي", "تعديل", "فترة", "عمادة"
				)
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "update_deanship_periods":

		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$do = mysql_real_escape_string(@$_POST["do"]);
		$check = @$_POST["check"];
		
		if (empty($submit))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		if (!isset($check))
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Everything is good, almost.
		if (count($check) > 0)
		{
			foreach ($check as $k => $v)
			{
				// Delete selected deanship period.
				// TODO: Delete all related rows.
				$delete_deanship_period_query = mysql_query("DELETE FROM deanship_period WHERE id = '$k'");
			}
		}
		else
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Done.
		echo success_message(
			"تم حذف فترات العمادة المحدّدة بنجاح.",
				"manage_family_dean.php"
		);
		
		return;
	
	break;
	
	case "start_deanship_period_voting":
		
		$deanship_period_id = mysql_real_escape_string(@$_GET["deanship_period_id"]);
		
		// Check if the deanship does exist.
		$get_deanship_period_query = mysql_query("SELECT id FROM deanship_period WHERE id = '$deanship_period_id' AND status = 'nomination'");

		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Everything is almost good, get the deanship period.
		$deanship_period = mysql_fetch_array($get_deanship_period_query);
		
		// Check if there is any dean for this deanship period.
		$get_related_deans_query = mysql_query("SELECT COUNT(id) as deans_count FROM dean WHERE period_id = '$deanship_period[id]'");
		$related_deans = mysql_fetch_array($get_related_deans_query);
		$realted_deans_count = $related_deans["deans_count"];
		
		if ($realted_deans_count == 0)
		{
			echo error_message("لم يتقدم فرد بترشيح نفسه إلى عمادة العائلة.");
			return;
		}
		
		// Now, everything is very well.
		// Just update the deanship period to be in 'voting' status.
		$update_deanship_period_query = mysql_query("UPDATE deanship_period SET status = 'voting' WHERE id = '$deanship_period[id]'");
		
		// Done.
		echo success_message(
			"تم البدء بمرحلة التصويت بنجاح.",
				"manage_family_dean.php"
		);
		
		return;
	break;
	
	case "finish_deanship_period_voting":
		
		$deanship_period_id = mysql_real_escape_string(@$_GET["deanship_period_id"]);
		
		// Check if the deanship does exist.
		$get_deanship_period_query = mysql_query("SELECT id FROM deanship_period WHERE id = '$deanship_period_id' AND status = 'voting'");

		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Everything is almost good, get the deanship period.
		$deanship_period = mysql_fetch_array($get_deanship_period_query);
		
		// Check if there is any dean for this deanship period.
		$get_related_deans_query = mysql_query("SELECT COUNT(id) as deans_count FROM dean WHERE period_id = '$deanship_period[id]'");
		$related_deans = mysql_fetch_array($get_related_deans_query);
		$realted_deans_count = $related_deans["deans_count"];
		
		if ($realted_deans_count == 0)
		{
			echo error_message("لم يتقدم فرد بترشيح نفسه إلى عمادة العائلة.");
			return;
		}
		
		// Now, everything is very well.
		// Just update the deanship period to be in 'ongoing' status.
		$update_deanship_period_query = mysql_query("UPDATE deanship_period SET status = 'ongoing' WHERE id = '$deanship_period[id]'");
		
		// Select the dean with the highest voting.
		$get_highest_dean_query = mysql_query("SELECT dean_id, COUNT(id) AS votes FROM member_dean WHERE dean_id IN (SELECT id FROM dean WHERE period_id = '$deanship_period[id]') GROUP BY dean_id ORDER BY votes DESC LIMIT 1");
		
		if (mysql_num_rows($get_highest_dean_query) == 0)
		{
			echo error_message("لم يتم التصويت إلى أي عميد.");
			return;
		}
		
		$highest_dean = mysql_fetch_array($get_highest_dean_query);
		
		// Update the highest dean to be the selected dean.
		$update_highest_dean_query = mysql_query("UPDATE dean SET selected = '1' WHERE id = '$highest_dean[dean_id]'");

		// Done.
		echo success_message(
			"تم إنهاء مرحلة التصويت و تعيين عميد للعائلة بنجاح.",
				"manage_family_dean.php"
		);
		
		return;
	break;
	
	case "finish_deanship_period":
		
		$deanship_period_id = mysql_real_escape_string(@$_GET["deanship_period_id"]);
		
		// Check if the deanship does exist.
		$get_deanship_period_query = mysql_query("SELECT id FROM deanship_period WHERE id = '$deanship_period_id' AND status = 'ongoing'");

		if (mysql_num_rows($get_deanship_period_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Everything is almost good, get the deanship period.
		$deanship_period = mysql_fetch_array($get_deanship_period_query);
		
		// Now, everything is very well.
		// Just update the deanship period to be in 'finished' status.
		$update_deanship_period_query = mysql_query("UPDATE deanship_period SET status = 'finished' WHERE id = '$deanship_period[id]'");

		// Done.
		echo success_message(
			"تم إنهاء فترة العمادة بنجاح.",
				"manage_family_dean.php"
		);
		
		return;
	break;
}
