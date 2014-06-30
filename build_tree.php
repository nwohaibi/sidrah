<?php

require_once("inc/functions.inc.php");
require_once("classes/Diagram/class.diagram.php");

$diagram = new Diagram("data.xml");
$diagram->Draw();

/*
$inner_xml = "";

function rec($id)
{
	global $inner_xml;

	$q1 = mysql_query("SELECT id, father_id, name FROM member WHERE id = '$id' AND tribe_id = 1");
	$f1 = mysql_fetch_array($q1);
	
	if ($f1["father_id"] == -1)
	{
		$f1f = 0;
	}
	else
	{
		$f1f = $f1["father_id"];
	}

	$inner_xml .= "<node>$f1[name]";
	
	// Get children.
	$q2 = mysql_query("SELECT id FROM member WHERE father_id = '$f1[id]' AND gender = 1 AND tribe_id = 1");
	
	if (mysql_num_rows($q2) > 0)
	{
		while ($ch = mysql_fetch_array($q2))
		{
			rec($ch["id"]);
		}
	}
	
	$inner_xml .= "</node>";
}

rec(1);

$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<diagram>';
$xml .= $inner_xml;
$xml .= '</diagram>'; 

echo $xml;
*/

?>
