<?php

// TODO: Fix metas.
// TODO: Fix some authorities stuff.

require_once("inc/functions.inc.php");

$bank_codes = array(
	"SA0080" => "بنك الراجحي",
	"SA0015" => "بنك البلاد",
	"SA0030" => "البنك العربي الوطني",
	"SA0010" => "البنك الاهلي التجاري",
	"SA0060" => "بنك الجزيرة",
	"SA0020" => "بنك الرياض",
	"SA0050" => "البنك السعودي الهولندي",
	"SA0055" => "البنك السعودي الفرنسي",
	"SA0045" => "البنك السعودي البريطاني",
	"SA0040" => "البنك السعودي الامريكي",
	"SA0065" => "البنك السعودي للاستثمار",
	"SA0085" => "بنك الباريبس",
	"SA0081" => "بنك دويتشة",
	"SA0095" => "بنك الامارات",
	"SA0090" => "بنك الخليج",
	"SA0075" => "بنك الكويت الوطني",
	"SA0005" => "مصرف الانماء",
);

$red = "#C60F13";
$green = "#5DA423";

// Get the information of the user.
$user = user_information();

if ($user["group"] != "admin" && $user["group"] != "moderator")
{
	redirect_to_login();
	return;
}

$action = mysql_real_escape_string(@$_GET["action"]);

switch ($action)
{
	case "add_collector":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$name = mysql_real_escape_string(@$_POST["name"]);
			$role = mysql_real_escape_string(@$_POST["role"]);
			$assigned_root_name = mysql_real_escape_string(@$_POST["assigned_root_name"]);
			
			if (empty($name) || empty($assigned_root_name))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$collector = get_member_fullname($name);
			$assigned_root = get_member_fullname($assigned_root_name);
			
			// Insert a new collector.
			$now = time();
			$insert_collector_query = mysql_query("INSERT INTO box_collector (member_id, role, assigned_root_id, created) VALUES ('$collector[id]', '$role', '$assigned_root[id]', '$now')");
			
			// Done.
			echo success_message(
				"تمّت إضافة المحصّل بنجاح.",
				"family_box.php"
			);
		}
	
	break;
	
	case "edit_collector":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
	
		// Get the collector of the id entered.
		$get_collector_query = mysql_query("SELECT * FROM box_collector WHERE id = '$id'");
	
		if (mysql_num_rows($get_collector_query) == 0)
		{
			echo error_message("لم يتم العثور على المحصّل المحدّد.");
			return;
		}
	
		$collector = mysql_fetch_array($get_collector_query);
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$name = mysql_real_escape_string(@$_POST["name"]);
			$role = mysql_real_escape_string(@$_POST["role"]);
			$assigned_root_name = mysql_real_escape_string(@$_POST["assigned_root_name"]);
			
			if (empty($name) || empty($assigned_root_name))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$new_collector = get_member_fullname($name);
			$assigned_root = get_member_fullname($assigned_root_name);
			
			// Update collector.
			$insert_collector_query = mysql_query("UPDATE box_collector SET member_id = '$new_collector[id]', role = '$role', assigned_root_id  = '$assigned_root[id]' WHERE id = '$collector[id]'");
			
			// Done.
			echo success_message(
				"تمّت إضافة المحصّل بنجاح.",
				"family_box.php"
			);
		}
		else
		{
			$collector_member = get_member_id($collector["member_id"]);
			$collector_assigned_root = get_member_id($collector["assigned_root_id"]);
		
			// Get the content.
			$content = template(
				"views/box_edit_collector.html",
				array(
					"id" => $collector["id"],
					"fullname" => $collector_member["fullname"],
					"assigned_root_name" => $collector_assigned_root["fullname"],
					"role" => $collector["role"]
				)
			);
			
			// Get the header.
			$header = website_header();
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "add_subscriber":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$name = mysql_real_escape_string(@$_POST["name"]);
			
			if (empty($name))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$subscriber = get_member_fullname($name);
			
			// Insert a new subscriber.
			$now = time();
			$insert_subscriber_query = mysql_query("INSERT INTO box_subscriber (member_id, created) VALUES ('$subscriber[id]', '$now')");
			
			// Done.
			echo success_message(
				"تمّت إضافة المشترك بنجاح.",
				"family_box.php?action=view_subscribers"
			);
		}
	
	break;
	
	case "add_account":
	
		$subscriber_id = mysql_real_escape_string(@$_GET["subscriber_id"]);
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		// Check if the subscriber does exist.
		$get_subscriber_query = mysql_query("SELECT * FROM box_subscriber WHERE id = '$subscriber_id'");

		if (mysql_num_rows($get_subscriber_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}

		// Fetch the subscriber.
		$subscriber = mysql_fetch_array($get_subscriber_query);

		if (!empty($submit))
		{
			$account_number = arabic_number(mysql_real_escape_string(@$_POST["account_number"]));
			$bank_code = mysql_real_escape_string(@$_POST["bank_code"]);
			
			if (empty($account_number))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$iban = iban($bank_code, $account_number);

			// Insert a new account.
			$now = time();
			$insert_account_query = mysql_query("INSERT INTO box_account (subscriber_id, iban, created) VALUES ('$subscriber[id]', '$iban', '$now')");
			
			// Done.
			echo success_message(
				"تمّت إضافة الحساب بنجاح.",
				"family_box.php?action=view_accounts&subscriber_id=$subscriber[id]"
			);
		}
	break;
	
	case "edit_account":
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		// Check if the account does exist.
		$get_account_query = mysql_query("SELECT box_account.*, member.fullname FROM box_account, box_subscriber, member WHERE box_account.subscriber_id = box_subscriber.id AND box_subscriber.member_id = member.id AND box_account.id = '$id'");

		if (mysql_num_rows($get_account_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}

		// Fetch the account.
		$account = mysql_fetch_array($get_account_query);

		$banks_html = "<select name='bank_code' id='banks'>";
		
		foreach ($bank_codes as $k => $v)
		{
			$banks_html .= "<option value='$k'>$v</option>\n";
		}
		
		$banks_html .= "</select>";

		if (!empty($submit))
		{
			$account_number = arabic_number(mysql_real_escape_string(@$_POST["account_number"]));
			$bank_code = mysql_real_escape_string(@$_POST["bank_code"]);
			
			if (empty($account_number))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			$iban = iban($bank_code, $account_number);

			// Update the account.
			$update_account_query = mysql_query("UPDATE box_account SET iban = '$iban' WHERE id = '$account[id]'");
			
			// Done.
			echo success_message(
				"تمّت تحديث الحساب بنجاح.",
				"family_box.php?action=view_accounts&subscriber_id=$account[subscriber_id]"
			);
		}
		else
		{
			$code = substr($account["iban"], 0, 2) . "00" . substr($account["iban"], 4, 2);
			$account_number = ltrim(substr($account["iban"], 6), "0");
		
			// Get the content.
			$content = template(
				"views/box_edit_account.html",
				array(
					"id" => $account["id"],
					"name" => $account["fullname"],
					"banks" => $banks_html,
					"account_number" => $account_number,
					"code" => $code,
				)
			);
			
			// Get the header.
			$header = website_header();
			
			// Get the footer.
			$footer = website_footer();
			
			// Print the page.
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "add_behavior":

		$account_id = mysql_real_escape_string(@$_GET["account_id"]);
		$submit = mysql_real_escape_string(@$_POST["submit"]);
		
		// Check if the account does exist.
		$get_account_query = mysql_query("SELECT * FROM box_account WHERE id = '$account_id'");

		if (mysql_num_rows($get_account_query) == 0)
		{
			echo error_message("لم يتم العثور على الحساب البنكي.");
			return;
		}

		// Fetch the account.
		$account = mysql_fetch_array($get_account_query);

		if (!empty($submit))
		{
			$amount = arabic_number(mysql_real_escape_string(@$_POST["amount"]));
			$start_day = arabic_number(mysql_real_escape_string(@$_POST["start_day"]));
			$start_month = mysql_real_escape_string(@$_POST["start_month"]);
			$start_year = arabic_number(mysql_real_escape_string(@$_POST["start_year"]));
			$end_day = arabic_number(mysql_real_escape_string(@$_POST["end_day"]));
			$end_month = mysql_real_escape_string(@$_POST["end_month"]);
			$end_year = arabic_number(mysql_real_escape_string(@$_POST["end_year"]));
			$dayofmonth = arabic_number(mysql_real_escape_string(@$_POST["dayofmonth"]));
			$for = mysql_real_escape_string(@$_POST["for"]);
			$other = mysql_real_escape_string(@$_POST["other"]);
			
			// Check the empty feilds.
			if (empty($amount) || empty($start_day) || empty($start_month) || empty($start_year) || empty($end_day) || empty($end_month) || empty($end_year) || empty($dayofmonth))
			{
				echo error_message("الرجاء تعبئة الحقول المطلوبة.");
				return;
			}
			
			// Set the dates.
			$start_date = sprintf("%04d-%02d-%02d", $start_year, $start_month, $start_day);
			$end_date = sprintf("%04d-%02d-%02d", $end_year, $end_month, $end_day);
			
			// Insert the behavior.
			$now = time();
			$insert_behavior_query = mysql_query("INSERT INTO box_behavior (account_id, from_date, to_date, dayofmonth, for_id, details, amount, created) VALUES ('$account[id]', '$start_date', '$end_date', '$dayofmonth', '$for', '$other', '$amount', '$now')")or die(mysql_error());
			
			echo success_message(
				"تمّت إضافة الاستقطاع الشهري بنجاح.",
				"family_box.php?action=view_behaviors&account_id=$account[id]"
			);
			
		}
	break;
	
	case "add_for":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$name = mysql_real_escape_string(@$_POST["name"]);
			$type = mysql_real_escape_string(@$_POST["type"]);
			
			if (empty($name) || empty($type))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Insert a new for.
			$now = time();
			$insert_for_query = mysql_query("INSERT INTO box_for (type, name, created) VALUES ('$type', '$name', '$now')");
			
			// Done.
			echo success_message(
				"تمّت إضافة الغرض بنجاح.",
				"family_box.php?action=view_fors"
			);
		}
	
	break;
	
	case "add_transaction":
	
		// Withdraws
		$get_fors_w_query = mysql_query("SELECT * FROM box_for WHERE type = 'withdraw'");
		$fors_w_array = array();
		$fors_w = "";
		
		if (mysql_num_rows($get_fors_w_query) == 0)
		{
			$fors_w = "Array()";
		}
		else
		{		
			while ($w = mysql_fetch_array($get_fors_w_query))
			{
				$fors_w_array []= "{ 'id': $w[id], 'name': '$w[name]' }";
			}
			
			$fors_w = "[" . implode($fors_w_array, ", ") . "]";
		}
		
		// Deposit
		$get_fors_d_query = mysql_query("SELECT * FROM box_for WHERE type = 'deposit'");
		$fors_d_array = array();
		$fors_d = "";
		
		if (mysql_num_rows($get_fors_d_query) == 0)
		{
			$fors_d = "Array()";
		}
		else
		{		
			while ($d = mysql_fetch_array($get_fors_d_query))
			{
				$fors_d_array []= "{ 'id': $d[id], 'name': '$d[name]' }";
			}
			
			$fors_d = "[" . implode($fors_d_array, ", ") . "]";
		}
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$type = mysql_real_escape_string(@$_POST["type"]);
			$account_id = mysql_real_escape_string(@$_POST["account_id"]);
			$amount = mysql_real_escape_string(@$_POST["amount"]);
			$for = mysql_real_escape_string(@$_POST["for"]);
			$other = mysql_real_escape_string(@$_POST["other"]);
			
			if (empty($account_id) || empty($amount) || ($for == -1 && empty($other)))
			{
				echo error_message("الرجاء إدخال الحقول المطلوبة.");
				return;
			}
			
			// Add a pending transaction.
			add_transaction($account_id, $amount, $type, $for, $other, "direct", $user["id"]);

			// Done.
			echo success_message(
				"تمّت إضافة العملية بنجاح.",
				"family_box.php?action=view_transactions"
			);
		}
		else
		{
			// Get the content.
			$content = template(
				"views/add_transaction.html",
				array(
					"for_w" => $fors_w,
					"for_d" => $fors_d
				)
			);
			
			// Get the header.
			$header = website_header();
			
			// Get the footer.
			$footer = website_footer();
			
			echo $header;
			echo $content;
			echo $footer;
		}
	break;
	
	case "accept_transaction":
	
		// TODO: Check if the user is manager.
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if there is a transaction.
		$get_transaction_query = mysql_query("SELECT * FROM box_transaction WHERE id = '$id'");
		
		if (mysql_num_rows($get_transaction_query) == 0)
		{
			echo error_message("لم يتم العثور على العملية المطلوبة.");
			return;
		}
		
		// Fetch transaction.
		$transaction = mysql_fetch_array($get_transaction_query);
		
		// Get the account information.
		$get_account_query = mysql_query("SELECT * FROM box_account WHERE id = '$transaction[account_id]'");
		$account = mysql_fetch_array($get_account_query);
		
		// Update the transaction and +/- to balance.
		$account_balance = (int) $account["balance"];
		$new_balance = $account_balance + (-$transaction["amount"]);
		
		$now = time();
		
		// Update the transaction to accepted.
		$update_transaction_query = mysql_query("UPDATE box_transaction SET status = 'accepted', executed_at = '$now', executed_by = '$user[id]' WHERE id = '$transaction[id]'");
		
		// Update the balance to the account.
		$update_account_query = mysql_query("UPDATE box_account SET balance = '$new_balance' WHERE id = '$account[id]'");
	
		// Done.
		echo success_message(
			"تمّ قبول العملية بنجاح.",
			"family_box.php?action=view_transactions"
		);
	
	break;
	
	case "reject_transaction":
	
		// TODO: Check if the user is manager.
	
		$id = mysql_real_escape_string(@$_GET["id"]);
		
		// Check if there is a transaction.
		$get_transaction_query = mysql_query("SELECT * FROM box_transaction WHERE id = '$id'");
		
		if (mysql_num_rows($get_transaction_query) == 0)
		{
			echo error_message("لم يتم العثور على العملية المطلوبة.");
			return;
		}
		
		// Fetch transaction.
		$transaction = mysql_fetch_array($get_transaction_query);
		
		$now = time();
		
		// Update the transaction to accepted.
		$update_transaction_query = mysql_query("UPDATE box_transaction SET status = 'rejected', executed_at = '$now', executed_by = '$user[id]' WHERE id = '$transaction[id]'");
	
		// Done.
		echo success_message(
			"تمّ رفض العملية بنجاح.",
			"family_box.php?action=view_transactions"
		);
	
	break;
	
	case "view_subscribers":
	
		// TODO: Only who is a collector.
		$get_roots_query = mysql_query("SELECT member.id, member.fullname FROM member, user WHERE user.assigned_root_id = member.id AND user.usergroup = 'moderator'");
		$subscribers_html = "";
	
		while ($root = mysql_fetch_array($get_roots_query))
		{		
			// Get the subscribers from database.
			$get_subscribers_query = mysql_query("SELECT member.fullname AS fullname, box_subscriber.*, (SELECT SUM(balance) FROM box_account WHERE subscriber_id = box_subscriber.id) AS balance, (SELECT COUNT(id) FROM box_account WHERE subscriber_id = box_subscriber.id) AS accounts FROM member, box_subscriber WHERE box_subscriber.member_id = member.id AND member.fullname LIKE '%$root[fullname]'");
		
			if (mysql_num_rows($get_subscribers_query) == 0)
			{
				//$subscribers_html .= "<tr><td colspan='4'>لم يتم العثور على مشتركين.</td></tr>";
			}
			else
			{
				$subscribers_count = mysql_num_rows($get_subscribers_query);
				$subscribers_html .= "<tr><td colspan='4'><b>فرع $root[fullname] ($subscribers_count)</b></td></tr>";
			
				// Get them.
				while ($subscriber = mysql_fetch_array($get_subscribers_query))
				{
					$balance = (int) $subscriber["balance"];
					$subscribers_html .= "<tr><td><a href='family_box.php?action=view_accounts&subscriber_id=$subscriber[id]'>$subscriber[fullname]</a></td><td>$subscriber[accounts]</td><td>$balance</td><td><a href='family_box.php?action='>عرض العمليات</a></td></tr>";
				}
			}
		}
		
		// Get the content of the page.
		$content = template(
			"views/box_view_subscribers.html",
			array(
				"subscribers" => $subscribers_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "view_accounts":

		// TODO: Check if the user is able to access this page.
		$subscriber_id = mysql_real_escape_string(@$_GET["subscriber_id"]);
	
		// Check if the subsciber exists.
		$get_subsciber_query = mysql_query("SELECT * FROM box_subscriber WHERE id = '$subscriber_id'");

		if (mysql_num_rows($get_subsciber_query) == 0)
		{
			echo error_message("لم يتم العثور على المشترك.");
			return;
		}
		
		$subscriber = mysql_fetch_array($get_subsciber_query);
		$subscriper_member = get_member_id($subscriber["member_id"]);
		
		// Get the accounts of the subscriber.
		$get_accounts_query = mysql_query("SELECT box_account.*, (SELECT COUNT(*) FROM box_behavior WHERE account_id = box_account.id) AS behavior_counts FROM box_account WHERE subscriber_id = '$subscriber[id]'");
		$accounts_html = "";

		if (mysql_num_rows($get_accounts_query) == 0)
		{
			$accounts_html = "<tr><td colspan='4'>لم يتم إضافة حسابات.</td></tr>";
		}
		else
		{
			// Get the accounts.
			while ($account = mysql_fetch_array($get_accounts_query))
			{
				$code = substr($account["iban"], 0, 2) . "00" . substr($account["iban"], 4, 2);
				$bank = $bank_codes[$code];
				
				$balance = (int) $account["balance"];
				$accounts_html .= "<tr><td><blockquote><a href='#'>$account[iban]</a><cite><b>$bank</b></cite></blockquote></td><td><a href='family_box.php?action=view_behaviors&account_id=$account[id]'>$account[behavior_counts]</a></td><td>$balance</td><td><a href='family_box.php?action=edit_account&id=$account[id]'>تعديل</a></td></tr>";
			}
		}
		
		$banks_html = "<select name='bank_code'>";
		
		foreach ($bank_codes as $k => $v)
		{
			$banks_html .= "<option value='$k'>$v</option>\n";
		}
		
		$banks_html .= "</select>";
		
		// Get the content of the page.
		$content = template(
			"views/box_view_accounts.html",
			array(
				"subscriber_id" => $subscriber["id"],
				"name" => shorten_name($subscriper_member["fullname"]),
				"accounts" => $accounts_html,
				"banks" => $banks_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "view_behaviors":
	
		$account_id = mysql_real_escape_string(@$_GET["account_id"]);
	
		// Check if the account does exist.
		$get_account_query = mysql_query("SELECT box_account.*, member.fullname FROM box_account, box_subscriber, member WHERE box_account.id = '$account_id' AND box_account.subscriber_id = box_subscriber.id AND box_subscriber.member_id = member.id")or die(mysql_error());

		if (mysql_num_rows($get_account_query) == 0)
		{
			echo error_message("لا يمكن الوصول إلى هذه الصفحة.");
			return;
		}
		
		// Fetch the account.
		$account = mysql_fetch_array($get_account_query);
		
		// Get the fullname.
		$shorten_name = shorten_name($account["fullname"]);

		// Get the behaviors.
		$get_behaviors_query = mysql_query("SELECT * FROM box_behavior WHERE account_id = '$account[id]'");
		$behaviors_html = "";
		
		if (mysql_num_rows($get_behaviors_query) == 0)
		{
			$behaviors_html = "<tr><td colspan='6'>لا يوجد استقطاعات شهرية مُضافة.</td></tr>";
		}
		else
		{
			while ($behavior = mysql_fetch_array($get_behaviors_query))
			{
				$behaviors_html .= "<tr><td><input type='checkbox' name='check[$behavior[id]]' /></td><td>$behavior[amount]</td><td>$behavior[dayofmonth]</td><td>$behavior[from_date]</td><td>$behavior[to_date]</td><td><a href='#'>تعديل</a></td></tr>";
			}
		}
 
		// Get the fors.
		$get_fors_query = mysql_query("SELECT * FROM box_for WHERE type = 'deposit'");
		$fors_html = "";
		
		while ($for = mysql_fetch_array($get_fors_query))
		{
			$fors_html .= "<option value='$for[id]'>$for[name]</option>";
		}

		// Get the content of the page.
		$content = template(
			"views/box_view_behaviors.html",
			array(
				"name" => $shorten_name,
				"account_id" => $account["id"],
				"fors" => $fors_html,
				"behaviors" => $behaviors_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "view_fors":

		// Get the fors.
		$get_fors_query = mysql_query("SELECT * FROM box_for ORDER BY type");
		$fors_html = "";
		
		if (mysql_num_rows($get_fors_query) == 0)
		{
			$fors_html = "<tr><td colspan='4'>لا يوجد أغراض مُضافة.</td></tr>";
		}
		else
		{
			while ($for = mysql_fetch_array($get_fors_query))
			{
				$type = ($for["type"] == "deposit") ? "إيداع" : "سحب";
				$fors_html .= "<tr><td><input type='checkbox' name='check[$for[id]]' /></td><td>$for[name]</td><td>$type</td><td><a href='#'>تعديل</a></td></tr>";
			}
		}

		// Get the content of the page.
		$content = template(
			"views/box_view_fors.html",
			array(
				"fors" => $fors_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "view_transactions":

		// Withdraws
		$get_withdraw_query = mysql_query("SELECT SUM(amount) AS amount FROM box_transaction WHERE status = 'accepted' AND type = 'withdraw'");
		$fetch_withdraw = mysql_fetch_array($get_withdraw_query);
		$total_withdraws = (int) $fetch_withdraw["amount"];
		
		// Deposit
		$get_deposit_query = mysql_query("SELECT SUM(amount) AS amount FROM box_transaction WHERE status = 'accepted' AND type = 'deposit'");
		$fetch_deposit = mysql_fetch_array($get_deposit_query);
		$total_deposits = (int) $fetch_deposit["amount"];

		$balance = $total_deposits + $total_withdraws;

		$get_pending_transactions_query = mysql_query("SELECT box_transaction.*, box_account.iban, box_account.subscriber_id, member.fullname, (SELECT name FROM box_for WHERE id = box_transaction.for_id) AS for_string FROM box_transaction, box_account, box_subscriber, member WHERE box_transaction.account_id = box_account.id AND box_subscriber.member_id = member.id AND box_account.subscriber_id = box_subscriber.id AND box_transaction.status = 'pending' ORDER BY box_transaction.created ASC");
		$pending_transactions_count = mysql_num_rows($get_pending_transactions_query);
		$pending_transactions_html = "";
		
		if ($pending_transactions_count == 0)
		{
			$pending_transactions_html = "<tr><td colspan='6'>لا يوجد عمليات معلّقة حتّى الآن.</td></tr>";
		}
		else
		{
			while ($pt = mysql_fetch_array($get_pending_transactions_query))
			{
				$amount = number_format(abs($pt["amount"]));
				$amount = ($pt["amount"] > 0) ? "<span style='color: $green'>+$amount</span>" : "<span style='color: $red'>-$amount</span>";
				$shorten_name = shorten_name($pt["fullname"]);
				$for = (empty($pt["for_string"])) ? $pt["details"] : $pt["for_string"];
				$triggered_by = ($pt["triggered_by"] == "direct") ? "مباشر" : "استقطاع";
				$created_by = get_user_id($pt["created_by"]);
				$created = arabic_date(date("d M Y, H:i:s", $pt["created"]));
				
				$pending_transactions_html .= "<tr><td><h4>$amount</h4></td><td><blockquote>$pt[iban]<cite><b><a href='family_box.php?action=view_accounts&subscriber_id=$pt[subscriber_id]'>$shorten_name</a></b></cite></blockquote></td><td>$triggered_by</td><td>$for</td><td><blockquote><a href='familytree.php?id=$created_by[member_id]'>$created_by[username]</a><cite>$created</cite></blockquote></td><td><a href='family_box.php?action=accept_transaction&id=$pt[id]' class='small button'>قبول</a> <a href='family_box.php?action=reject_transaction&id=$pt[id]' class='small button alert'>رفض</a></td></tr>";
			}
		}

		$get_transactions_query = mysql_query("SELECT box_transaction.*, box_account.iban, box_account.subscriber_id, member.fullname, (SELECT name FROM box_for WHERE id = box_transaction.for_id) AS for_string FROM box_transaction, box_account, box_subscriber, member WHERE box_transaction.account_id = box_account.id AND box_subscriber.member_id = member.id AND box_account.subscriber_id = box_subscriber.id AND box_transaction.status <> 'pending' ORDER BY box_transaction.executed_at DESC");
		$transactions_count = mysql_num_rows($get_transactions_query);
		$transactions_html = "";
		
		if ($transactions_count == 0)
		{
			$transactions_html = "<tr><td colspan='7'>لا يوجد عمليات حتّى الآن.</td></tr>";
		}
		else
		{
			while ($t = mysql_fetch_array($get_transactions_query))
			{
				$decision = ($t["status"] == "accepted") ? "<span class='label success round'>مقبول</span>" : "<span class='label alert round'>مرفوض</span>";
				$amount = number_format(abs($t["amount"]));
				$amount = ($t["amount"] > 0) ? "<span style='color: $green'>+$amount</span>" : "<span style='color: $red'>-$amount</span>";
				$shorten_name = shorten_name($t["fullname"]);
				$for = (empty($t["for_string"])) ? $t["details"] : $t["for_string"];
				$triggered_by = ($t["triggered_by"] == "direct") ? "مباشر" : "استقطاع";
				$created_by = get_user_id($t["created_by"]);
				$created = arabic_date(date("d M Y, H:i:s", $t["created"]));
				$executed_by = get_user_id($t["executed_by"]);
				$executed = arabic_date(date("d M Y, H:i:s", $t["executed_at"]));
				
				$transactions_html .= "<tr><td>$decision</td><td><h4>$amount</h4></td><td><blockquote>$t[iban]<cite><b><a href='family_box.php?action=view_accounts&subscriber_id=$t[subscriber_id]'>$shorten_name</a></b></cite></blockquote></td><td>$triggered_by</td><td>$for</td><td><blockquote><a href='familytree.php?id=$created_by[member_id]'>$created_by[username]</a><cite>$created</cite></blockquote></td><td><blockquote><a href='familytree.php?id=$executed_by[member_id]'>$executed_by[username]</a><cite>$executed</cite></blockquote></td></tr>";
			}
		}

		$total_withdraws_f = number_format($total_withdraws);
		$total_deposits_f = number_format($total_deposits);
		$balance_f = number_format($balance);

		if ($total_withdraws < 0)
		{
			$total_withdraws = abs($total_withdraws);
			$total_withdraws_f = "<span style='color: $red'>-" . number_format($total_withdraws) . "</span>";
		}
		
		// Deposits.
		if ($total_deposits > 0)
		{
			$total_deposits = abs($total_deposits);
			$total_deposits_f = "<span style='color: $green'>+"  . number_format($total_deposits) . "</span>";
		}
		
		// Balance.
		if ($balance < 0)
		{
			$balance = abs($balance);	
			$balance_f = "<span style='color: $red'>-" . number_format($balance) . "</span>";
		}
		else if ($balance > 0)
		{
			$balance = abs($balance);
			$balance_f = "<span style='color: $green'>+" . number_format($balance) . "</span>";
		}

		// Get the content of the page.
		$content = template(
			"views/box_view_transactions.html",
			array(
				"total_withdraws" => $total_withdraws_f,
				"total_deposits" => $total_deposits_f,
				"balance" => $balance_f,
				"pending_transactions_count" => $pending_transactions_count,
				"pending_transactions" => $pending_transactions_html,
				"transactions_count" => $transactions_count,
				"transactions" => $transactions_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	
	break;
	
	case "update_collectors":
	
		$submit = mysql_real_escape_string(@$_POST["submit"]);
	
		if (!empty($submit))
		{
			$check = @$_POST["check"];
			
			if (empty($check))
			{
				echo error_message("الرجاء اختيار محصّل واحد على الأقل.");
				return;
			}
			
			if (count($check) > 0)
			{
				foreach ($check as $k => $v)
				{
					$delete_collector_query = mysql_query("DELETE FROM box_collector WHERE id = '$k'");
				}
			}
			
			// Done.
			echo success_message(
				"تم حذف المحصّلين المحدّدين بنجاح.",
				"family_box.php"
			);
		}
	
	break;

	default: case "view_collectors":
	
		// TODO: Only for admins.
		// Also, a manager of box.
	
		// Get the collectors from database.
		$get_collectors_query = mysql_query("SELECT m1.fullname AS fullname, m2.fullname AS root_fullname, box_collector.* FROM box_collector, member m1, member m2 WHERE m1.id = box_collector.member_id AND box_collector.assigned_root_id = m2.id");
		$collectors_html = "";
		
		if (mysql_num_rows($get_collectors_query) == 0)
		{
			$collectors_html = "<tr><td colspan='5'>لم يتم إضافة محصّلين و لا مدراء.</td></tr>";
		}
		else
		{
			// Get them.
			while ($collector = mysql_fetch_array($get_collectors_query))
			{
				$role = ($collector["role"] == "collector") ? "محصّل" : "أمين الصندوق";
				$collectors_html .= "<tr><td><input type='checkbox' name='check[$collector[id]]' /></td><td><a href='family_tree.php?id=$collector[member_id]'>$collector[fullname]</a></td><td>$role</td><td>$collector[root_fullname]</td><td><a href='family_box.php?action=edit_collector&id=$collector[id]'>تعديل</a></td></tr>";
			}
		}

		// Get the content of the page.
		$content = template(
			"views/box_view_collectors.html",
			array(
				"collectors" => $collectors_html
			)
		);
		
		// Get the header.
		$header = website_header();
		
		// Get the footer.
		$footer = website_footer();
		
		// Print the page.
		echo $header;
		echo $content;
		echo $footer;
	break;
	
	case "box_stats":
		
		// Get the subscribers from database.
			$get_subscribers_query = mysql_query("SELECT member.fullname AS fullname, box_subscriber.*, (SELECT SUM(balance) FROM box_account WHERE subscriber_id = box_subscriber.id) AS balance, (SELECT COUNT(id) FROM box_account WHERE subscriber_id = box_subscriber.id) AS accounts FROM member, box_subscriber WHERE box_subscriber.member_id = member.id AND member.fullname LIKE '%$root[fullname]'");
				
	break;
}
