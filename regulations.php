<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] == "visitor")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

// Get the content.
$content = template(
	"views/regulations.html"
);

// Get the header.
$header = website_header(
	"اللائحة التنظيميّة",
	"صفحة من أجل استعراض اللائحة التنظيمية لعائلة الزغيبي.",
	array(
		"عائلة", "الزغيبي", "اللائحة", "التنظيمية"
	)
);

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;
