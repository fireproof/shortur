<?php

	require_once('config.php');
	
	function db($db_host_in=null, $db_username_in=null, $db_password_in=null, $db_name_in=null) {
	
		global $db_host, $db_username, $db_password, $db_name;
	
		$db_host = d($db_host_in, $db_host);
		$db_username = d($db_username_in, $db_username);
		$db_password = d($db_password_in, $db_password);
		$db_name = d($db_name_in, $db_name);
	
		$db = @mysql_connect($db_host, $db_username, $db_password);
		if (!$db) {
			$data['errors'][] = "Cannot connect to database on $db_host";
			template($data);
			exit;
		} else {
		
			if (!mysql_select_db($db_name)) {
				$data['errors'][] = "Cannot select database $db_name";
				template($data);
				exit;
				
			}
		}
		
		return $db;
		
	}
	
	function q($sql, $page=1) {
		
		$db = db();
		
		$retval=array();
		$results = mysql_query($sql);
		
		if (mysql_error()) {
			$data = array();
			$data['errors'] = array("The MySQL server returned the following error: " . mysql_error());
			template($data, false);
			exit;
		}
		
		// if this is an insert or update command, there's no reason to get results
		// unless you want to throw an error
		if (preg_match("/^(update|insert|delete|create|drop)/i", $sql)) {
			return;
		}
		
		while ($result = mysql_fetch_object($results)) {
			$retval[] = $result;
		}
		
		return $retval;
	
	}

	function d($x=null, $y=null) {
		if ($x) return $x;
		else return $y;
	}
	
	function s($in) {
		$db = db();
		return mysql_real_escape_string($in, $db);
	}

	function template($data, $show_tabs=true) {
	
		global $http_path;
		
		$css_path = $http_path . "shortur.css";
		$css_ie_6_path = $http_path . "shortur.ie.6.css";
		$css_ie_7_path = $http_path . "shortur.ie.7.css";
		
		// build the errors HTML block

		$super_errors_html = "";
		if ($data['super_errors']) {
			$super_errors_html = "<div id='super_errors'>";
			foreach ($data['super_errors'] as $s)
				$super_errors_html .= "$s <br/>";
			$super_errors_html .= "</div>";
		}

		$errors_html = "";
		if ($data['errors']) {
			$errors_html = "<div id='errors'>";
			foreach ($data['errors'] as $e) 
				$errors_html .= $e . "<br/>";
			$errors_html .= "</div>";
		}
		
		// build the messages HTML block
		$messages_html = "";
		if ($data['messages']) {
			$messages_html = "<div id='messages'>";
			foreach ($data['messages'] as $m) 
				$messages_html .= $m . "<br/>";
			$messages_html .= "</div>";
		}

		// see if the current user is an admin
		if ($_SESSION['shortur_user_id']) {
		
			list($user) = q("select * from users where id = $_SESSION[shortur_user_id]");
			
			if ($user->admin) {
				$admin_tab =<<<EOF
					<div id='admin'>
						<span><a href='admin.php?action=admin'>User Admin</a></span>
					</div>
					<div id='add_user'>
						<span><a href='admin.php?action=add_user'>Add User</a></span>
					</div>
EOF;
				$all_urls_tab =<<<EOF
					<div id='all_urls'>
						<span><a href='admin.php?action=all_urls'>All Short URLs</a></span>
					</div>
EOF;
			}
		}
		
		if ($_SESSION['shortur_user_id'] && $show_tabs)
			$tabs =<<<EOF
				<div id='tabs'>
				
					<div id='main' class='first'>
						<span><a href='admin.php'>My Short URLs</a></span>
					</div>
					
					$all_urls_tab
					
					<div id='add'>
						<span><a href='admin.php?action=add'>Add Short URL</a></span>
					</div>
					
					$admin_tab
					
					<div id='logout'>
						<span><a href='admin.php?action=logout'>Logout</a></span>
					</div>			
				
				</div id='tabs'>
				
EOF;
	
		echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>ShortUr: The URL Shortener</title>
		<link rel='stylesheet' type='text/css' href='$css_path'>
		
		<!--[if lte IE 6]>
			<link rel='stylesheet' type='text/css' href='$css_ie_6_path'>
		<![endif]-->

		<!--[if IE 7]>
			<link rel='stylesheet' type='text/css' href='$css_ie_7_path'>
		<![endif]-->		
		
		<style>
			div#$data[tab] {
				background-color: #ccc;
			}
		</style>
	</head>
	<body>
		<div id='wrapper'>
			
			<div id='header'>
				<span class='large'>ShortUr:</span>
				<span class='small'>The URL shortener</span>
			</div>
			
			<div class='horizontal_rule_top'></div>

			$tabs
			
			$super_errors_html 

			$messages_html
			
			$errors_html
			
			<div id='content'>
				
				$data[content]
				
			</div>
		
		</div>
	</body>
</html>
EOF;

	}	
	
	function debug($in=null) {
		$in = ($in ? $in : time());
		print "<pre>";
		print_r($in);
		print "</pre>";
		exit;
	}
	
?>
