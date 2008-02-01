<?php

	require_once('default.inc.php');
	$config_file = 'config.php';
	
	$data = array();
	
	$estimated_httpd_path = 'http://' . $_SERVER['HTTP_HOST'] . 
		substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/')) . '/';

	$http_path = d($_REQUEST['http_path'], $estimated_httpd_path);
	$db_host = d($_REQUEST['db_host'], 'localhost');
	$db_username = $_REQUEST['db_username'];
	$db_password = $_REQUEST['db_password'];
	$db_name = d($_REQUEST['db_name'], 'shortur');
	$db_exists = $_REQUEST['db_exists'];
	$admin_password = $_REQUEST['admin_password'];
	$domain = d($_REQUEST['domain'], $_SERVER['HTTP_HOST']);
	
	// can this user write to the config file?
	if (!is_writeable($config_file)) {
		$data['errors'][] = 
			"The configuration file, $config_file, is not writeable by the web server user ' " . 
			`whoami` . " '.";
		$data['errors'][] = "The installer cannot continue.";
		template($data, false);
		exit;
	}
	
	// is mod_rewrite enabled?
	if (!check_mod_rewrite()) {
		$data['errors'][] = "mod_rewrite is not enabled on this server.";
		$data['errors'][] = "The installer cannot continue.";
		template($data, false);
		exit;
	}
	
	if ($_REQUEST['submit']) {

		if (!$http_path)
			$data['errors'][] = "You must specify a valid installation path";
		
		if (!$db_host)
			$data['errors'][] = "You must specify a database host.";
			
		if (!$db_username)
			$data['errors'][] = "You must specify a database user name.";
			
		if (!$db_password)
			$data['errors'][] = "You must specify a database password.";
			
		if (!$admin_password)
			$data['errors'][] = "You must specify an admin password.";
			
		if ($db_host && $db_username && $db_password && $db_name && $admin_password) {
			
			if (!($db = @mysql_connect($db_host, $db_username, $db_password))) {
			
				$data['errors'][] = "Cannot connect to the database with the credentials you supplied.";
				
			} else if ($db_exists && !mysql_select_db($db_name))  {
				
				$data['errors'][] = "The database '$_REQUEST[db_name]' does not exist on $_REQUEST[db_host].";
				
			} else if (!$db_exists) {
			
				$sql = "create database " . $db_name;
			
				$data['errors'][] = "The database credentials you provided do not have sufficient " . 
					"permissions to create the database.  Try creating the database before running this " .
					"installer.";
					
			}	
		}
		
		if (!$data['errors']) {

			write_settings($http_path, $db_host, $db_username, $db_password, $db_name, $domain);
			
			mysql_select_db($db_name);	
			
			$create_table_entries_sql ="CREATE TABLE `entries` (" .
				"`id` int(11) NOT NULL auto_increment," .
				"`user_id` int(11) NOT NULL default '0'," .
				"`target` text NOT NULL," .
				"`short_url` text NOT NULL," .
				"PRIMARY KEY (`id`))";
			
			
			$create_table_users_sql = "CREATE TABLE `users` ( " .
				"`id` int(11) NOT NULL auto_increment," .
				"`username` text NOT NULL," .
				"`password` text NOT NULL," .
				"`admin` tinyint(1) NOT NULL," .
				"PRIMARY KEY  (`id`))";

			$create_admin_user_sql = "insert into `users` (username, password, admin) values " . 
				"('admin', '" . md5($admin_password) . "', 1)";

			q($create_table_entries_sql);
			q($create_table_users_sql);
			q($create_admin_user_sql);
			
			$data['messages'][] = "Your ShortUr installation is complete!";
			$data['messages'][] = 
				"Be sure to change permissions on config.php to a non-writeable state";
			
			template($data, $false);
			exit;

		}
	}
	
	$data['content'] =<<<EOF
	
	<form action='install.php' method='post'>
	
		<div class='line_item'>
			<b>Installation Path: </b>
			<input type='text' name='http_path' value='$http_path' />
		</div>
	
		<div class='line_item_alt'>
			<b>Database Host: </b>
			<input type='text' name='db_host' value='$db_host'/>
		</div>
		
		<div class='line_item'>
			<b>Database Username: </b>
			<input type='text' name='db_username' value='$db_username'/>
		</div>

		<div class='line_item_alt'>
			<b>Database Password: </b>
			<input type='password' name='db_password' />
		</div>
		
		<div class='line_item'>
			<b>Database Name: </b>
			<input type='text' name='db_name' value='$db_name' /> <br/><br/>
			<input type='checkbox' name='db_exists' value='1'> This database has already been created.
		</div>
	
		<div class='line_item_alt'>
			<b>Domain: </b>
			<input type='text' name='domain' value='$domain' />
		</div>
		
		<div class='line_item'>
			<b>Admin Password:</b>
			<input type='password' name='admin_password' />
		</div>
		
		<div class='line_item_alt' style='text-align: center;'>
			<input type='submit' name='submit' value='Install ShortUr'>
		</div>
		
	</form>
	
EOF;
	
	template($data, false);

	function write_settings($in_http_path=null, $in_db_host=null, $in_db_username=null, 
		$in_db_password=null, $in_db_name=null, $in_domain=null, $in_cookie_name='shortur_auth') {

		global $config_file, $http_path, $db_host, $db_username, $db_password, $domain, $cookie_name;

		$output =<<<EOF
<?php

	\$http_path = '$in_http_path';
	\$db_host = '$in_db_host';
	\$db_username = '$in_db_username';
	\$db_password = '$in_db_password';
	\$db_name = '$in_db_name';
	\$domain = '$in_domain';
	\$cookie_name = '$in_cookie_name';	
	
?>
EOF;

		$f = fopen($config_file, 'w');
		fwrite($f, $output);
		fclose($f);
		
		$http_path = $in_http_path;
		$db_host = $in_db_host;
		$db_username = $in_db_username;
		$db_password = $in_db_password;
		$domain = $in_domain;
		$cookie_name = $in_cookie_name;
		
	}

	function check_mod_rewrite() {
	
		global $estimated_httpd_path;

		if (preg_match('/enabled/', wget($estimated_httpd_path . 'test_mod_rewrite')))
			return true;
		else 
			return false;
	
	}
	
	function wget($url) {
	
		// strip the http:// off the front and trim the url
		$url = trim(preg_replace("/^http:\/\//", "", $url));

		// split the URL on the first instance of a slash, if it exists
		$host = $path = "";
		if (strpos($url, '/')) {
			$host = substr($url, 0, strpos($url, '/'));
			$path = substr($url, strpos($url, '/'));
		} else {
			$host = $url;
			$path = '/';
		}
		
		$socket = @fsockopen($host, 80, $errorNumber, $errorString);
	
		if (!$socket) {
			return false;
		}
		
		$header = "GET ".$path."  HTTP/1.1\r\n";
		$header.= "Host: ".$host."\r\n";
		$header.= "Connection: close\r\n\r\n";
		
		fwrite($socket, $header);
		
		$response_header = '';
		$response_content = '';
		
		do {
			$response_header.= fread($socket, 1);
		}	while (!preg_match('/\\r\\n\\r\\n$/', $response_header));
		
		
		if (!strstr($response_header, "Transfer-Encoding: chunked")) {
			while (!feof($socket)) {
				$response_content.= fgets($socket, 128);
			}
		} else {
		
			while ($chunk_length = hexdec(fgets($socket))) {
			
				$response_content_chunk = '';
				$read_length = 0;
		
				while ($read_length < $chunk_length) {
					$response_content_chunk .= fread($socket, $chunk_length - $read_length);
					$read_length = strlen($response_content_chunk);
				}
				
				$response_content.= $response_content_chunk;
		
				fgets($socket);
			
			}
		}
		
		return chop($response_content);
	}

?>
