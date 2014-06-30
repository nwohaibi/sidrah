<?php

require_once("inc/functions.inc.php");

// Get the user information.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Get the member information.
$member = get_member_id($user["member_id"]);
$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	default: case "view_media":
	
		// 
		$id = mysql_real_escape_string(@$_GET["id"]);

		// Get the media.
		$get_media_query = mysql_query("SELECT media.*, member.fullname AS member_fullname, member.photo AS member_photo, member.gender AS member_gender, user.username FROM media, member, user WHERE media.author_id = member.id AND member.id = user.member_id AND media.id = '$id'");

		if (mysql_num_rows($get_media_query) == 0)
		{
			echo error_message("لم يتم العثور على الصورة.");
			return;
		}

		// Get the media
		$media = mysql_fetch_array($get_media_query);

		// Update the views of the media.
		$update_media_views_query = mysql_query("UPDATE media SET views = views+1 WHERE id = '$media[id]'");

		// Get the event.
		$get_event_query = mysql_query("SELECT * FROM event WHERE id = '$media[event_id]'");

		if (mysql_num_rows($get_event_query) == 0)
		{
			$event["id"] = -1;
			$event["title"] = "";
		}
		else
		{
			$event = mysql_fetch_array($get_event_query);
		}
		
		// Get the media likes.
		$get_media_likes_query = mysql_query("SELECT COUNT(id) as media_likes FROM media_reaction WHERE media_id = '$media[id]' AND reaction = 'like'");
		$fetch_media_likes = mysql_fetch_array($get_media_likes_query);

		
		// Get the tagmembers in media.
		$get_tagmember_query = mysql_query("SELECT member.id AS member_id, member.fullname, tagmember.* FROM member, tagmember WHERE tagmember.member_id = member.id AND tagmember.type = 'media' AND tagmember.content_id = '$media[id]'");
		$tagmembers_string = "";

		if (mysql_num_rows($get_tagmember_query) > 0)
		{
			while ($tagmember = mysql_fetch_array($get_tagmember_query))
			{
				$tagmembers_string .= "tagmembers[$tagmember[member_id]] = {name: '$tagmember[fullname]'};\n";
			}
		}
		
		$can_like = media_member_can_like($media["id"], $member["id"]);
		$media_like = "";
		
		if ($can_like == true)
		{
			$media_like = "<a href='media.php?action=like_media&id=$media[id]' title='هل أعجبتك الصورة؟' id='media_like' class='small button'>أعجبتني</a>";
		}
		
		// Check if the media has an event.
		$get_media_event_query = mysql_query("SELECT id, title FROM event WHERE id = '$media[event_id]'");
		$media_event = "";
		
		if (mysql_num_rows($get_media_event_query) > 0)
		{
			$fetch_media_event = mysql_fetch_array($get_media_event_query);
			$media_event = "<a href='calendar.php?action=view_event&id=$event[id]'>$event[title]</a> ";
		}
		
		// Get the previous media.
		$get_previous_media_query = mysql_query("SELECT id, title FROM media WHERE event_id = '$media[event_id]' AND id < $media[id] ORDER BY id DESC");
		$previous_media = "";
		
		if (mysql_num_rows($get_previous_media_query) > 0)
		{
			$fetch_previous_media = mysql_fetch_array($get_previous_media_query);
			$previous_media = "<a class='small button secondary' href='media.php?id=$fetch_previous_media[id]' title='$fetch_previous_media[title]'>السابق</a>";
		}
		
		// Get the next media.
		$get_next_media_query = mysql_query("SELECT id, title FROM media WHERE event_id = '$media[event_id]' AND id > $media[id]");
		$next_media = "";
		
		if (mysql_num_rows($get_next_media_query) > 0)
		{
			$fetch_next_media = mysql_fetch_array($get_next_media_query);
			$next_media = "<a class='small button secondary' href='media.php?id=$fetch_next_media[id]' title='$fetch_next_media[title]'>التالي</a>";
		}
		
		$delete_media = "";
		$rotate_media = "";
		
		if ($user["group"] == "admin" || $user["member_id"] == $media["author_id"])
		{
			$delete_media = "<a href='media.php?action=delete_media&media_id=$media[id]' class='small button alert'>حذف</a>";
			$rotate_media = "<a href='zoghiby_ajax.php?action=rotate_image&type=media&id=$media[id]' class='small button success'>تدوير 90º</a>";
		} 
		
		// Get the creatde date for this media.
		$created = arabic_date(date("d M Y, H:i:s", $media["created"]));

		// Get the header.
		$header = website_header(
			$media["title"],
			$media["description"]
		);

		// Get the footer.
		$footer = website_footer();

		// Get the content.
		$content = template(
			"views/single_media.html",
			array(
				"media_id" => $media["id"],
				"media_name" => $media["name"],
				"media_title" => $media["title"],
				"media_description" => $media["description"],
				"media_views" => $media["views"],
				"media_likes" => $fetch_media_likes["media_likes"],
				"media_like" => $media_like,
				"author_username" => $media["username"],
				"author_id" => $media["author_id"],
				"author_fullname" => $media["member_fullname"],
				"author_photo" => rep_photo($media["member_photo"], $media["member_gender"], "avatar"),
				"author_shorten_name" => shorten_name($media["member_fullname"]),
				"media_event" => $media_event,
				"previous_media" => $previous_media,
				"next_media" => $next_media,
				"comments" => get_media_comments($media["id"], $comments_count, $member["id"]),
				"comments_count" => $comments_count,
				"tagmembers" => $tagmembers_string,
				"created" => $created,
				"delete_media" => $delete_media,
				"rotate_media" => $rotate_media
			)
		);

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;

	break;
	
	case "like_media":
	
		// Get the id.
		$id = mysql_real_escape_string(@$_GET["id"]);
		$can_like = media_member_can_like($id, $member["id"]);
		
		if ($can_like == false)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$id'");
		
		if (mysql_num_rows($get_media_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = mysql_fetch_array($get_media_query);
		$now = time();
		
		// Insert the like.
		$insert_media_like_query = mysql_query("INSERT INTO media_reaction (media_id, member_id, reaction, created) VALUES ('$media[id]', '$member[id]', 'like', '$now')");

		// Get the user of the media.
		$get_media_user_query = mysql_query("SELECT * FROM user WHERE member_id = '$media[author_id]'");

		if (mysql_num_rows($get_media_user_query) > 0)
		{
			$fetch_media_user = mysql_fetch_array($get_media_user_query);
			
			if ($user["id"] != $fetch_media_user["id"])
			{
				// Set the notification.
				$desc = "$user[username] أُعجب بالصورة: $media[title].";
				$link = "media.php?action=view_media&id=$media[id]";
		
				// Notify the author of the media.
				notify("media_like", $fetch_media_user["id"], $desc, $link);
			}
		}

		// Done.
		echo success_message(
				"تم تسجيل إعجابك بالصورة، شكراً لك.",
			"media.php?action=view_media&id=$media[id]"
		);
	
	break;
	
	case "add_comment":
	
		$media_id = mysql_real_escape_string(@$_GET["media_id"]);
		
		// Check if the media does exist.
		$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$media_id'");
		
		if (mysql_num_rows($get_media_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = mysql_fetch_array($get_media_query);
		
		// TODO: Check if the commenting on the media is available.
		
		// Post.
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{

			// Do some cleaning for the comment (XSS stuff).
			$content = trim(mysql_real_escape_string(@strip_tags($_POST["content"])));

			// Insert the comment.
			$now = time();
			$insert_media_comment_query = mysql_query("INSERT INTO media_comment (media_id, content, author_id, created) VALUES ('$media[id]', '$content', '$member[id]', '$now')");
			$inserted_comment_id = mysql_insert_id();
			
			// Get the count of media comments.
			$get_media_comments_query = mysql_query("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = '$media[id]'");
			$fetch_media_comments = mysql_fetch_array($get_media_comments_query);
			
			// Update the media thumb to be with comments count.
			draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);

			// Set a variable to hold notify/not-notify user ids.
			$notify_user_ids = array();
			$not_notify_user_ids = array();
		
			// Do not notify the author of the comment.
			//$not_notify_user_ids []= $user["id"];
		
			// Get the author id of the media.
			$get_media_author_query = mysql_query("SELECT id FROM user WHERE member_id = '$media[author_id]'");
			$fetch_media_author = mysql_fetch_array($get_media_author_query);
			$media_author_user_id = $fetch_media_author["id"];
			
			// Check if the author of the media is not the same with the author of the comment.
			if ($media_author_user_id != $user["id"])
			{
				$notify_user_ids []= $media_author_user_id;
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
			$get_users_before_query = mysql_query("SELECT DISTINCT user.id AS id FROM media_comment, user WHERE media_comment.author_id = user.member_id AND media_comment.media_id = '$media[id]' AND media_comment.created < $now $not_in_users_condition");
			$users_before_count = mysql_num_rows($get_users_before_query);
			
			if ($users_before_count > 0)
			{
				while ($users_before = mysql_fetch_array($get_users_before_query))
				{
					$notify_user_ids []= $users_before["id"];
				}
			}
			
			// Set the notification.
			$desc = "تعليق جديد على صورة: $media[title]";
			$link = "media.php?action=view_media&id=$media[id]#comment_$inserted_comment_id";
			
			// Notify related users.
			notify_many("media_comment_response", $desc, $link, $notify_user_ids);

			// Done.
			echo success_message(
				"تم إضافة التعليق بنجاح، شكراً لك.",
				"media.php?action=view_media&id=$media[id]"
			);
		}
		else
		{
			// Only post page.
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
	break;
	
	case "delete_comment":
		
		$comment_id = mysql_real_escape_string(@$_GET["comment_id"]);
		
		// Check if the comment exits.
		$get_comment_query = mysql_query("SELECT * FROM media_comment WHERE id = '$comment_id'")or die(mysql_error());
		
		if (mysql_num_rows($get_comment_query) == 0)
		{
			echo error_message("لم يتم العثور على التعليق.");
			return;
		}
		
		$comment = mysql_fetch_array($get_comment_query);
		
		// Get the media.
		$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$comment[media_id]'");
		$media = mysql_fetch_array($get_media_query);
		
		// Check if the comment author is the logged in user,
		// Or if the user is admin or moderator.
		if ($user["group"] == "admin" || $user["group"] == "moderator" || $user["member_id"] == $comment["author_id"])
		{
			// Delete related likes.
			$delete_likes_query = mysql_query("DELETE FROM media_comment_like WHERE media_comment_id = '$comment[id]'");
			
			// Then, delete the comment itsef.
			$delete_comment_query = mysql_query("DELETE FROM media_comment WHERE id = '$comment[id]'");
			
			// Count the comments, and update the media.
			$get_media_comments_query = mysql_query("SELECT COUNT(id) AS comments_count FROM media_comment WHERE media_id = '$media[id]'");
			$fetch_media_comments = mysql_fetch_array($get_media_comments_query);

			// Update the comments count written on thumb.
			draw_comments_count_thumb($media["name"], $fetch_media_comments["comments_count"]);
			
			// Done.
			echo success_message(
				"تم حذف التعليق بنجاح.",
				"media.php?action=view_media&id=$comment[media_id]"
			);
		}
		else
		{
			echo error_message("لا يمكنك حذف التعليق.");
			return;
		}
		
	break;
	
	case "like_comment":
	
		$comment_id = mysql_real_escape_string(@$_GET["comment_id"]);
		
		// Check if the comment does exist.
		$get_comment_query = mysql_query("SELECT media_comment.*, media.title AS media_title FROM media_comment, media WHERE media.id = media_comment.media_id AND media_comment.id = '$comment_id'");
		
		if (mysql_num_rows($get_comment_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the event.
		$comment = mysql_fetch_array($get_comment_query);
		
		// Check if the member has liked this comment before.
		$get_member_likes_query = mysql_query("SELECT * FROM media_comment_like WHERE media_comment_id = '$comment[id]' AND member_id = '$member[id]'");

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
		$like_comment_query = mysql_query("INSERT INTO media_comment_like (media_comment_id, member_id, created) VALUES ('$comment[id]', '$member[id]', '$now')");
		
		// Set the notification.
		$desc = "$user[username] أُعجب بتعليقك على صورة: $comment[media_title].";
		$link = "media.php?action=view_media&id=$comment[media_id]#comment_$comment[id]";
		
		// Get the user id of the comment author.
		$get_comment_user_query = mysql_query("SELECT id FROM user WHERE member_id = '$comment[author_id]'");
		$fetch_comment_user = mysql_fetch_array($get_comment_user_query);
		
		// Notify the commenter.
		notify("media_comment_like", $fetch_comment_user["id"], $desc, $link);
		
		// Done.
		echo success_message(
			"تم تسجيل إعجابك بالتعليق، شكراً لك.",
			"media.php?action=view_media&id=$comment[media_id]"
		);
	break;
	
	case "delete_media":
	
		$media_id = mysql_real_escape_string(@$_GET["media_id"]);
		
		// Check if the media does exist.
		$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$media_id'");
		
		if (mysql_num_rows($get_media_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = mysql_fetch_array($get_media_query);
		
		// Check if the user can delete the media.
		if ($user["group"] == "admin" || $user["member_id"] == $media["author_id"])
		{
			// Delete the media.
			$delete_media_query = mysql_query("DELETE FROM media WHERE id = '$media[id]'")or die(mysql_error());
			
			// Delete the reactions of media.
			$delete_media_reactions_query = mysql_query("DELETE FROM media_reaction WHERE media_id = '$media[id]'")or die(mysql_error());
			
			// Delete the likes of comments for media.
			$delete_media_comment_likes_query = mysql_query("DELETE FROM media_comment_like WHERE media_comment_id IN (SELECT id FROM media_comment WHERE media_id  = '$media[id]')")or die(mysql_error());
			
			// Delete the comments of media.
			$delete_media_comments_query = mysql_query("DELETE FROM media_comment WHERE media_id = '$media[id]'")or die(mysql_error());
			
			// Delete the tagmembers related.
			$delete_tagmembers_query = mysql_query("DELETE FROM tagmember WHERE type = 'media' AND content_id = '$media[id]'")or die(mysql_error());
			
			// Delete the files (large/thumb) also.
			unlink("views/medias/photos/large/$media[name]");
			unlink("views/medias/photos/thumb/$media[name]");
			
			// Done.
			echo success_message(
				"تم حذف الصورة بنجاح، شكراً لك.",
				"index.php"
			);
		}
		else
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
	break;
	
	case "update_tagmembers":

		$id = mysql_real_escape_string(@$_GET["id"]);
		$tagmembers = mysql_real_escape_string(@$_POST["tagmembers"]);
		
		// Check if the media does exist.
		$get_media_query = mysql_query("SELECT * FROM media WHERE id = '$id'");
		
		if (mysql_num_rows($get_media_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the media.
		$media = mysql_fetch_array($get_media_query);

		// Get the already added tagmembers.
		$get_tagmembers_query = mysql_query("SELECT * FROM tagmember WHERE type = 'media' AND content_id = '$media[id]'");
		$already_tagmembers = array();
		
		if (mysql_num_rows($get_tagmembers_query) > 0)
		{
			while ($tm = mysql_fetch_array($get_tagmembers_query))
			{
				$already_tagmembers[]= $tm["member_id"];
			}
		}

		if (empty($tagmembers) && count($already_tagmembers) == 0)
		{
			echo error_message("الرجاء إدخال اسم واحد على الأقل.");
			return;
		}
		
		$tagmembers_array = explode(",", $tagmembers);
		
		foreach ($already_tagmembers as $already_tagmember)
		{
			if (!in_array($already_tagmember, $tagmembers_array))
			{
				mysql_query("DELETE FROM tagmember WHERE type = 'media' AND content_id = '$media[id]' AND member_id = '$already_tagmember'");
			}
		}
		
		$now = time();
		
		foreach ($tagmembers_array as $tagmember)
		{
			if (!in_array($tagmember, $already_tagmembers))
			{
				mysql_query("INSERT INTO tagmember (type, content_id, member_id, created) VALUES ('media', '$media[id]', '$tagmember', '$now')");
			}
		}
		
		// Done.
		echo success_message(
			"تم إضافة الأسماء بنجاح.",
			"media.php?id=$media[id]"
		);
	break;
}
