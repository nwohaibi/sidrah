<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	redirect_to_login();
	return;
}

$stats = array();

// Get main tribe.
$main_tribe_id = main_tribe_id;

// Get alive members count.
$stats["alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get the last updates.
$limit = 15;
$days = 15;
$todaytime = mktime(0, 0, 0, date("m"), date("d"), date("Y")) - ($days * 24 * 60 * 60);

$stats["today_added_members"] = get_num_stats("SELECT count(id) AS today_added_members FROM member WHERE created >= $todaytime AND tribe_id = '$main_tribe_id'", "today_added_members");

if ($stats["today_added_members"] > 0)
{
	$today_added_members = "(<a title='أعضاء جدد خلال $days أيام'>+$stats[today_added_members]</a>)";
}
else
{
	$today_added_members = "";
}

// Get male alive members count.
$stats["male_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '1' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get female alive members count.
$stats["female_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '0' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get married male alive members count.
$stats["married_male_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '1' AND marital_status = '2' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get single male alive members count.
$stats["single_male_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '1' AND marital_status = '1' AND age >= '18' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get single female alive members count.
$stats["single_female_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '0' AND marital_status = '1' AND age >= '18' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get married female alive members count.
$stats["married_female_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '0' AND marital_status = '2' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get divorced female alive members count.
$stats["divorced_female_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '0' AND marital_status = '3' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get widow female alive members count.
$stats["widow_female_alive_members"] = get_num_stats("SELECT count(id) AS alive_members FROM member WHERE is_alive = '1' AND gender = '0' AND marital_status = '4' AND tribe_id = '$main_tribe_id'", "alive_members");

// Get the average of male for family members.
$stats["male_average"] = @rep_percent($stats["alive_members"], $stats["male_alive_members"]);

// Get the average of female for family members.
$stats["female_average"] = @rep_percent($stats["alive_members"], $stats["female_alive_members"]);

// Get the average of married males.
$stats["married_male_average"] = @rep_percent($stats["male_alive_members"], $stats["married_male_alive_members"]);

// Get the average of single males.
$stats["single_male_average"] = @rep_percent($stats["male_alive_members"], $stats["single_male_alive_members"]);

// Get the average of married females.
$stats["married_female_average"] = @rep_percent($stats["female_alive_members"], $stats["married_female_alive_members"]);

// Get the average of single females.
$stats["single_female_average"] = @rep_percent($stats["female_alive_members"], $stats["single_female_alive_members"]);

// Get the average of divorced females.
$stats["divorced_female_average"] = @rep_percent($stats["female_alive_members"], $stats["divorced_female_alive_members"]);

// Get the average of widow females.
$stats["widow_female_average"] = @rep_percent($stats["female_alive_members"], $stats["widow_female_alive_members"]);

// Get the ranges of member ages.
$stats["member_ages_ranges"] = get_range_stats("member", array(
"WHEN age >= 0 AND age <= 5 THEN '0-5'",
"WHEN age >= 6 AND age <= 10 THEN '6-10'",
"WHEN age >= 11 AND age <= 15 THEN '11-15'",
"WHEN age >= 16 AND age <= 20 THEN '16-20'",
"WHEN age >= 21 AND age <= 25 THEN '21-25'",
"WHEN age >= 26 AND age <= 30 THEN '26-30'",
"WHEN age >= 31 AND age <= 35 THEN '31-35'",
"WHEN age >= 36 AND age <= 40 THEN '36-40'",
"WHEN age >= 41 AND age <= 45 THEN '41-45'",
"WHEN age >= 46 AND age <= 50 THEN '46-50'",
"WHEN age >= 51 AND age <= 55 THEN '51-55'",
"WHEN age >= 56 AND age <= 60 THEN '56-60'",
"WHEN age >= 61 AND age <= 65 THEN '61-65'",
"WHEN age >= 66 AND age <= 70 THEN '66-70'",
"WHEN age >= 71 AND age <= 75 THEN '71-75'",
"WHEN age >= 76 AND age <= 80 THEN '76-80'",
"WHEN age >= 81 AND age <= 85 THEN '81-85'",
"WHEN age >= 86 AND age <= 90 THEN '86-90'",
"WHEN age >= 91 AND age <= 95 THEN '91-95'",
"WHEN age >= 96 AND age <= 100 THEN '96-100'",
"WHEN age >= 101 AND age <= 105 THEN '101-105'",
"WHEN age >= 106 AND age <= 110 THEN '106-110'",
"WHEN age >= 111 AND age <= 115 THEN '111-115'",
"WHEN age >= 116 AND age <= 120 THEN '116-120'",
"ELSE '>120'"
), "is_alive = '1' AND tribe_id = '$main_tribe_id'", "age ASC");

// Get the ranges of member educations.
$stats["member_educations_ranges"] = get_range_stats("member", array(
"WHEN education = 1 THEN 'ابتدائي'",
"WHEN education = 2 THEN 'متوسط'",
"WHEN education = 3 THEN 'ثانوي'",
"WHEN education = 4 THEN 'دبلوم'",
"WHEN education = 5 THEN 'بكالوريوس'",
"WHEN education = 6 THEN 'ماجستير'",
"WHEN education = 7 THEN 'دكتوراه'",
"ELSE 'غير محدّد'"
), "is_alive = '1' AND tribe_id = '$main_tribe_id'", "education ASC");

// Get members majors.
$stats["members_majors"] = get_rows_stats("SELECT COUNT(id) AS repeated, major FROM member WHERE major != '' AND tribe_id = '$main_tribe_id' GROUP BY major ORDER BY repeated DESC");

// Get members job titles.
$stats["members_job_titles"] = get_rows_stats("SELECT COUNT(id) AS repeated, job_title FROM member WHERE job_title != '' AND tribe_id = '$main_tribe_id' GROUP BY job_title ORDER BY repeated DESC");

// Get members companies.
$stats["members_companies"] = get_rows_stats("SELECT COUNT(member.id) AS repeated, company.name as company_name FROM member, company WHERE member.company_id = company.id AND member.tribe_id = '$main_tribe_id' GROUP BY company_name ORDER BY repeated DESC");

// Get top male member names.
$stats["top_male_member_names"] = get_rows_stats("SELECT COUNT(id) AS repeated, name FROM member WHERE gender = '1' AND tribe_id = '$main_tribe_id' GROUP BY name ORDER BY repeated DESC LIMIT $limit");

// Get top female member names.
$stats["top_female_member_names"] = get_rows_stats("SELECT COUNT(id) AS repeated, name FROM member WHERE gender = '0' AND tribe_id = '$main_tribe_id' GROUP BY name ORDER BY repeated DESC");

// Get top member locations.
$stats["top_member_locations"] = get_rows_stats("SELECT COUNT(id) AS repeated, location FROM member WHERE is_alive = '1' AND location != '' AND tribe_id = '$main_tribe_id' GROUP BY location ORDER BY repeated DESC");

// Get top tribes we married to.
$stats["top_tribes_we_married"] = get_rows_stats("SELECT count(married.wife_id) as repeated, tribe.name as tribe_name FROM married, member, tribe WHERE married.wife_id = member.id AND member.tribe_id = tribe.id GROUP BY tribe_name ORDER BY repeated DESC LIMIT $limit");

// Get top tribes they married to us.
$stats["top_tribes_they_married"] = get_rows_stats("SELECT count(married.husband_id) as repeated, tribe.name as tribe_name FROM married, member, tribe WHERE married.husband_id = member.id AND member.tribe_id = tribe.id GROUP BY tribe_name ORDER BY repeated DESC LIMIT $limit");

// Get top hobbies.
$stats["top_hobbies"] = get_rows_stats("SELECT COUNT(hobby.id) AS repeated, (hobby.name) AS hobby_name FROM hobby, member_hobby WHERE member_hobby.hobby_id = hobby.id GROUP BY hobby.name ORDER BY repeated DESC LIMIT $limit");

// Get members livings.
$stats["members_livings"] = get_rows_stats("SELECT COUNT(id) AS repeated, living FROM member WHERE living != '' AND tribe_id = '$main_tribe_id' GROUP BY living ORDER BY repeated DESC");

// Get members bloods.
$stats["members_bloods"] = get_rows_stats("SELECT COUNT(id) AS repeated, blood_type FROM member WHERE blood_type != '' AND tribe_id = '$main_tribe_id' GROUP BY blood_type ORDER BY repeated DESC");

// Get member ages ranges.
$member_ages_ranges = "";
foreach ($stats["member_ages_ranges"] as $range)
{
	$member_ages_ranges .= "<tr><td>$range[range]</td><td><span class='number'>$range[count]</span></td></tr>\n";
}

// Get member educations ranges
$member_educations_ranges = "";
foreach ($stats["member_educations_ranges"] as $range)
{
	$member_educations_ranges .= "<tr><td>$range[range]</td><td><span class='number'>$range[count]</span></td></tr>\n";
}

// Get member members majors
$members_majors = "";
foreach ($stats["members_majors"] as $range)
{
	$members_majors .= "<tr><td>$range[major]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get members companies
$members_companies = "";
foreach ($stats["members_companies"] as $range)
{
	$members_companies .= "<tr><td>$range[company_name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get members job titles
$members_job_titles = "";
foreach ($stats["members_job_titles"] as $range)
{
	$members_job_titles .= "<tr><td>$range[job_title]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top male member names
$top_male_member_names = "";
foreach ($stats["top_male_member_names"] as $range)
{
	$top_male_member_names .= "<tr><td>$range[name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top female member names
$top_female_member_names = "";
foreach ($stats["top_female_member_names"] as $range)
{
	$top_female_member_names .= "<tr><td>$range[name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top member locations
$top_member_locations = "";
foreach ($stats["top_member_locations"] as $range)
{
	$top_member_locations .= "<tr><td>$range[location]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top tribes we married
$top_tribes_we_married = "";
foreach ($stats["top_tribes_we_married"] as $range)
{
	$top_tribes_we_married .= "<tr><td>ال$range[tribe_name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top tribes they married
$top_tribes_they_married = "";
foreach ($stats["top_tribes_they_married"] as $range)
{
	$top_tribes_they_married .= "<tr><td>ال$range[tribe_name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get members livings
$members_livings = "";
foreach ($stats["members_livings"] as $range)
{
	$members_livings .= "<tr><td>$range[living]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get members bloods
$members_bloods = "";
foreach ($stats["members_bloods"] as $range)
{
	$members_bloods .= "<tr><td>$range[blood_type]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get top hobbies
$top_hobbies = "";
foreach ($stats["top_hobbies"] as $range)
{
	$top_hobbies .= "<tr><td>$range[hobby_name]</td><td><span class='number'>$range[repeated]</span></td></tr>\n";
}

// Get the header.
$header = website_header(
	"إحصاءات",
	"صفحة من أجل عرض بعض الإحصاءات ذات العلاقة بعائلة الزغيبي.",
	array(
		"عائلة", "الزغيبي", "شجرة", "إحصاءات"
	)
);

// Get the content.
$content = template(
	"views/stats.html",
	array(
		"today_added_members" => $today_added_members,
		"alive_members" => $stats["alive_members"],
		"male_alive_members" => $stats["male_alive_members"],
		"female_alive_members" => $stats["female_alive_members"],
		"married_male_alive_members" => $stats["married_male_alive_members"],
		"single_male_alive_members" => $stats["single_male_alive_members"],
		"single_female_alive_members" => $stats["single_female_alive_members"],
		"married_female_alive_members" => $stats["married_female_alive_members"],
		"divorced_female_alive_members" => $stats["divorced_female_alive_members"],
		"widow_female_alive_members" => $stats["widow_female_alive_members"],
		"male_average" => $stats["male_average"],
		"female_average" => $stats["female_average"],
		"married_male_average" => $stats["married_male_average"],
		"single_male_average" => $stats["single_male_average"],
		"married_female_average" => $stats["married_female_average"],
		"single_female_average" => $stats["single_female_average"],
		"divorced_female_average" => $stats["divorced_female_average"],
		"widow_female_average" => $stats["widow_female_average"],
		"member_ages_ranges" => $member_ages_ranges,
		"member_educations_ranges" => $member_educations_ranges,
		"members_majors" => $members_majors,
		"members_companies" => $members_companies,
		"members_job_titles" => $members_job_titles,
		"top_male_member_names" => $top_male_member_names,
		"top_female_member_names" => $top_female_member_names,
		"top_member_locations" => $top_member_locations,
		"top_tribes_we_married" => $top_tribes_we_married,
		"top_tribes_they_married" => $top_tribes_they_married,
		"members_livings" => $members_livings,
		"members_bloods" => $members_bloods,
		"top_hobbies" => $top_hobbies
	)
);

// Get the footer.
$footer = website_footer();

// Print the page
echo $header;
echo $content;
echo $footer;

?>
