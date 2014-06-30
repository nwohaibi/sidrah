<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Get the members of the council.
$member_ids = array(
	array("230", "head"),
	array("450", "member"),
);

// Start walking...
$members_html = "";

foreach ($member_ids as $member_id)
{
	// Get the member information.
	$member = get_member_id($member_id[0]);
	$title = ($member_id[1] == "head") ? "رئيس" : "عضو";
	
	$members_html .= "<tr><td><a href='familytree.php?id=$member[id]' target='_blank'>$member[fullname]</a></td><td>($title)</td></tr>";
}

// Get the content.
$content = template(
	"views/family_council.html",
	array(
		"members" => $members_html
	)
);

// Get the header.
$header = website_header(
	"مجلس العائلة",
	"صفحة من أجل عرض مجلس العائلة.",
	array(
		"عائلة", "الزغيبي", "مجلس", "العائلة"
	)
);

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;
