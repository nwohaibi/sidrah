<?php

require_once("inc/functions.inc.php");

$user = user_information();

// Check if the user is not admin
if ($user["group"] != "admin")
{
	echo error_message("لا يمكنك الوصول إلى هذه الصفحة.");
	return;
}

$submit = @$_POST["submit"];
$find_words = @$_POST["find_words"];
$replace_with_word = @$_POST["replace_with_word"];

if (!empty($submit))
{
	$s_words = explode("،", $find_words);
	$clean_words = array();
	
	// Walk up-onto s_words.
	foreach ($s_words as $s_word)
	{
		$s_word = trim($s_word);
		
		if (!empty($s_word))
		{
			$clean_words []= "'$s_word'";	
		}
	}
	
	// Start replacing locations with the entered ones.
	$clean_words_string = implode(",", $clean_words);
	
	mysql_query("UPDATE member SET location = '$replace_with_word' WHERE location IN ($clean_words_string)")or die(mysql_error());
	$affected_locations = mysql_affected_rows();
	
	echo success_message(
		"تم تحديث $affected_locations مكان إقامة"
	);
	
	return;
}
else
{
	$header = website_header(
		"استبدال أماكن الإقامة",
		"صفحة من أجل استبدال أماكن الإقامة.",
		array()
	);

	$footer = website_footer();

	echo $header;

	?>
	<div class="row">
		<div class="large-12 columns">
	
		<form action="fix_locations.php" method="post" class="inputform">

		<div class="row">
			<div class="large-4 columns">
				<label>ابحث عن الكلمات</label>
				<input type="text" name="find_words" />
			</div>
		</div>
		<div class="row">
			<div class="large-4 columns">
				<label>استبدلها بالكلمة</label>
				<input type="text" name="replace_with_word" />
			</div>
		</div>
		<div class="row">
			<div class="large-4 columns">
				<button class="small button" type="submit" name="submit" value="1">استبدال</button>
			</div>
		</div>
		
		</form>
		</div>
	</div>
	<?php

	echo $footer;
}
