<?php

// Manage Ramadan Questions.

require_once("inc/functions.inc.php");

$user = user_information();

if ($user["group"] != "admin" && $user["member_id"] != 348)
{
	redirect_to_login();
	return;
}

$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	case "add_question":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$day = mysql_real_escape_string(trim(arabic_number(@$_POST["day"])));
			$question = mysql_real_escape_string(@$_POST["question"]);
			$answer1 = mysql_real_escape_string(@$_POST["answer1"]);
			$answer2 = mysql_real_escape_string(@$_POST["answer2"]);
			$answer3 = mysql_real_escape_string(@$_POST["answer3"]);
			$answer4 = mysql_real_escape_string(@$_POST["answer4"]);
			$correct_answer = mysql_real_escape_string(@$_POST["correct_answer"]);
			$positive_message = mysql_real_escape_string(@$_POST["positive_message"]);
		
			// Check if there are missing fields.
			if (empty($day) || empty($question) || empty($answer1) || empty($correct_answer) )
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$insert_ramadan_question_query = mysql_query("INSERT INTO ramadan_question (question, answer1, answer2, answer3, answer4, correct_answer, day, positive_message, created) VALUES ('$question', '$answer1', '$answer2', '$answer3', '$answer4', '$correct_answer', '$day', '$positive_message', '$now')");
			
			// Awesome.
			echo success_message(
				"تم إضافة السؤال الرمضاني بنجاح.",
				"manage_ramadan_questions.php"
			);
		}

	break;
	
	case "edit_question":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if the ramadan question does exist.
		$get_ramadan_question_query = mysql_query("SELECT * FROM ramadan_question WHERE id = '$id'");
		
		if (mysql_num_rows($get_ramadan_question_query) == 0)
		{
			echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Get the job information.
		$ramadan_question = mysql_fetch_array($get_ramadan_question_query);
		
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			$day = mysql_real_escape_string(trim(arabic_number(@$_POST["day"])));
			$question = mysql_real_escape_string(@$_POST["question"]);
			$answer1 = mysql_real_escape_string(@$_POST["answer1"]);
			$answer2 = mysql_real_escape_string(@$_POST["answer2"]);
			$answer3 = mysql_real_escape_string(@$_POST["answer3"]);
			$answer4 = mysql_real_escape_string(@$_POST["answer4"]);
			$correct_answer = mysql_real_escape_string(@$_POST["correct_answer"]);
			$positive_message = mysql_real_escape_string(@$_POST["positive_message"]);
		
			// Check if there are missing fields.
			if (empty($day) || empty($question) || empty($answer1) || empty($correct_answer) )
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Everything is alright.
			$now = time();
			$update_ramadan_question_query = mysql_query("UPDATE ramadan_question SET day = '$day', question = '$question', answer1 = '$answer1', answer2 = '$answer2', answer3 = '$answer3', answer4 = '$answer4', correct_answer = '$correct_answer', positive_message = '$positive_message', created = '$now' WHERE id = '$ramadan_question[id]'");
			
			// Awesome.
			echo success_message(
				"تم تعديل السؤال الرمضاني بنجاح.",
				"manage_ramadan_questions.php"
			);
		}
		else
		{
			$checked_answer1 = ($ramadan_question["correct_answer"] == 1) ? "checked" : "";
			$checked_answer2 = ($ramadan_question["correct_answer"] == 2) ? "checked" : "";
			$checked_answer3 = ($ramadan_question["correct_answer"] == 3) ? "checked" : "";
			$checked_answer4 = ($ramadan_question["correct_answer"] == 4) ? "checked" : "";
		
			// Get the content.
			$content = template(
				"views/edit_ramadan_question.html",
				array(
					"id" => $ramadan_question["id"],
					"day" =>  $ramadan_question["day"],
					"question" => $ramadan_question["question"],
					"answer1" => $ramadan_question["answer1"],
					"answer2" => $ramadan_question["answer2"],
					"answer3" => $ramadan_question["answer3"],
					"answer4" => $ramadan_question["answer4"],
					"positive_message" => $ramadan_question["positive_message"],
					"checked_answer1" => $checked_answer1,
					"checked_answer2" => $checked_answer2,
					"checked_answer3" => $checked_answer3,
					"checked_answer4" => $checked_answer4,
				)
			);
			
			// Get the header.
			$header = website_header(
				"تعديل سؤال رمضاني - $ramadan_question[question]",
				"صفحة من أجل تعديل سؤال رمضاني $ramadan_question[question]",
				array(
					"عائلة", "الزغيبي", "سؤال", "رمضاني"
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
	
	case "random_winner":
	
		$get_random_winner_query = mysql_query("SELECT COUNT(ramadan.id) AS correct_answers, ramadan.member_id AS member_id FROM (SELECT mq.id AS id, mq.member_id AS member_id FROM ramadan_question rq, member_question mq WHERE rq.id = mq.question_id AND rq.correct_answer = mq.answer) ramadan GROUP BY ramadan.member_id ORDER BY correct_answers DESC, RAND() LIMIT 5");
		$content = "";
		
		if (mysql_num_rows($get_random_winner_query) > 0)
		{
		
			while ($fetch_winner = mysql_fetch_array($get_random_winner_query))
			{
				$winner_member = get_member_id($fetch_winner["member_id"]);
				
				// Set the content.
				$content .= "<center><h4 class='subheader'><a href='familytree.php?id=$winner_member[id]'>$winner_member[fullname]</a></h4></center>";
			}
		
			// Get the header.
			$header = website_header(
					"تحديد فائز عشوائي",
					"صفحة من أجل تحديد فائز عشوائي",
					array(
						"تحديد", "فائز", "الزغيبي", "عائلة"
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
	
	case "update_questions":
	
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
				// Delete all related rows.
				$delete_member_question_query = mysql_query("DELETE FROM member_question WHERE question_id = '$k'");
			
				// Delete selected ramadan question.
				$delete_ramadan_question_query = mysql_query("DELETE FROM ramadan_question WHERE id = '$k'");
			}
		}
		else
		{
			echo error_message("الرجاء اختيار خيار واحد على الأقل.");
			return;
		}
		
		// Done.
		echo success_message(
			"تم حذف الأسئلة الرمضانيّة المحدّدة بنجاح.",
				"manage_ramadan_questions.php"
		);
		
		return;
	break;
	
	default: case "view_questions":
		
		$get_ramadan_questions_query = mysql_query("SELECT ramadan_question.*, (SELECT COUNT(member_question.id) FROM member_question WHERE member_question.question_id = ramadan_question.id) AS answers, (SELECT COUNT(member_question.id) FROM member_question WHERE member_question.question_id = ramadan_question.id AND member_question.answer = ramadan_question.correct_answer) AS correct_answers FROM ramadan_question ORDER BY id ASC");
		$ramadan_questions_html = "";
		
		if (mysql_num_rows($get_ramadan_questions_query) == 0)
		{
			$ramadan_questions_html = "<tr><td colspan='4'>لا يوجد أسئلة رمضانيّة بعد.</td></tr>";
		}
		else
		{
			// Walk up-on these ramadan questions.
			while ($ramadan_question = mysql_fetch_array($get_ramadan_questions_query))
			{
				$correct_answer = "answer" . $ramadan_question["correct_answer"];
				$ramadan_questions_html .= "<tr><td><input type='checkbox' name='check[$ramadan_question[id]]' /></td><td>$ramadan_question[question]<br /><b>($ramadan_question[$correct_answer])</b></td><td>$ramadan_question[answers]</td><td>$ramadan_question[correct_answers]</td><td><a href='manage_ramadan_questions.php?action=edit_question&id=$ramadan_question[id]' class='small button'>تعديل</a></td></tr>";
			}
		}
	
		// Get the content.
		$content = template(
			"views/manage_ramadan_questions.html",
			array(
				"ramadan_questions" => $ramadan_questions_html,
			)
		);
		
		// Get the header.
		$header = website_header(
				"عرض أسئلة رمضان",
				"صفحة من أجل عرض أسئلة رمضان",
				array(
					"أسئلة", "رمضان", "الزغيبي", "عائلة"
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
