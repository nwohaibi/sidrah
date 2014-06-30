<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

// Get the main tribe id.
$main_tribe_id = main_tribe_id;

// Get the locations of the members.
$get_locations_query = mysql_query("SELECT * FROM (SELECT location, COUNT(id) AS members_count FROM member WHERE (privacy_mobile != 'related_circle' AND is_alive = 1 AND gender = 1 AND mobile LIKE '5%' AND tribe_id = '$main_tribe_id') GROUP BY location) AS locations WHERE locations.members_count > 0 ORDER BY locations.members_count DESC");

if (mysql_num_rows($get_locations_query) == 0)
{
	echo error_message("لم يتم العثور على أرقام هاتف لعرضها.");
	return;
}

// Set the locations inner text.
$mobile_book_html = "";
$i = 1;

while ($location = mysql_fetch_array($get_locations_query))
{
	$string_location = empty($location["location"]) ? "(غير محدّد)" : $location["location"];
	$mobile_book_html .= "<h3>$string_location <span>($location[members_count])</span></h3>\n";
	$mobile_book_html .= "<table class='table'><thead><tr><th>الاسم</th><th class='hide-for-medium-down'>جهة العمل</th><th>الجوّال</th></tr></thead><tbody>";
	
	// Get all members in the current location.
	$get_current_members_query = mysql_query("SELECT id, fullname, mobile, company_id, job_title, privacy_company, privacy_job_title FROM member WHERE location = '$location[location]' AND privacy_mobile != 'related_circle' AND is_alive = 1 AND gender = 1 AND mobile LIKE '5%' AND tribe_id = '$main_tribe_id' ORDER BY fullname");
	
	while ($one_member = mysql_fetch_array($get_current_members_query))
	{
		// Get the company of the current member.
		$company = ($one_member["privacy_company"] != "related_circle") ? rep_company($one_member["company_id"]) : "";
		$job_title = ($one_member["privacy_job_title"] != "related_circle" && !empty($one_member["job_title"])) ? "($one_member[job_title])" : "";
		
		$mobile_book_html .= "<tr><td>$i. <a href='familytree.php?id=$one_member[id]'>$one_member[fullname]</a></td><td class='hide-for-medium-down'>$company $job_title</td><td>$one_member[mobile]</td></tr>";
		$i++;
	}
	
	$mobile_book_html .= "</tbody></table>";
}

// Get the content.
$content = template(
	"views/mobile_book.html",
	array(
		"mobile_book" => $mobile_book_html
	)
);

// Get the header.
$header = website_header(
	"دليل الجوّال",
	"صفحة من أجل استعراض دليل الجوّال لأفراد عائلة الزغيبي.",
	array(
		"عائلة", "الزغيبي", "دليل", "الجوّال"
	)
);

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;


