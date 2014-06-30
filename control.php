<?php

require_once("inc/functions.inc.php");

$user = user_information();

$do = @$_GET["do"];
$alert = @$_GET["alert"];
$redirect = @$_POST["redirect"];

switch ($do)
{
	case "delete_member":
	
		// If the user is not admin
		if ($user["group"] != "admin")
		{
			echo error_message("Cannot access this area.");
			return;
		}

		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		// After submitting the form.
		if (!empty($submit))
		{
			$id = trim(mysql_real_escape_string(@$_POST["id"]));
			$member = get_member_id($id);
			
			if ($member == false)
			{
				echo error_message("Cannot find the member.");
				return;
			}
			
			// Delete the member
			delete_member($id);

			echo success_message(
				"تم حذف العضو بنجاح.",
				"familytree.php?id=$member[father_id]"
			);
			
			return;
		}	
		// Before submitting the form.
		else
		{
			$id = mysql_real_escape_string(@$_GET["id"]);
			$member = get_member_id($id);
			
			if ($member == false)
			{
				echo "Cannot find the member.";
				return;
			}

			$template = template(
				"views/delete_member.html",
				array(
					"id" => $id
				)
			);
			
			echo $template;
		}
	break;

	default: case "view_member":

		$tribe_id = mysql_real_escape_string(@$_GET["tribe_id"]);
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		if (empty($id))
		{
			echo "404 Not Found.";
			return;
		}

		$member = get_member_id($id);
		
		if ($member == false)
		{
			echo "404 Not Found.";
			return;
		}
		else
		{
			// Check if the member is a female.
			if ($member["gender"] == 0)
			{
				if ($user["group"] == "visitor")
				{
					echo "403 Access Denied";
					return;
				}

				// Check if the user is admin
				$is_admin = ($user["group"] == "admin");

				// Check if the user is seeing his/her profile.
				$is_me = ($member["id"] == $user["member_id"]);

				// Check if the moderator is accepted (if any).
				$is_accepted_moderator = is_accepted_moderator($member["id"]);

				// Check if the user is relative to the member.
				$is_relative_user = is_relative_user($member["id"]);
				
				if (!$is_admin && !$is_me && !$is_accepted_moderator && !$is_relative_user)
				{
					echo "403 Access Denied";
					return;
				}
			}
			
			$page = view_member_page($id, $tribe_id);
			echo $page;
		}

	break;
}
