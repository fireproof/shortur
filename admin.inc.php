<?php

	require_once('default.inc.php');
	
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
							<a href='forgot.php'>Forgot your password?</a>
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
	
	function validate_admin_user() {
	
		$user = q("select * from users where id = " . s($_SESSION[shortur_user_id]) . " and admin = 1");
		if (count($user))
			return true;
		else
			return false;
	
	}
	
	function short_urls($get_all_urls=false) {
		
		global $http_path;
		global $base_url;
		global $per_page;
		global $show_pages;
		
		$page = d($_REQUEST['page'], 1);
		$action = $_REQUEST['action'];		
		
		$whose_urls = ($get_all_urls ? "All" : "My");
		
		// get all the URLs for this user
		$sql = "select * from entries";
		
		if (!$get_all_urls)
			$sql .= " where user_id = " . s($_SESSION[shortur_user_id]);
		
		$results = q($sql);
		
		return _display_short_urls($results, "$whose_urls Short URLs", "You have no Short URLs set up");
				
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

		global $per_page;

		$users = q("select * from users");
		
		list($paging_links, $start, $end) = _paging($users);
		$users = array_splice($users, $start, $per_page);

		$output =<<<EOF
			<div class='table'>
				<div class='table_header'>
					<div class='table_header_title'>Users</div>
					$paging_links
				</div>
EOF;
		
		$n = 0;
		foreach ($users as $user) {
			
			$css_modifier = ($user->admin ? '_highlight' :  ($n%2 ? '_alt' : ''));
			$output .= "<div class='line_item$css_modifier'>";
			$output .= "<em>$user->username</em> " . 
				"<a href='admin.php?action=edit_user&id=$user->id'>edit</a> ";
			
			if ($user->id != $_SESSION['shortur_user_id'])
				$output .= "<a href='admin.php?action=delete_user&id=$user->id'>delete</a>";
				
			$output .= "</div>";			
			$n++;
			
		}
		
		if ($paging_links)
			$output .= "<div class='table_footer'>$paging_links</div>";
		
		$output .= "</div class='table'>";
		
		return $output;
	}
	
	function users_form($action='add_user', $username=null, $email=null, $admin=null, $id=null) {
		
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
						<b>Email:</b> <input type='text' name='email' value='$email' /> 
					</div>
					<div class='line_item'>
						<b>Password:</b> <input type='password' name='password' />
					</div>
					<div class='line_item_alt'>
						<b>Admin:</b> <input type='checkbox' name='admin' value='1' $admin_checked />
					</div>
					<div class='line_item'>
						<input type='submit' name='submit' value='$action_display User'/>
					</div>
				</div class='table'>
				
			</form>
EOF;

	}
	
	function search_form($query=null) {
	
		return <<<EOF
		
			<form action='admin.php' method='get'>
				<input type='hidden' name='action' value='search'>
				
				<div class='table'>
					<div class='table_header'>Search for Short URL</div>
					<div class='line_item'>
						<b>Search For:</b> <input type='text' name='query' value='$query'/> 
					</div>
					<div class='line_item'>
						<input type='submit' name='submit' value='Search'/>
					</div>
				</div class='table'>
				
			</form>
EOF;
	
	}
	
	function _display_short_urls($results, $title, $no_results_text="No Results") {
	
		global $http_path;
		global $base_url;
		global $per_page;
		global $show_pages;	

		list($paging_links, $start, $end) = _paging($results);
		$results = array_splice($results, $start, $per_page);		
	
		$output =<<<EOF
			<div class='table'>
				<div class='table_header'>
					<div class='table_header_title'>$title</div>
					$paging_links
				</div>
EOF;
			
		if ($results) {
			$n = 0;
			foreach ($results as $url) {
				$output .= "<div class='line_item" . ($n%2 ? '_alt' : '') . "'>";
				$output .= "<em>$url->short_url</em> points to <em>$url->target</em> " .
					"<span class='line_item_action'><a href='admin.php?action=edit&id=$url->id'>edit</a> " . 
					"<a href='admin.php?action=delete&id=$url->id'>delete</a></span>";
					
				$output .= "</div>";
				$n++;
			}
			
		} else {
			$output .= "<div class='line_item'>$no_results_text</div>";
		}
		
		$output .=<<<EOF
			<div class='table_footer'>
				$paging_links
			</div>
		</div class='table'>
EOF;

		return $output;
	
	}
	
	function _paging($result_set) {
		
		global $http_path;
		global $base_url;
		global $per_page;
		global $show_pages;
		
		$page = d($_REQUEST['page'], 1);
		$action = $_REQUEST['action'];		
		$start = 0;
		$end = count($result_set)-1;
		$paging_links = '';
		$tmp_query_string = "?";
		
		// cycle thru the query string key/value pairs and rebuild it without the page
		foreach ($_GET as $k=>$v) {
			if ($k != 'page') $tmp_query_string .= $k . '=' . $v . '&';
		}
		
		if (count($result_set) > $per_page) {
			
			$tmp_base_url = $http_path . "admin.php" . $tmp_query_string;
			$total_pages = ceil(count($result_set) / $per_page);
			$start = ($page-1) * $per_page;
			$end = (count($result_set) > ($start+$per_page) ? ($start+$per_page) : count($result_set));
			$start_page = (($page-$show_pages) < 1 ? 1 : $page-$show_pages);
			$end_page = (($page+$show_pages) > $total_pages ? 
				$total_pages :
				$page + $show_pages);
			
			
			$paging_links .= "<div class='paging'><b>Page:</b>  ";
			
			if ($start_page > 1)
				$paging_links .= "<a href='" . $tmp_base_url . "page=1'>1</a> ... ";
			
			for ($p = $start_page; $p < $page; $p++)
				$paging_links .= "<a href='" . $tmp_base_url . "page=$p'>$p</a> ";
			
			$paging_links .= "<b>$page</b> ";
			
			for ($p = $page+1; $p <= $end_page; $p++)
				$paging_links .= "<a href='" . $tmp_base_url . "page=$p'>$p</a> ";

			if ($end_page < $total_pages)
				$paging_links .= " ... <a href='" . $tmp_base_url . "page=$total_pages'>$total_pages</a>";

			
			$paging_links .= "</div>";
			
		}
		
		return array($paging_links, $start, $end);
			
	}
	
	function set_return_url() {
		$_SESSION['return_url'] = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/')+1);
	}
	
	function visit_return_url($default='admin.php') {
		if (!$_SESSION['return_url']) {
			header("Location: $default");
		} else {
			$url = $_SESSION['return_url'];
			unset($_SESSION['return_url']);
			header("Location: $url");
		}
	}
	
?>
