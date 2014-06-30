<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();
$do = mysql_real_escape_string(@$_GET["do"]);
$id = 1;//mysql_real_escape_string(@$_GET["id"]);

// Check if the user logged in.
if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Check if the survey exists.
$get_survey_query = mysql_query("SELECT * FROM survey WHERE id = '$id'");

if (mysql_num_rows($get_survey_query) == 0)
{
	echo error_message("Error.");
	return;
}

// Get the survey.
$survey = mysql_fetch_array($get_survey_query);

switch ($do)
{	
	default: case "survey":

		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		if (!empty($submit))
		{
			
		}
		else
		{
			// Get the questions.
			$get_survey_questions_query = mysql_query("SELECT * FROM survey_question WHERE survey_id = '$survey[id]'");

			if (mysql_num_rows($get_survey_questions_query) == 0)
			{
				echo error_message("Error.");
				return;
			}
		
			$questions = array();
		
			while ($question = mysql_fetch_array($get_survey_questions_query))
			{
				$questions[$question["id"]] = array(
					"description" => $question["description"],
					"answers" => array()
				);
			
				// Get the answers.
				$get_survey_answers_query = mysql_query("SELECT * FROM survey_answer WHERE question_id = '$question[id]'");
			
				if (mysql_num_rows($get_survey_answers_query) == 0)
				{
					echo error_message("Error.");
					return;
				}
			
				while ($answer = mysql_fetch_array($get_survey_answers_query))
				{			
					$questions[$question["id"]]["answers"][$answer["id"]] = array(
						"type" => $answer["type"],
						"description" => $answer["description"]
					);
				}
			}

			$survey_html = "";

			foreach ($questions as $question_id => $question)
			{
				$survey_html .= "<div class='survey_question'>";
				$survey_html .= "<h2>$question_id. $question[description]</h2>";
			
				$i = 0;
			
				foreach ($question["answers"] as $answer_id => $answer)
				{
					if ($i == 0)
					{
						$checked = "checked";
					}
					else
					{
						$checked = "";
					}

					if (count($question["answers"]) == 1)
					{
						$survey_html .= "<input type='hidden' name='answer[$question_id]' value='$answer_id' />";
					}
					else
					{
						$survey_html .= "<input type='radio' name='answer[$question_id]' value='$answer_id' $checked />";
					}
			
					$survey_html .= " $answer[description]";
				
					switch ($answer["type"])
					{
						case "option": break;
					
						case "text":
							$survey_html .= " <input type='text' name='value[$answer_id]' />";
						break;
					
						case "textarea":
							$survey_html .= " <textarea type='text' name='value[$answer_id]'></textarea>";
						break;
					}
				
					$survey_html .= "<br />\n";
					$i++;
				}
				
				$survey_html .= "</div>\n";
			}

			// Get the content.
			$content = template(
				"views/survey.html",
				array(
					"survey_name" => $survey["name"],
					"survey_description" => $survey["description"],
					"survey" => $survey_html
				)
			);
		
			// Get the header.
			$header = website_header(
					"استبيان $survey[name]",
					$survey["description"],
					array("عائلة", "الزغيبي", "عرض", "الاستبانة")
			);
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;

		}
	break;
}
