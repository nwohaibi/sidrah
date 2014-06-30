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
	"views/secretariat.html"
);

// Get the header.
$header = website_header(
	"أمانة المجلس",
	"صفحة من أجل عرض مهام و مواصفات أعضاء أمانة المجلس.",
	array(
		"عائلة", "الزغيبي", "أمانة", "المجلس"
	)
);

// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;
