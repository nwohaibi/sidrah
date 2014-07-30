<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

// Get variables.
$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	case "check_user_availability":

		// Check if the user is a visitor.
		if ($user["group"] == "visitor")
		{
			echo "MustLogin";
			return;
		}
	
		// Get variables.
		$username = trim(mysql_real_escape_string(@$_GET["name"]));
		$new_username = trim(mysql_real_escape_string(@$_GET["new_username"]));
		$name = trim(mysql_real_escape_string(@$_GET["name"]));
		
		if (check_user_availability($username, $new_username, $name))
		{
			echo "Available";
			return;
		}
		else
		{
			echo "NotAvailable";
			return;
		}
	break;
	
	case "send_sms_message":

		$to = mysql_real_escape_string(@$_POST["to"]);
		$message = mysql_real_escape_string(@$_POST["message"]);
		$method = mysql_real_escape_string(@$_POST["method"]);
		$offset = (int) mysql_real_escape_string(@$_POST["offset"]);

		// Get the prepared relation depending on the id (to).
		$get_prepared_relation_query = mysql_query("SELECT * FROM prepared_relation WHERE id = '$to'");

		if (mysql_num_rows($get_prepared_relation_query) == 0)
		{
			return;
		}
		
		// Get the prepared relation information.
		$prepared_relation = mysql_fetch_array($get_prepared_relation_query);
		$query = base64_decode($prepared_relation["relation"]);

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

			$message = preg_replace('/\{(.*)\}/e', '$member["$1"]', $message);

			$mobile = "966" . $member["member_mobile"];

			$status = send_sms(
				array($mobile), arabic_number($message)
			);

			echo $status;
			return;
		}

	break;
	
	case "get_request_update_code":
	
		$key = mysql_real_escape_string(@$_GET["key"]);
		
		// Check if the request update does exist.
		$get_request_query = mysql_query("SELECT phpscript FROM request WHERE random_key = '$key'");
		
		if (mysql_num_rows($get_request_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		else
		{
			$fetch_request = mysql_fetch_array($get_request_query);
			
			echo "<!DOCTYPE><html><head><meta charset='utf8' /></head><body><pre>";
			echo $fetch_request["phpscript"];
			echo "</pre></body></html>";
		}
	
	break;
	
	case "upload_media":
		
		if ($user["group"] == "visitor")
		{
			$data = array(
				"status" => 0,
				"message" => "Not logged in."
			);
		}
		else
		{
			$media_title = mysql_real_escape_string(@$_POST["media_title"]);
			$media_is_event = mysql_real_escape_string(@$_GET["media_is_event"]);
			$event_id = mysql_real_escape_string(@$_GET["event_id"]);
			$media = @$_FILES["media_file"];

			// TODO: Check if the event does exists.
			$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$event_id'");
			$event_exist = mysql_num_rows($get_event_query);

			if ($media_is_event == 1 && $event_exist == 0)
			{
				$data = array(
					"status" => 0,
					"message" => "Event id not found."
				);
			}
			else
			{
				if (!empty($media) && !empty($media_title))
				{
					$error = $media["error"];
			
					if ($error == UPLOAD_ERR_OK)
					{
						$tmp_name = $media["tmp_name"];
						$name = $media["name"];
						$size = filesize($tmp_name);
						$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
						$uniqename = uniqid() . ".$extension";
				
						// Check media extension.
						if (!in_array($extension, array("jpg", "jpeg", "png", "gif")))
						{
							$data = array(
								"status" => 0,
								"message" => "Media is not an image."
							);
						}
						else
						{
							// Check media size.
							if ($size > media_max_size * 1024)
							{
								$data = array(
									"status" => 0,
									"message" => "Media size is huge."
								);
							}
							else
							{
								$hash_file = hash_file("md5", $tmp_name);
						
								// TODO: Check if the file already been uploaded.
					
								// Chcek if the file already uploaded.
								if (false)
								{
							
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
						
									// Large media fixing.
									if ($width > media_large_width)
									{
										$ratio = media_large_width/$width;
										$new_width = media_large_width;
										$new_height = $height * $ratio;
									}
									else
									{
										$new_width = $width;
										$new_height = $height;
									}
						
									$large = imagecreatetruecolor($new_width, $new_height);
									$thumb = imagecreatetruecolor(media_thumb_width, media_thumb_height);
			
									// Resample large and thumb medias.
									imagecopyresampled($large, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
									imagecopyresampled($thumb, $large, 0, 0, 0, 0, media_thumb_width, media_thumb_height, $new_width, $new_height);
						
									// Create medias.
									imagejpeg($large, "views/medias/photos/large/{$uniqename}", 100);
									imagejpeg($thumb, "views/medias/photos/thumb/{$uniqename}", 100);
						
									// Get the file size.
									$filesize = filesize("views/medias/photos/large/{$uniqename}");
						
									// Destroy some.
									imagedestroy($src);
									imagedestroy($large);
									imagedestroy($thumb);
									
									// Get member information.
									$member = get_member_id($user["member_id"]);
						
									$now = time();
									
									// Insert media into media table.
									$insert_media_query = mysql_query("INSERT INTO media (event_id, type, name, size, width, height, hash, title, author_id, created) VALUES ('$event_id', 'photo', '$uniqename', '$filesize', '$new_width', '$new_height', '$hash_file', '$media_title', '$user[member_id]', '$now')");
									$media_id = mysql_insert_id();
						
									$data = array(
										"status" => 1,
										"message" => "Done.",
										"media" => array(
											"id" => $media_id,
											"name" => $name,
											"extension" => $extension,
											"uniqename" => $uniqename,
											"size" => $filesize,
											"width" => $new_width,
											"height" => $new_height,
											"author_id" => $user["member_id"],
											"author_username" => $user["username"],
											"author_fullname" => $member["fullname"],
										)
									);
									
									$media_link = "media.php?action=view_media&id=$media_id";
									$media_desc = "تم إضافة صورة جديدة: $media_title.";
									
									// Notify all users of inserting the media.
									notify_all("media_add", $media_desc, $media_link);
								}
							}
						}
					}
					else
					{
						$data = array(
							"status" => 0,
							"message" => "Media cannot be uploaded."
						);
					}
				}
				else
				{
					$data = array(
						"status" => 0,
						"message" => "No media selected."
					);
				}
			}
		}
		
		// Print the output.
		header("Content-Type: application/json");
		echo json_encode($data);
	break;
	
	case "get_node":
	
		$tribe_id = mysql_real_escape_string(@$_GET["tribe_id"]);
		$id = mysql_real_escape_string(@$_GET["id"]);

		// Get the member information from database.
		$get_member_query = mysql_query("SELECT * FROM member WHERE tribe_id = '$tribe_id' AND id = '$id'");

		// TODO: Do some extra checking.

		if (mysql_num_rows($get_member_query) == 0)
		{
			$data = array(
				"status" => "failure"
			);
		}
		else
		{
			$member = mysql_fetch_array($get_member_query);

			// Get parent information.
			$parent = array(
				"id" => -1,
				"name" => ""
			);
			
			if ($member["father_id"] != -1)
			{
				$get_parent_query = mysql_query("SELECT * FROM member WHERE id = '$member[father_id]'");
				
				if (mysql_num_rows($get_parent_query) > 0)
				{
					$parent_fetch = mysql_fetch_array($get_parent_query);
					
					$parent = array(
						"id" => $member["father_id"],
						"name" => $parent_fetch["name"],
						"photo" => $parent_fetch["photo"],
						"nickname" => $parent_fetch["nickname"],
					);
				}
			}
			
			$related_fullname = "";

			// Get the related name.
			if ($user["group"] == "moderator")
			{
				$user_info = get_user_id($user["id"]);
				$assigned_root_info = get_member_id($user_info["assigned_root_id"]);
	
				if ($assigned_root_info)
				{
					$related_fullname = $assigned_root_info["fullname"];
				}
			}

			$conditions = array("father_id = '$id'");
	
			if ($user["group"] == "visitor")
			{
				$is_admin = $is_me = $is_accepted_moderator = $is_relative_user = false;
			}
			else
			{
				// Check if the user is admin
				$is_admin = ($user["group"] == "admin");

				// Check if the user is seeing his/her profile.
				$is_me = ($member["id"] == $user["member_id"]);

				// Check if the moderator is accepted (if any).
				$is_accepted_moderator = is_accepted_moderator($member["id"]);

				// Check if the user is relative to the member.
				$is_relative_user = is_relative_user($member["id"]);
			}
			
			// Get the privacy.
			$display_daughters = privacy_display($member["id"], "daughters", $user["group"], $is_me, $is_relative_user, $is_accepted_moderator);
	
			switch ($user["group"])
			{
				case "visitor": case "user":
				{
					if ($display_daughters == false)
					{
						$conditions []= "gender = '1'";
					}
				}
				break;

				case "moderator":
				{
					$conditions []= "(gender = '1' OR (gender = '0' AND fullname LIKE '%$related_fullname'))";
				}
				break;
			}

			$condition = implode("AND ", $conditions);

			// Get the children of the member.
			$get_children_query = mysql_query("SELECT * FROM member WHERE $condition");
			$children = array();

			if (mysql_num_rows($get_children_query) > 0)
			{
				while ($child = mysql_fetch_array($get_children_query))
				{
					// Get the number of children for this child.
					$get_child_children_query = mysql_query("SELECT id, name FROM member WHERE father_id = '$child[id]'");
				
					$children[$child["id"]] = array(
						"id" => $child["id"],
						"name" => $child["name"],
						"children_number" => mysql_num_rows($get_child_children_query),
						"photo" => $child["photo"],
						"nickname" => $child["nickname"],
					);
				}
			}

			$data = array(
		
				"parent" => $parent,
				"name" => $member["name"],
				"children" => $children,
				"status" => "success",
				"photo" => $member["photo"],
				"nickname" => $member["nickname"],
			);
		}
	
		// Print the output.
		header("Content-Type: application/json");
		echo json_encode($data);
	
	break;
	
	case "answer_ramadan_question":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$question_id = mysql_real_escape_string(@$_POST["question_id"]);
			$answer = mysql_real_escape_string(@$_POST["answer"]);
			
			// Check if the question id does exist.
			$check_question_query = mysql_query("SELECT id FROM ramadan_question WHERE id = '$question_id'");
			
			if (mysql_num_rows($check_question_query) == 0)
			{
				echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
				return;
			}
			
			// Check if the answer is correct.
			if (!in_array($answer, range(1, 4)))
			{
				echo error_message("الرجاء اختيار إجابة من قائمة الاختيارات.");
				return;
			}
			
			// Check if the user already answered this question.
			$check_answered_query = mysql_query("SELECT id FROM member_question WHERE member_id = '$user[member_id]' AND question_id = '$question_id'");
			
			if (mysql_num_rows($check_answered_query) > 0)
			{
				echo error_message("لقد قمت بالإجابة على هذا السؤال مسبقاً.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$insert_answer_query = mysql_query("INSERT INTO member_question (member_id, question_id, answer, created) VALUES ('$user[member_id]', '$question_id', '$answer', '$now')");
			
			// Awesome.
			echo success_message(
				"شكراً لك، تم حفظ إجابتك في النظام.",
				"index.php"
			);
		}
	
	break;
	
	case "rotate_image":
		
		$type = mysql_real_escape_string(@$_GET["type"]);
		
		switch ($type)
		{
			case "media":
			
				$id = mysql_real_escape_string(@$_GET["id"]);
				
				// Check if the media exists.
				$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$id'");
				
				if (mysql_num_rows($get_media_query) == 0)
				{
					echo error_message("لم يتم العثور على الصورة.");
					return;
				}	
				
				$media = mysql_fetch_array($get_media_query);
				
				$large_file = "views/medias/photos/large/$media[name]";
				$thumb_file = "views/medias/photos/thumb/$media[name]";
				
				// Get the source of the media.
				$filearray = explode(".", $media["name"]);
				$extension = $filearray[1];
				
				if ($extension == "jpg" || $extension == "jpeg")
				{
					$thumb_src = imagecreatefromjpeg($thumb_file);
					$large_src = imagecreatefromjpeg($large_file);
				}
				else if ($extension == "png")
				{
					$thumb_src = imagecreatefrompng($thumb_file);
					$large_src = imagecreatefrompng($large_file);
				}
				else
				{
					$thumb_src = imagecreatefromgif($thumb_file);
					$large_src = imagecreatefromgif($large_file);
				}
				
				$angle = 90;
				
				$thumb_rotate = imagerotate($thumb_src, $angle, 0);
				$large_rotate = imagerotate($large_src, $angle, 0);
				
				if ($extension == "jpg" || $extension == "jpeg")
				{
					imagejpeg($thumb_rotate, $thumb_file, 100);
					imagejpeg($large_rotate, $large_file, 100);
				}
				else if ($extension == "png")
				{
					imagepng($thumb_rotate, $thumb_file, 9);
					imagepng($large_rotate, $large_file, 9);
				}
				else
				{
					imagegif($thumb_rotate, $thumb_file);
					imagegif($large_rotate, $large_file);
				}
				
				imagedestroy($thumb_rotate);
				imagedestroy($large_rotate);
				imagedestroy($thumb_src);
				imagedestroy($large_src);
				
				echo success_message(
					"تم تدوير الصورة بزاوية $angle.",
					"media.php?id=$id"
				);
				return;

			break;
			
			case "avatar":
			
			break;
		}
		
	break;
	
	default:
		echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
		return;
	break;
}
