<?php

require_once("inc/functions.inc.php");

$user = user_information();

// If the user is a visitor.
if ($user["group"] == "visitor" || $user["group"] == "user")
{
	redirect_to_login();
	return;
}

// If the user is an admin.
if ($user["group"] == "admin")
{
	$assigned_to_query = "";
}
else
{
	$assigned_to_query = "AND assigned_to = '$user[id]'";
}

// GET variables.
$do = @$_GET["do"];

switch ($do)
{
	case "accept":
	
		$key = mysql_real_escape_string(@$_GET["key"]);

		// Get the request information.		
		$get_pending_request = mysql_query("SELECT * FROM request WHERE random_key = '$key' AND status = 'pending' $assigned_to_query");
		$pending_request_count = mysql_num_rows($get_pending_request);
	
		if ($pending_request_count == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			// Just execute php script.
			$request = mysql_fetch_array($get_pending_request);
			$output = execute_request($request["random_key"], $user["id"]);
			$affected_info = get_member_id($request["affected_id"]);	

			// Notify.
			notify("request_accept", $request["created_by"], "تم قبول تحديثاتك على ($affected_info[fullname])", "familytree.php?id=$affected_info[id]");

			echo success_message(
				"تم قبول التحديث بنجاح.",
				"request_update.php"
			);
			
			return;
		}
	
	break;
	
	case "reject":

		$key = mysql_real_escape_string(@$_GET["key"]);
		$reason = trim(mysql_real_escape_string(@$_GET["reason"]));

		// Get the request information.
		$get_pending_request = mysql_query("SELECT * FROM request WHERE random_key = '$key' AND status = 'pending' $assigned_to_query");
		$pending_request_count = mysql_num_rows($get_pending_request);
	
		if ($pending_request_count == 0 || empty($reason))
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			// Fetch one request.
			$request = mysql_fetch_array($get_pending_request);
			$affected_info = get_member_id($request["affected_id"]);
			$now = time();
			
			$reject_request_query = mysql_query("UPDATE request SET status = 'rejected', reason = '$reason', executed = '$now', executed_by = '$user[id]' WHERE random_key = '$key'");
			
			// Notify.
			notify("request_reject", $request["created_by"], "تم رفض تحديثاتك على ($affected_info[fullname]), $reason", "familytree.php?id=$affected_info[id]");
			
			echo success_message(
				"تم رفض التحديث بنجاح.",
				"request_update.php"
			);
			
			return;
		}

	break;
	
	case "assign":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		$assign_to = mysql_real_escape_string(@$_POST["assign_to"]);
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
		
		if (empty($assign_to))
		{
			echo error_message("الرجاء إختيار المشرف الذي تريد أن تسند إليه التحديثات المعلّقة.");
			return;
		}
		
		if (count($check) > 0)
		{
			$keys = array();
			
			foreach ($check as $key => $value)
			{
				//echo $key;
				//echo "\n";
				$keys []= "random_key = '$key'";
			}
			
			$keys_query = implode(" OR ", $keys);
			$update_request_query = mysql_query("UPDATE request SET assigned_to = '$assign_to' WHERE ($keys_query)");
			
			echo success_message(
				"تم إسناد التحديثات المعلّقة إلى المشرف المحدّد بنجاح.",
				"request_update.php"
			);
			
			return;
		}
	break;
	
	default:
		// Get pendings first
		$get_pending_request = mysql_query("SELECT * FROM request WHERE status = 'pending' $assigned_to_query ORDER BY created ASC");

		if (mysql_num_rows($get_pending_request) > 0)
		{
			$pendings = "";
				
			while ($update = mysql_fetch_array($get_pending_request))
			{
				$affected_info = get_member_id($update["affected_id"]);
				$created = arabic_date(date("d M Y, H:i:s", $update["created"]));
				
				$created_by = get_user_id($update["created_by"]);
				$assigned_to = get_user_id($update["assigned_to"]);
				
				$assigned_to_username = "";
				$assigned_to_fullname = "";
				$assigned_to_link = "";
				
				if ($assigned_to)
				{
					$assigned_to_username = $assigned_to["username"];
					$assigned_to_fullname = $assigned_to["fullname"];
					$assigned_to_link = "<a href='familytree.php?id=$assigned_to[member_id]' title='$assigned_to_fullname'>$assigned_to_username</a>";
				}
				
				// Get the photo of the member.
				$pic = rep_photo($affected_info["photo"], $affected_info["gender"]);
				
				// Get the details of the request.
				$request_details_array = explode("\n", $update["description"]);
				$request_details_items = "";
				
				foreach ($request_details_array as $details)
				{
					$details = trim($details);
					
					if (!empty($details))
					{
						$request_details_items .= "<li>$details</li>";
					}
				}
				
				$request_details = "";
				
				if (!empty($request_details_items))
				{
					$request_details = "<ol>$request_details_items</ol>";
				}
				
				if ($affected_info["gender"] == 1)
				{
					$edit = "<li><a class='small button' href='update_profile_male.php?id=$affected_info[id]&qkey=$update[random_key]'><i class='icon-pencil'></i> تعديل</a> </li>";
				}
				else
				{
					// TODO:
					$edit = "";
				}
				
				$pendings .= "<tr>
					<td><input type='checkbox' name='check[$update[random_key]]' id='check_$update[id]' data-id='$update[id]' class='check' /></td>
					<td class='hide-for-small'>$pic</td>
					<td>
						<a href='familytree.php?id=$update[affected_id]'>$update[title]</a>
						<p>
							<div id='details_btn_$update[id]' class='request_details'><ul class='button-group'><li><button class='small button' type='button' onclick='show_details($update[id])' id='btn_show_details_$update[id]'>إظهار التفاصيل</button></li><li><button style='display: none;' type='button' class='success small button' id='btn_hide_details_$update[id]' onclick='hide_details($update[id])'>إخفاء التفاصيل</button></li><li><a class='secondary small button' href='familytree.php?id=$created_by[member_id]' title='بواسطة: $created_by[fullname]'>$created_by[name]</a></li><li><span class='secondary small button'>$created</span></li></ul></div>
							<div id='details_msg_$update[id]' class='request_details' style='display: none;'>$request_details</div>
						</p>
					</td>
					
					<td class='hide-for-small'>$assigned_to_link</td>
					
					<td>
						<ul class='button-group'>
							$edit
							<li id='accept_$update[id]'><a class='small button' href='request_update.php?do=accept&key=$update[random_key]'><i class='icon-ok icon-white'></i> قبول</a> </li>
							<li><a class='alert small button' href='#' onclick='reject($update[id])'><i class='icon-remove icon-white'></i> رفض</a></li>
						</ul>
						
						<div class='reject_reason'>
							<input type='hidden' id='random_key_$update[id]' value='$update[random_key]'/>
							<input type='text' placeholder='سبب الرفض (إن وجد)' id='reason_$update[id]' onkeyup='write_reason($update[id])' onkeydown='write_reason($update[id])' onkeypress='write_reason($update[id])' />
						</div>
					</td>
				</tr>\n";
			}
			
			$mods_admins_array = get_all_mods_admins();
			$mods_admins = "";
			
			foreach ($mods_admins_array as $mod_admin)
			{
				$mods_admins .= "<option value='$mod_admin[user_id]'>$mod_admin[username] ($mod_admin[member_fullname])</option>";
			}

			$assign_to_div = "
				<div class='hide-for-small row' >
					<div class='large-8 columns'>
						<ul class='button-group'>
							<li><button type='button' onclick='check_all()' class='secondary small button'>تحديد الكل</button></li>
							<li><button type='button' onclick='uncheck_all()' class='secondary small button'>إلغاء تحديد الكل</button></li>
							<li><button type='button' onclick='show_all_details()' class='secondary small button'>إظهار كل التفاصيل</button></li>
							<li><button type='button' onclick='hide_all_details()' class='secondary small button'>إخفاء كل التفاصيل</button></li>
						</ul>
					</div>
					<div class='large-4 columns'>
						<select name='assign_to'><option value=''>إسناد إلى</option>$mods_admins</select>
						<button type='submit' name='submit' class='small button' value='1'>إسناد</button>		
					</div>
				</div>";
		
		}
		else
		{
			$pendings = "<tr><td colspan='5'>لا يوجد تحديثات معلّقة مناطة بالمشرف.</td></tr>\n";
			$assign_to_div = "";
		}
		
		// Get not pending second.
		$updates_count = 100;
		$get_not_pending_query = mysql_query("SELECT * FROM request WHERE status != 'pending' ORDER BY executed DESC, created DESC LIMIT $updates_count");

		if (mysql_num_rows($get_not_pending_query) == 0)
		{
			$not_pendings = "<tr><td colspan='4'>لا يوجد طلبات جديدة مقبولة أو مرفوضة.</td>\n";
		}
		else
		{
			$not_pendings = "";
			
			while ($not_pending_request = mysql_fetch_array($get_not_pending_query))
			{
				$affected_info = get_member_id($not_pending_request["affected_id"]);
				$created = arabic_date(date("d M Y, H:i:s", $not_pending_request["created"]));
				$executed = arabic_date(date("d M Y, H:i:s", $not_pending_request["executed"]));
				
				$created_by = get_user_id($not_pending_request["created_by"]);
				$executed_by = get_user_id($not_pending_request["executed_by"]);
				
				if ($executed_by)
				{
					$time_diff = "<div class='success round label'>" . time_diff($not_pending_request["executed"], $not_pending_request["created"]) . "</div>";
					$executed_by = "<li class='hide-for-small'><a class='secondary small button' href='familytree.php?id=$executed_by[member_id]' title='نفّذ بواسطة: $executed_by[fullname]'>$executed_by[username]</a></li><li class='hide-for-small'><span class='secondary small button'>$executed</span></li>";
				}
				else
				{
					$time_diff = "-";
					$executed_by = "";
				}
				
				$status = ($not_pending_request["status"] == "rejected") ? "<span class='round alert label'>مرفوض</span>" : "<span class='round success label'>مقبول</span>";
				$img = ($affected_info["gender"] == 1) ? "male.png" : "female.png";
				$created = arabic_date(date("d M Y, H:i:s", $not_pending_request["created"]));
				$executed = arabic_date(date("d M Y, H:i:s", $not_pending_request["executed"]));
				
				$not_pendings .= "<tr>
					<td class='hide-for-small'><img src='views/img/$img' border='0' /></td>
					<td><a href='familytree.php?id=$affected_info[id]'>$not_pending_request[title]</a><p><ul class='button-group'><li><a class='secondary small button' href='familytree.php?id=$created_by[member_id]' title='أضيف بواسطة: $created_by[fullname]'>$created_by[username]</a></li><li class='hide-for-small'><span class='secondary small button'>$created</span></li>$executed_by</ul></p></td>
					<td>$status</td>
					<td class='hide-for-small'><span class='error'>$not_pending_request[reason]</span></td>
					<td class='hide-for-small'>$time_diff</td>
				</tr>\n";
			}
		}
		
		// Get the number of the pending updates.
		$pending_updates_count = mysql_num_rows($get_pending_request);
		$pending_updates_title = ($pending_updates_count == 0) ? "" : "($pending_updates_count)";

		// Get the header
		$header = website_header(
			"تحديثات معلّقة $pending_updates_title",
			"صفحة من أجل قبول أو رفض التحديثات المعلّقة",
			array(
				"عائلة", "الزغيبي", "شجرة", "تحديثات", "معلّقة"
			)
		);
			
		// Get the content.
		$content = template(
			"views/request_update.html",
			array(
				"pendings_count" => $pending_updates_title,
				"pendings" => $pendings,
				"not_pendings" => $not_pendings,
				"updates_count" => $updates_count,
				"assign_to_div" => $assign_to_div
			)
		);
			
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
		
	break;
}
