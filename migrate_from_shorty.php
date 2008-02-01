<?php

	$shorty_db_host = 'cms.colum.edu';
	$shorty_db_username = 'shorty';
	$shorty_db_password = 'vmfjru47';
	$shorty_db_name = 'shorty';

	require_once('default.inc.php');

	$shorty_db = mysql_connect($shorty_db_host, $shorty_db_username, $shorty_db_password, $shorty_db_name);	
	mysql_select_db($shorty_db_name, $shorty_db);
	
	$shorty_entries = mysql_query("select * from shorty_shorties", $shorty_db);
	$shorty_users = mysql_query("select * from shorty_users", $shorty_db);
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
	
	$data['messages'][] = "Import from Shorty successful";
	template($data);
	
?>