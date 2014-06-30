<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] != "admin")
{
	redirect_to_login();
	return;
}

// Get the header.
$header = website_header(
	"شجر العوائل",
	"صفحة من أجل عرض شجر العوائل جميعاً.",
	array(
		"عائلة", "الزغيبي", "شجر", "العوائل"
	)
);

// Set the content.
$get_all_tribes_query = mysql_query("SELECT tribe_id, tribe_name, members_count FROM (SELECT tribe.id as tribe_id, tribe.name as tribe_name, COUNT(member.tribe_id) as members_count FROM member, tribe WHERE member.tribe_id = tribe.id GROUP BY member.tribe_id) as table1 ORDER BY members_count DESC");
$tribes_count = mysql_num_rows($get_all_tribes_query);
$tr = 0;

if ($tribes_count > 0)
{
	$table = "";

	while ($tribe = mysql_fetch_array($get_all_tribes_query))
	{
		$table .= "<td><a href='familytree.php?tribe_id=$tribe[tribe_id]' target='_blank'>$tribe[tribe_name]</a> ($tribe[members_count])</td>";
		$tr++;
		
		if ($tr % 4 == 0)
		{
			$table .= "</tr><tr>";
		}
	}
}

$content = "<div class='row'><div class='large-12 columns'><table class='large-12 columns'><thead><tr><th>العائلة (العدد)</th><th>العائلة (العدد)</th><th>العائلة (العدد)</th><th>العائلة (العدد)</th></tr></thead><tbody><tr>$table</tr></tbody></table></div></div>";

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;
