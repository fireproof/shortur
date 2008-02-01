<?php
	
	require_once('default.inc.php');
	
	$shorty_db_host = $_REQUEST['shorty_db_host'];
	$shorty_db_username = $_REQUEST['shorty_db_username'];
	$shorty_db_password = $_REQUEST['shorty_db_password'];
	$shorty_db_name = d($_REQUEST['shorty_db_name'], 'shorty');

	$data = array();
	
	if ($_POST['submit']) {

		$shorty_db = @mysql_connect($shorty_db_host, $shorty_db_username, $shorty_db_password);
		if (!$shorty_db) {
			debug(array($shorty_db_host, $shorty_db_username, $shorty_db_password));
			$data['errors'][] = "Cannot connect to Shorty database with the credentials you supplied.";
			template($data);
			exit;
		}
		
		$shorty_db_select = mysql_select_db($shorty_db_name, $shorty_db);
		if (!$shorty_db_select) {
			$data['errors'][] = "The database $shorty_db_name on $shorty_db_host doesn't exist.";
			template($data);
			exit;
		}
		
		
		$shorty_entries = mysql_query("select * from shorty_shorties", $shorty_db);
		$shorty_users = mysql_query("select * from shorty_users", $shorty_db);
		
		if (mysql_errno($shorty_db)) {
			$data['errors'][] = "There was an error while retrieving the information from Shorty:  " . mysql_error($shorty_db);
			template($data);
			exit;
		}			

		$shorty_entries_array = $shorty_users_array = array();
		
		while ($user = mysql_fetch_object($shorty_users)) {
			if ($user->username != 'admin') {
				array_push($shorty_users_array, $user);
			}
		}
		while ($entry = mysql_fetch_object($shorty_entries)) {
			array_push($shorty_entries_array, $entry);
		}
		
		mysql_close($shorty_db);
	
		$shortur_db = mysql_connect($db_host, $db_username, $db_password);	
		mysql_select_db($db_name, $shortur_db);
	
		foreach ($shorty_users_array as $user) {
		
			mysql_query("insert into users (username, password) values (" .
				"'" . $user->username . "', " .
				"'" . $user->password . "')", $shortur_db);
		}
		
		foreach ($shorty_entries_array as $entry) {
		
			$short_url = $entry->key1;
			if ($entry->key2) $short_url .= '/' . $entry->key2;
			if ($entry->key3) $short_url .= '/' . $entry->key3;
			if ($entry->key4) $short_url .= '/' . $entry->key4;
			if ($entry->key5) $short_url .= '/' . $entry->key5;		
			
			mysql_query("insert into entries (user_id, target, short_url) values (" .
				$entry->user_id . ", " .
				"'" . $entry->target . "', " . 
				"'" . $short_url . "')", $shortur_db);
			
		}
		
		$data['messages'][] = "Import from Shorty successful.";
		$data['messages'][] = "<a href='admin.php'>Login to ShortUr Admin</a>";
		template($data);
		exit;
		
	}
	
	$data['content'] = <<<EOF

	
	<form action='migrate_from_shorty.php' method='post'>
		<div class='table'>
		
			<div class='table_header'>
				Migrate Shorty information to ShortUr
			</div>
		
			<div class='line_item'>
				<b>Shorty database host: </b>
				<input type='text' name='shorty_db_host' value='$shorty_db_host'/>
			</div>

			<div class='line_item_alt'>
				<b>Shorty database name: </b>
				<input type='text' name='shorty_db_name' value='$shorty_db_name'/>
			</div>			
			
			<div class='line_item'>
				<b>Shorty database username: </b>
				<input type='text' name='shorty_db_username' value='$shorty_db_username'/>
			</div>
	
			<div class='line_item_alt'>
				<b>Shorty database password: </b>
				<input type='password' name='shorty_db_password' />
			</div>
		
			<div class='line_item'>
				<input type='submit' name='submit' value='Submit' />
			</div>
		</div>
		
	</form>
	
EOF;

	template($data);
	
?>