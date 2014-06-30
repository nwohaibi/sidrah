<?php

require_once("inc/config.inc.php");

// Set some configurations.
$backup_filename = database_name . "_" . date("d_m_Y") . ".sql";
$compressed_backup_filename = "$backup_filename.gz";

// Execute the command of mysqldump and gzip.
$command = sprintf("mysqldump -h %s -u %s -p%s %s | gzip > $compressed_backup_filename", database_server, database_username, database_password, database_name);
exec($command);

// Send this file through email.
$headers  = "MIME-Version: 1.0\r\n";
$headers .= 'Content-Type: multipart/mixed; boundary="hossamzeeboundary"' . "\r\n";

$mime_message  = 'Content-Type: text/html; charset="utf8"' . "\r\n";
$mime_message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$mime_message .= "Hello.\r\n";
$mime_message .= "--hossamzeeboundary\r\n";

$mime_message .= "Content-Type: application/x-gzip; name=\"$compressed_backup_filename\"\r\n";
$mime_message .= "Content-Transfer-Encoding: base64\r\n";
$mime_message .= "Content-disposition: attachment; file=\"$compressed_backup_filename\"\r\n\r\n";
$mime_message .= chunk_split(base64_encode(file_get_contents($compressed_backup_filename)));
$mime_message .= "\r\n--hossamzeeboundary--";

$result = mail(
	"zee_hossam@hotmail.com, zzughaibi@hotmail.com, moath@lolaretail.com",
	"AlZughaibi Daily Backup ($compressed_backup_filename)",
	$mime_message,
	$headers
);

var_dump($result);

// Delete the backup file.
unlink($compressed_backup_filename);

