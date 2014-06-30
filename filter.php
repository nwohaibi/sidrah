<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$member = get_member_id($user["member_id"]);
$main_tribe_id = main_tribe_id;

$action = mysql_real_escape_string(@$_GET["action"]);
$submit = mysql_real_escape_string(@$_POST["submit"]);

$parameters = array(
		"رقم العائلة (1=زغيبي)" => "tribe_id",
		"رقم العضو" => "member_id",
		"الاسم الأول" => "member_name",
		"الاسم الكامل" => "member_fullname",
		"رقم أب العضو" => "member_father_id",
		"رقم أم العضو" => "member_mother_id",
		"المسمّى الوظيفي" => "member_job_title",
		"الجنس (1=ذكر، 0=أنثى)" => "member_gender",
		"الجوّال" => "member_mobile",
		"الحالة الاجتماعية (0=بدون، 1=أعزب(ة)، 2=متزوج(ة)، 3=مطلّقة، 4=أرملة)" => "member_marital_status",
		"العمر" => "member_age",
		"تاريخ الميلاد" => "member_dob",
		"مكان الميلاد" => "member_pob",
		"تاريخ الوفاة" => "member_dod",
		"النبض (1=حيّ يرزق، 0=متوفّى)" => "member_is_alive",
		"مكان الإقامة" => "member_location",
		"التعليم (0=بدون، 1=إبتدائي، 2=متوسط، 3=ثانوي، 4=دبلوم، 5=بكالوريوس، 6=ماجستير، 7=دكتوراه)" => "member_education",
		"التخصّص" => "member_major",
		"نوع السكن (ملك، إيجار، مع العائلة، أخرى)" => "member_living",
		"الحيّ" => "member_neighborhood",
		"اسم المستخدم" => "user_username",
		"مجموعة المستخدم (user, moderator, admin)" => "user_usergroup",
		"سبق و سجّل الدخول (0=نعم، 1=لا)" => "user_first_login",
		"اسم الأب" => "father_name",
		"اسم الأم" => "mother_name",
		"جهة العمل" => "company_name",
		"عدد الزوجات" => "wives_married_count",
		"عدد المطلّقات" => "wives_divorced_count",
		"عدد الأزواج" => "husbands_married_count",
		"عدد المطلّقين" => "husbands_divorced_count",
		"عدد الأصوات للعميد" => "dean_votes",
);

$operators = array(
	"يساوي" => "=",
	"لا يساوي" => "!=",
	"أقل من" => "<",
	"أقل من أو يساوي" => "<=",
	"أكبر من" => ">",
	"أكبر من أو يساوي" => ">=",
	"يحتوي على" => "LIKE",
	"لا يحتوي على" => "NOT LIKE"
);

$logic_relations = array(
	"و" => "AND",
	"أو" => "OR"
);
switch ($action)
{
	default: case "search":
	
	if (!empty($submit))
	{
	
		// 
		$conditions = array();
	
		if (isset($_POST["parameter"]))
		{
			foreach ($_POST["parameter"] as $parameter_name => $on)
			{
				// Get the inner condition.
				if (isset($_POST["parameter_logic_relations"][$parameter_name]))
				{
					$count = count($_POST["parameter_logic_relations"][$parameter_name]);
					$this_conditions = array();
				
					for ($i=0; $i<$count; $i++)
					{
						$parameter_logic_relation = $_POST["parameter_logic_relations"][$parameter_name][$i];
						$parameter_operator = $_POST["parameter_operators"][$parameter_name][$i];
						$parameter_value = $_POST["parameter_values"][$parameter_name][$i];
					
						$this_condition = "";
					
						if ($parameter_operator == "LIKE" || $parameter_operator == "NOT LIKE")
						{
							$this_condition = "$parameter_operator '%$parameter_value%'";
						}
						else
						{
							$this_condition = "$parameter_operator '$parameter_value'";
						}
					
						if ($i == 0)
						{
							$parameter_logic_relation = "";
						}

						$temp_name = $parameter_name;
					
						$this_conditions []= "$parameter_logic_relation ($temp_name $this_condition)";
					}
				
					$this_conditions_string = implode(" ", $this_conditions);
					$conditions []= "($this_conditions_string)";
				}
			}
		}
	
		$conditions_string = "";
	
		if (count($conditions))
		{
			$conditions_string = "AND\n\t\t" . implode(" AND ", $conditions);
		}
	
		$main_query = "SELECT relation_table.* FROM
			(
				SELECT

				member.tribe_id AS tribe_id, member.id AS member_id, member.name AS member_name, member.fullname AS member_fullname,
				member.father_id AS member_father_id, member.mother_id AS member_mother_id, member.job_title AS member_job_title,
				member.gender AS member_gender, member.mobile AS member_mobile, member.marital_status AS member_marital_status,
				member.age AS member_age, member.dob AS member_dob, member.pob AS member_pob, member.dod AS member_dod,
				member.is_alive AS member_is_alive, member.location AS member_location, member.education AS member_education,
				member.major AS member_major, member.living AS member_living, member.neighborhood AS member_neighborhood,

				user.username AS user_username, user.usergroup AS user_usergroup, user.first_login AS user_first_login,

				(SELECT fullname FROM member WHERE id = member_father_id) AS father_name,
				(SELECT fullname FROM member WHERE id = member_mother_id) AS mother_name,

				(SELECT company.name FROM company WHERE company.id = member.company_id) AS company_name,

				(SELECT COUNT(married.id) FROM married WHERE married.husband_id = member_id AND marital_status = 'married') AS wives_married_count,
				(SELECT COUNT(married.id) FROM married WHERE married.husband_id = member_id AND marital_status = 'divorced') AS wives_divorced_count,
				(SELECT COUNT(married.id) FROM married WHERE married.husband_id = member_id AND marital_status = 'widow') AS wives_widow_count,
				(SELECT COUNT(married.id) FROM married WHERE married.husband_id = member_id AND marital_status = 'widower') AS wives_widower_count,

				(SELECT COUNT(married.id) FROM married WHERE married.wife_id = member_id AND marital_status = 'married') AS husbands_married_count,
				(SELECT COUNT(married.id) FROM married WHERE married.wife_id = member_id AND marital_status = 'divorced') AS husbands_divorced_count,
				(SELECT COUNT(married.id) FROM married WHERE married.wife_id = member_id AND marital_status = 'widow') AS husbands_widow_count,
				(SELECT COUNT(married.id) FROM married WHERE married.wife_id = member_id AND marital_status = 'widower') AS husbands_widower_count,
				
				(SELECT COUNT(member_dean.id) FROM member_dean WHERE member_dean.member_id = member.id) AS dean_votes

				FROM

				member, user

				WHERE

				user.member_id = member.id
			
			) relation_table
		
			WHERE
			1 = 1
			$conditions_string
			";

		$mysql_query = mysql_query($main_query);
		$results_count = mysql_num_rows($mysql_query);

		$content = "<table class='table' style='width: 2200px;'>";
		$content .= "<thead><tr>";
	
		// Fill the table head.
		foreach ($parameters as $k => $v)
		{
			$k = preg_replace('/ \(.*\)/', '', $k);
			$content .= "<th>$k</th>";
		}
	
		$content .= "</tr><tbody>";
	
		if ($results_count > 0)
		{
			while ($result = mysql_fetch_array($mysql_query))
			{
				$content .= "<tr>";
			
				foreach ($parameters as $v)
				{
					$content .= "<td>$result[$v]</td>\t";
				}
			
				$content .= "</tr>";
			}
		}
		else
		{
			echo error_message("لم يتم العثور على نتائج.");
			return;
		}

		$content .= "</tbody></table>";

		// Add shortcuts.
		$base64_encode_query = base64_encode($main_query);
		
		// Add some actions.
		$content .= '<script type="text/javascript">$(function(){ $("#export_to_excel").click(function(){$(".inputform").attr("action", "filter.php?action=export_to_excel");}); $("#save_prepared_relation").click(function(){ if($("#prepared_relation_text").val() == ""){alert("الرجاء إدخال اسم العلاقة المعدّة"); return false;} $(".inputform").attr("action", "filter.php?action=save_prepared_relation");}); });</script>';
		
		$content .= "<form class='inputform' action='filter.php' method='post'><p><ul class='autocomplete_ul'>";
		$content .= "<input type='hidden' name='query' value='$base64_encode_query' />";
		$content .= "<li class='label'><button id='export_to_excel' type='submit' value='1' class='zoghiby_btn positive'><i class='icon-list-alt icon-white'></i> تصدير إلى Excel</button></li> ";
		$content .= "<li><input id='prepared_relation_text' type='text' name='prepared_relation' placeholder='أدخل اسم العلاقة المعدّة للحفظ.' /></li> <li><button id='save_prepared_relation' type='submit' value='1' class='zoghiby_btn negative'><i class='icon-filter icon-white'></i> حفظ كعلاقة معدّة</button></li>";
		$content .= "</ul><div class='clear'></div></p></form>";
	
		// Get the header.
		$header = website_header(
			"نتائج البحث ($results_count نتيجة)",
			"صفحة من أجل عرض نتائج البحث.",
			array()
		);
	
		// Get the footer.
		$footer = website_footer();

		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	}
	else
	{
	
		$content = "<form action='filter.php' method='post' class='inputform'>";
	
		// Start looping up-on the parameters.
		foreach ($parameters as $parameter_label => $parameter_name)
		{
			// Normalize a bit.
			$parameter_name = str_replace(".", "_", $parameter_name);
	
			$content .= "<div style='padding: 8px; /*border: 1px solid #ccc;*/ width: 280px; float: right;'>";
			$content .= "<input type='checkbox' name='parameter[$parameter_name]' /> $parameter_label";
		
			$content .= "<div id='$parameter_name'>";
		
			// Add all logic relations.
			$one_parameter_condition = "<select name=\"parameter_logic_relations[$parameter_name][]\">";
			foreach ($logic_relations as $logic_relation_label => $logic_relation_value)
			{
				$one_parameter_condition .= "<option value=\"$logic_relation_value\">$logic_relation_label</option>";
			}
			$one_parameter_condition .= "</select>";
		
			// Add all operators.
			$one_parameter_condition .= "<select name=\"parameter_operators[$parameter_name][]\">";
			foreach ($operators as $operator_label => $operator_value)
			{
				$one_parameter_condition .= "<option value=\"$operator_value\">$operator_label</option>";
			}
		
			$one_parameter_condition .= "</select>";
			$one_parameter_condition .= "<input type=\"text\" name=\"parameter_values[$parameter_name][]\" /><br />";
		
			$content .= "<script type='text/javascript'>var one_{$parameter_name} = '$one_parameter_condition'; $(function(){ $('#btn_{$parameter_name}').click(function(){ $('#$parameter_name').append(one_{$parameter_name}); }); $('#btn_{$parameter_name}').click(); });</script>";
		
			$content .= "</div>";
			$content .= "<button type='button' id='btn_{$parameter_name}' class='zoghiby_btn'><i class='icon-plus'></i></button>";
			$content .= "</div>";
		}
	
		$content .= "<div class='clear'></div><p><button value='1' type='submit' name='submit' class='zoghiby_btn positive'><i class='icon-search icon-white'></i> بحث</button></p>";
		$content .= "</form>";
	
		// Get the header.
		$header = website_header(
			"بحث (متقدّم)",
			"صفحة من أجل إجراء بحث تفصيلي في سجلات عائلة الزغيبي.",
			array(
				"عائلة", "الزغيبي", "بحث", "متقدّم"
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
	
	case "export_to_excel":
		
		$query = mysql_real_escape_string(@$_POST["query"]);
		$query = base64_decode($query);
		
		// Get the results.
		$execute_query = mysql_query($query);
		
		if (!$execute_query || mysql_num_rows($execute_query) == 0)
		{
			echo error_message("لم يتم العثور على نتائج.");
			return;
		}
		
		require_once("classes/PHPExcel.php");
		
		$objPHPExcel = new PHPExcel();
		
		// Set some properties.
		$objPHPExcel->getProperties()->setCreator(shorten_name($member["fullname"]));
		$objPHPExcel->getProperties()->setLastModifiedBy(shorten_name($member["fullname"]));
		$objPHPExcel->getProperties()->setTitle("نتائج البحث من موقع عائلة الزغيبي");
		$objPHPExcel->getProperties()->setSubject("نتائج البحث من موقع عائلة الزغيبي");
		$objPHPExcel->getProperties()->setDescription("تصدير نتائج البحث من موقع عائلة الزغيبي.");
		$objPHPExcel->getProperties()->setKeywords("عائلة الزغيبي بحث");
		$objPHPExcel->getProperties()->setCategory("عائلة الزغيبي");
		
		// Add all columns.
		$i = 0; $prefix = "";
		
		foreach ($parameters as $label => $v)
		{
			if ($i == 26)
			{
				$prefix .= "A";
				$i = 0;
			}

			$chr = chr(65 + $i);
			$column = "{$prefix}{$chr}1";

			$label = preg_replace('/ \(.*\)/', '', $label);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($column, $label);
			
			$i++;
		}
		
		// Add all results.
		$j = 2;
		
		while ($result = mysql_fetch_array($execute_query))
		{
			$i = 0; $prefix = "";
		
			foreach ($parameters as $v)
			{
				if ($i == 26)
				{
					$prefix .= "A";
					$i = 0;
				}

				$chr = chr(65 + $i);
				$column = "{$prefix}{$chr}{$j}";

				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($column, $result[$v]);

				$i++;
			}
			
			$j++;
		}
		
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle("نتائج البحث");
		
		// Set the first sheet to be active.
		$objPHPExcel->setActiveSheetIndex(0);
		$now = time();
		
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $now . '.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
		$objWriter->save("php://output");
		exit();

	break;
	
	case "save_prepared_relation":
		
		$prepared_relation = trim(mysql_real_escape_string(@$_POST["prepared_relation"]));
		$query = mysql_real_escape_string(@$_POST["query"]);
		
		// Check if the name is empty.
		if (empty($prepared_relation))
		{
			echo error_message("الرجاء إدخال اسم العلاقة المعدّة.");
			return;
		}
		
		// Check if the name already exists.
		$get_prepared_relation_query = mysql_query("SELECT * FROM prepared_relation WHERE name = '$prepared_relation'");
		
		if (mysql_num_rows($get_prepared_relation_query) > 0)
		{
			echo error_message("اسم العلاقة المعدّة موجود مسبقاً، الرجاء إدخال اسم آخر.");
			return;
		}
		
		// Everything else is good.
		// Insert the prepared relation.
		$now = time();
		$insert_prepared_relation_query = mysql_query("INSERT INTO prepared_relation (name, relation, created) VALUES ('$prepared_relation', '$query', '$now')");
		
		// Done.
		echo success_message(
			"تمت إضافة العلاقة المعدّة بنجاح.",
			"filter.php"
		);
	break;
}


