<?php

require_once("inc/functions.inc.php");

// Get the information of the user.
$user = user_information();

if ($user["group"] != "admin")
{
	redirect_to_login();
	return;	
}

// Get all feedbacks that are bugs.
$get_bugs_query = mysql_query("SELECT * FROM feedback WHERE type = 'bug'");
$bugs_count = mysql_num_rows($get_bugs_query);
$bugs_html = "";

// Walk up-on the bugs.
if ($bugs_count == 0){
	$bugs_html = "<tr><td>لا يوجد أخطاء بعد.</td></tr>";
}
else
{
	while ($bug = mysql_fetch_array($get_bugs_query))
	{
		$created = arabic_date(date("d M Y, H:i:s", $bug["created"]));
		$bugs_html .= "<tr><td>$bug[content]<p><ul class='button-group left'><li><a href='#' class='secondary small button'>$created</a></li><li><a href='#' class='small button' title='$bug[user_agent]'>UA</a></li></ul></p></td></tr>";
	}
}

// Get all feedbacks that are ideas.
$get_ideas_query = mysql_query("SELECT * FROM feedback WHERE type = 'idea'");
$ideas_count = mysql_num_rows($get_ideas_query);
$ideas_html = "";

// Walk up-on the ideas.
if ($ideas_count == 0){
	$ideas_html = "<tr><td>لا يوجد أفكار بعد.</td></tr>";
}
else
{
	while ($idea = mysql_fetch_array($get_ideas_query))
	{
		$created = arabic_date(date("d M Y, H:i:s", $idea["created"]));
		$ideas_html .= "<tr><td>$idea[content]<p><ul class='button-group left'><li><a href='#' class='secondary small button'>$created</a></li><li><a href='#' class='small button' title='$idea[user_agent]'>UA</a></li></ul></p></td></tr>";
	}
}

// Get all feedbacks that are praises.
$get_praises_query = mysql_query("SELECT * FROM feedback WHERE type = 'praise'");
$praises_count = mysql_num_rows($get_praises_query);
$praises_html = "";

// Walk up-on the praises.
if ($praises_count == 0){
	$praises_html = "<tr><td>لا يوجد شكر بعد.</td></tr>";
}
else
{
	while ($praise = mysql_fetch_array($get_praises_query))
	{
		$created = arabic_date(date("d M Y, H:i:s", $praise["created"]));
		$praises_html .= "<tr><td>$praise[content]<p><ul class='button-group left'><li><a href='#' class='secondary small button'>$created</a></li><li><a href='#' class='small button' title='$praise[user_agent]'>UA</a></li></ul></p></td></tr>";
	}
}

$content = template(
	"views/feedbacks_reader.html",
	array(
		"bugs_count" => $bugs_count,
		"ideas_count" => $ideas_count,
		"praises_count" => $praises_count,
		"bugs" => $bugs_html,
		"ideas" => $ideas_html,
		"praises" => $praises_html
	)
);

// Get the header.
$header = website_header(
	"ردود فعل الزوّار",
	"صفحة من أجل عرض ردود فعل الزوّار",
	array(
		"ردود", "فعل", "زوار", "عائلة", "الزغيبي"
	)
);
// Get the footer.
$footer = website_footer();

// Print the page.
echo $header;
echo $content;
echo $footer;
