<?php

	require_once('default.inc.php');
	$acceptable_http_response_codes = array('200', '302');
	
	session_start();
	
	function auth() {
	
		global $cookie_name;

		$data = array();
		$data['errors'] = array();
	
		// check current credentials
		if ($_COOKIE[$cookie_name]) {
			
			list($user, $pass) = split('\|', $_COOKIE[$cookie_name]);
			$result = q("select * from users where username = '" . s($user) . "'");
			
			if ($result[0]->password == $pass) {
				
				// set user information to session so admin.php can use it for queries
				$_SESSION['shortur_user_id'] = $result[0]->id;
				$_SESSION['shortur_username'] = $result[0]->username;
		
				// return so main script can continue
				return;
				
			} else {
			
				// unset cookie, unset session
				unset($_SESSION['shortur_user_id']);
				unset($_SESSION['shortur_username']);
				setcookie($cookie_name, null);
				
				// return so main script can continue
				return;				
			}
			
		} else {
		
			if ($_REQUEST['submit']) {
			
				$user = $_REQUEST['username'];
				$pass = md5($_REQUEST['password']);
				
				$result = q("select * from users where username = '" . s($user) . "' and password = '" . 
					s($pass) . "'");
				
				if ($result[0]->id) {
				
					// set user information to session so admin.php can use it for queries
					$_SESSION['shortur_user_id'] = $result[0]->id;
					$_SESSION['shortur_username'] = $result[0]->username;
					
					// set cookie
					setcookie($cookie_name, $user . '|' . $pass, ($_REQUEST['remember'] ? time()+(60*60*24*365) : "0"));						
					
					// reload page so cookies can be picked up
					header("Location: admin.php");
					
				} else {
				
					// set error message
					$data['errors'][] = "Invalid username and/or password";
					
				}
			
			}
			
			$data['content'] =<<<EOF
			
				<form name='login' action='admin.php' method='post'>
					<input type='hidden' name='action' value='login'>
					<div class='table'>
						<div class='table_header'>Please log in.</div>
						<div class='line_item'>
							<b>Username:</b> <input type='text' name='username' id='username' value='$user'/>
						</div>
						<div class='line_item'>
							<b>Password:</b> <input type='password' name='password' id='password' />
						</div>
						<div class='line_item'>
							<b>Remember me:</b> <input type='checkbox' name='remember' id='remember' value='1' />
						</div>
						<div class='line_item'>
							<input type='submit' name='submit' value='Login'>
						</div>
					</div>
				</form>		
EOF;

			template($data, false);
			
			// if the script has fallen thru to this point, we need to exit so that the main 
			// script won't keep executing.
			exit;
			
		}
		
	} // end auth()
	
	function validate_target($url) {
	
		global $acceptable_http_response_codes;
	
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
		
		// check to see that an acceptable HTTP header is returned from the URL
		$http_header = "";
		$socket = @fsockopen($host ,80);
		if ($socket) {
			fputs($socket, "HEAD $path HTTP/1.0\r\n\r\n");
			while(!feof($socket)) {
				$http_header .= fgets($socket);
			}
			fclose($socket);
		} else {
			return false;
		}
		
		$http_header = split("\n", $http_header);
		
		// parse the HTTP Response code out of the header
		preg_match("/^HTTP\/\d\.\d (\d\d\d)/", $http_header[0], $matches);
		
		if (in_array($matches[1], $acceptable_http_response_codes))
			return true;
		else 
			return false;
		
	}
	
	function validate_admin_user() {
	
		$user = q("select * from users where id = " . s($_SESSION[shortur_user_id]) . " and admin = 1");
		if (count($user))
			return true;
		else
			return false;
	
	}
	
	function short_urls($get_all_urls=false) {
		
		global $base_url;
		$whose_urls = ($get_all_urls ? "All" : "My");
		
		$output =<<<EOF
			<div class='table'>
				<div class='table_header'>$whose_urls Short URLs</div>
EOF;
			
		// get all the URLs for this user
		$sql = "select * from entries";
		
		if (!$get_all_urls)
			$sql .= " where user_id = " . s($_SESSION[shortur_user_id]);
		
		$result = q($sql);
		
		if ($result) {
			$n = 0;
			foreach ($result as $url) {
				$output .= "<div class='line_item" . ($n%2 ? '_alt' : '') . "'>";
				$output .= "<em>$base_url$url->short_url</em> points to <em>$url->target</em> " .
					"<a href='admin.php?action=edit&id=$url->id'>edit</a> " . 
					"<a href='admin.php?action=delete&id=$url->id'>delete</a>";
					
				$output .= "</div>";
				$n++;
			}
			
		} else {
			$output .= "<div class='line_item'>You have no Short URLs set up</div>";
		}
		
		$output .= "</div class='table'>";
		
		return $output;
				
	}
	
	function short_url_form($action='add', $short_url=null, $target_url='http://', $id=null) {
		
		global $base_url;
		$action_display = ucfirst($action);
		$action_form_element = strtolower($action);
		$hidden_form_id = "";
		
		if ($id) 
			$hidden_form_id = "<input type='hidden' name='id' value='$id' />";
		
		return <<<EOF

			<form action='admin.php' method='post'>
				$hidden_form_id
				<input type='hidden' name='action' value='$action_form_element'>		
				
				<div class='table'>
					<div class='table_header'>$action_display Short URL</div>
	
					
					<div class='line_item'>
						<b>Enter the full URL:</b> 
						<input type='text' name='target_url' value='$target_url' size='60' />
					</div>
					
					<div class='line_item'>
						<b>Choose a short URL:</b>
						<span style='color: #009;'>$base_url</span> 
						<input type='text' name='short_url' value='$short_url' size='40'/>
					</div>
					
					<div class='line_item'>
						<input type='submit' name='submit' value='$action_display Short Url'/>
					</div>
				</div>
				
			</form>
EOF;
	
	}
	
	function users() {
		
		$output =<<<EOF
			<div class='table'>
				<div class='table_header'>
					Users
				</div>
EOF;

		$users = q("select * from users");
		$n = 0;
		foreach ($users as $user) {
			
			if ($user->username == 'root') continue;
			
			$css_modifier = ($user->admin ? '_highlight' :  ($n%2 ? '_alt' : ''));
			$output .= "<div class='line_item$css_modifier'>";
			$output .= "<em>$user->username</em> " . 
				"<a href='admin.php?action=edit_user&id=$user->id'>edit</a> ";
			
			if ($user->id != $_SESSION['shortur_user_id'])
				$output .= "<a href='admin.php?action=delete_user&id=$user->id'>delete</a>";
				
			$output .= "</div>";			
			$n++;
			
		}
		
		$output .= "</div class='table'>";
		
		return $output;
	}
	
	function users_form($action='add_user', $username=null, $admin=null, $id=null) {
		
		list($tmp_action, $junk) = split("_", $action);
		$action_display = ucfirst($tmp_action);
		$action_form_element = strtolower($action);
		$hidden_form_id = "";
		$admin_checked = ($admin ? "checked" : "");
		
		if ($id) 
			$hidden_form_id = "<input type='hidden' name='id' value='$id' />";
		
		return <<<EOF
		
			<form action='admin.php' method='post'>
				$hidden_form_id
				<input type='hidden' name='action' value='$action_form_element'>
				
				<div class='table'>
					<div class='table_header'>$action_display User</div>
					<div class='line_item'>
						<b>Username:</b> <input type='text' name='username' value='$username'/> 
					</div>
					<div class='line_item_alt'>
						<b>Password:</b> <input type='password' name='password' />
					</div>
					<div class='line_item'>
						<b>Admin:</b> <input type='checkbox' name='admin' value='1' $admin_checked />
					</div>
					<div class='line_item'>
						<input type='submit' name='submit' value='$action_display User'/>
					</div>
				</div class='table'>
				
			</form>
EOF;

	}
	
?>
