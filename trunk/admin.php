<?php

	require_once('admin.inc.php');

	// authorize the user
	auth();

	// initialize the data array
	$data = array();
	$data['errors'] = $data['messages'] = array();

	// check for the existence of the install file and alert the admin to it
	if (file_exists('install.php')) {
		$data['super_errors'][] = "The file 'install.php' still exists in your directory structure.  Delete the file to secure ShortUr from malicious users.";
	}
	
	// see if 'config.php' is writeable by the current process
	if (is_writeable('config.php')) {
		$wwwuser = `whoami`;
		$data['super_errors'][] = "The file 'config.php' is writeable by the web server user ($wwwuser).  Edit the file permissions so that it is not writeable.";
	}
	
	switch ($_REQUEST['action']) {
	
		case 'add':

			$data['tab'] = 'add';

			if ($_REQUEST['submit']) {
				
				// clean up the input
				$_REQUEST['short_url'] = trim($_REQUEST['short_url']);
				$_REQUEST['short_url'] = preg_replace("/^(\/*)/", "", $_REQUEST['short_url']);
				$_REQUEST['short_url'] = preg_replace("/(\/*)$/", "", $_REQUEST['short_url']);			
				$_REQUEST['target_url'] = trim($_REQUEST['target_url']);
				
				// make sure that the short url doesn't exist in the database
				$short_urls = q("select * from entries where short_url = '" .
					s(trim($_REQUEST['short_url'])) . "'");
				
				if ($short_urls) {
					$data['errors'][] = 
						"The short URL '$_REQUEST[short_url]' is already set up as a short url.";
				}
				
				// make sure that the target URL is valid
				/*
				if (!validate_target($_REQUEST['target_url'])) {
					$data['errors'][] = 
						"The target URL is not a valid website.  Please check the target URL and try again.";
				}	
				*/
				
				if (!$data['errors']) {
					q("insert into entries(user_id, target, short_url) values (" . 
						s($_SESSION[shortur_user_id]) . ", ' " . s($_REQUEST[target_url]) . "', '" . 
						s($_REQUEST[short_url]) . "')");
					header("Location: admin.php");
				} 
	
			}
	
			// add short url form
			$data['content'] .= short_url_form('add', $_REQUEST['short_url'], $_REQUEST['target_url']);

			template($data);
			break;

		case 'delete':

			$data['tab'] = 'add';
			
			list($short_url) = q("select * from entries where id = " . s($_REQUEST[id]));
			
			// make sure this short url exists
			if (!$short_url)
				$data['errors'][] = "This short url does not exist.";
				
			// make sure this user is the owner of this url
			if ($short_url->user_id != $_SESSION['shortur_user_id'])
				$data['errors'] = "You are not the owner of this short url.";
				
			if (!$data['errors']) {
				
				if ($_REQUEST['submit']) {
					
					q("delete from entries where id = " . s($_REQUEST[id]));
					header("Location: admin.php");
					
				} else {
					
					$data['messages'][] =<<<EOF
					This will permanently delete the mapping of <br/>
						$domain$short_url->short_url <br/> 
						to <br/>
						$short_url->target <br/><br/>
						
						<b>This action cannot be undone</b> <br/><br/>
						
						<form action='admin.php' method='post'>
							<input type='hidden' name='action' value='delete'>
							<input type='hidden' name='id' value='$short_url->id'>
							<input type='button' value='Go Back' onClick='history.go(-1);' />
							<input type='submit' name='submit' value='Delete Short Url' />
						</form>
EOF;
				
				}
			}
			
			template($data);
			break;
			
		case 'search': 
		
			$data['tab'] = 'search';
			$data['content'] = search_form($_REQUEST['query']);
			
			if ($_REQUEST['submit']) {
				
				if (!$_REQUEST['query'])
					$data['errors'][] = "No query specified.";
					
				if (!$data['errors']) {
				
					$sql = "select * from entries where (target like '%" . s($_REQUEST['query']) . "%' or " .
						"short_url like '%" . s($_REQUEST['query']) . "%')";
					
					if (!validate_admin_user()) 
						$sql .= ' and user_id = ' . $_SESSION[shortur_user_id];
					
					// debug($sql);
					
					$results = q($sql);
					
					$data['content'] .= _display_short_urls($results, "Search results for '" . 
						$_REQUEST['query'] . "'", "No results match your search.");
					
				}
					
			}
			
			template($data);
			break;
			
		case 'edit':

			$data['tab'] = 'add';

			// clean up the input
			$_REQUEST['short_url'] = trim($_REQUEST['short_url']);
			$_REQUEST['short_url'] = preg_replace("/^(\/*)/", "", $_REQUEST['short_url']);
			$_REQUEST['short_url'] = preg_replace("/(\/*)$/", "", $_REQUEST['short_url']);			
			$_REQUEST['target_url'] = trim($_REQUEST['target_url']);
			
			// check that an incoming ID is specified
			if (!$_REQUEST['id'])
				$data['errors'][] = "No incoming id specified.";
			
			list($short_url) = q("select * from entries where id = " . s($_REQUEST[id]));
			
			// make sure this short url exists
			if (!$short_url)
				$data['errors'][] = "This short url does not exist.";
				
			// make sure this user is the owner of this url or is an admin
			if (($short_url->user_id != $_SESSION['shortur_user_id']) && !validate_admin_user()) {
			
				$data['errors'][] = "You are not the owner of this short url.";
				template($data);
				
			}
			
			if (!$data['errors']) {
				
				if ($_REQUEST['submit']) {
					
					// make sure that the short url doesn't exist in the database
					$short_urls = q("select * from entries where short_url = '" .
						s(trim($_REQUEST['short_url'])) . "' and id != " . s($_REQUEST[id]));
					
					if ($short_urls) {
						$data['errors'][] = 
							"The short URL '$_REQUEST[short_url]' is already set up as a short url.";
					}

					// make sure that the target URL is valid
					/*
					if (!validate_target($_REQUEST['target_url'])) {
						$data['errors'][] = 
							"The target URL is not a valid website.  Please check the target URL and try again.";
					}						
					*/
				
					if (!$data['errors']) {
						q("update entries set target = '" . s($_REQUEST[target_url]) . "', short_url = '" . 
							s($_REQUEST[short_url]) . "' where id = " . s($_REQUEST[id]));
						header("Location: admin.php");
					}
				}
			}
			
			$data['content'] = short_url_form('edit', 
				($_REQUEST['short_url'] ? $_REQUEST['short_url'] : $short_url->short_url),
				($_REQUEST['target_url'] ? $_REQUEST['target_url'] : $short_url->target),
				$_REQUEST['id']);
			template($data);
			break;

		case 'admin': 
			
			$data['tab'] = 'admin';
			$data['content'] = users();
			template($data);
			break;
		
		case 'add_user':
			
			$data['tab'] = 'add_user';
			
			// make sure that the current user is authorized to admin
			if (!validate_admin_user()) {
			
				$data['errors'][] = "You are not authorized to make these changes.";
				template($data);
			
			}
		
			if ($_REQUEST['submit']) {
				
				$_REQUEST['username'] = trim($_REQUEST['username']);
				
				if (!$_REQUEST['username'])
					$data['errors'][] = "You must enter a username";
				else {
					list($count) = q("select count(*) as n from users where username = '" . 
						s($_REQUEST[username]) . "'");
					if ($count->n > 0)
						$data['errors'][] = "The username '$_REQUEST[username]' is already in use.";		
				}
				
				if (!$_REQUEST['password'])
					$data['errors'][] = "You must enter a password";
					
				list($count) = q("select count(*) as n from users where username = '" . 
					s($_REQUEST[username]) . "'");

				if (!$data['errors']) {
					
					$_REQUEST['admin'] = ($_REQUEST['admin'] ? $_REQUEST['admin'] : 0);
					$_REQUEST['password'] = md5($_REQUEST['password']);
					q("insert into users (username, password, admin) values ('" . s($_REQUEST[username]) . 
						"', '" . s($_REQUEST[password]) . "', " . s($_REQUEST[admin]) . ")");
					header("Location: admin.php?action=admin");
				}
			}			
			
			$data['content'] = users_form('add_user', $_REQUEST['username'], $_REQUEST['admin']);
			template($data);
			break;
		
		case 'edit_user': 
		
			global $cookie_name;
			$data['tab'] = 'admin';

			// make sure that the current user is authorized to admin
			// also check to make sure that we're not editing the root user
			if (!validate_admin_user() || $_REQUEST['id'] == 1) {
				$data['errors'][] = "You are not authorized to make these changes.";			
				template($data);
				break;
			}
			
			// make sure the user in question exists
			list($user) = q("select * from users where id = " . s($_REQUEST[id]));
			
			if (!$user) 
				$data['errors'][] = "The specifid user does not exist.";
		
			// see if a user is editing themselves.  let them know they will be logged out
			// when they completed
			if ($user->id == $_SESSION['shortur_user_id'])
				$data['messages'][] = 
					"You are editing your own account.  You will be logged out when you are finished.";
				
		
			if ($_REQUEST['submit']) {
				
				$_REQUEST['username'] = trim($_REQUEST['username']);
				
				if (!$_REQUEST['username'])
					$data['errors'][] = "You must enter a username";
				else {
					list($count) = q("select id from users where username = '" . s($_REQUEST[username]) . 
						"'");
					if ($count->id != $_REQUEST['id'] && $count->id)
						$data['errors'][] = "The username '$_REQUEST[username]' is already in use.";				
				}
					
				if (!$data['errors']) {
				
					$_REQUEST['admin'] = ($_REQUEST['admin'] ? $_REQUEST['admin'] : 0);
					q("update users set username = '" . s($_REQUEST[username]) . "', admin = " . 
						s($_REQUEST[admin]) . " where id = " . s($_REQUEST[id]));
					
					// if they specified a password, update it
					if ($_REQUEST['password'])
						q("update users set password = '" . md5($_REQUEST['password']) . "' where id = " . 
							s($_REQUEST[id]));
					
					
					if ($user->id == $_SESSION['shortur_user_id']) {
						$_SESSION['shortur_user_id'] = $_SESSION['shortur_username'] = null;
						setcookie($cookie_name, null);
						header("Location: admin.php");
					} else {
						header("Location: admin.php?action=admin");
					}
				}
			}

			$data['content'] = users_form('edit_user',
				($_REQUEST['username'] ? $_REQUEST['username'] : $user->username),
				($_REQUEST['admin'] ? $_REQUEST['admin'] : $user->admin),
				$_REQUEST['id']);
		
			template($data);
			break;
			
		case 'delete_user':
		
			// make sure that the current user is authorized to admin
			// also check to make sure that we're not removing the root user
			// also check to make sure that we're not removing ourselves
			if (!validate_admin_user() || $_REQUEST['id'] == 1 || 
				$_REQUEST['id'] == $_SESSION['shortur_user_id']) {
				
				$data['errors'][] = "You are not authorized to make these changes.";			
				template($data);
				break;
			}
			
			if (!$_REQUEST['id']) {
				$data['errors'][] = "No user ID specified";
				template($data);
				break;
			}
	
			// is the id valid 
			list($user) = q("select * from users where id = " . s($_REQUEST[id]));
			if (!$user->id) {
				$data['errors'][] = "No user exists with ID $_REQUEST[id]";						
				template($data);
				break;
			}

			// find all the URLS that pertain to this user
			$urls = q("select * from entries where user_id = $user->id");
			if (count($urls))
				$url_count_display = "This will transfer ownership of their " .
					count($urls) . " URL" . (count($urls) > 1 ? 's ' : ' ') . 
					"to the user <b>admin</b>.<br/><br/>";
			
			if ($_REQUEST['submit']) {
			
				if (count($urls)) {
					
					foreach ($urls as $u) {
						q("update entries set user_id = 1 where id = $u->id");
					}
				}
				
				q("delete from users where id = $user->id");
				header("Location: admin.php?action=admin");
				
			}
			
			$data['messages'][] =<<<EOF
				You are deleting user <b>$user->username</b><br/><br/>
				$url_count_display
				This action cannot be undone.<br/><br/>
				<form action='admin.php' method='post'>
					<input type='hidden' name='action' value='delete_user'>
					<input type='hidden' name='id' value='$user->id'>
					<input type='button' value='Go Back' onClick='history.go(-1);' />
					<input type='submit' name='submit' value='Delete User' />
				</form>
EOF;
			
			
			template($data);
			break;

		case 'all_urls':
		
			$data['tab'] = 'all_urls';
			
			// are you authorized
			if (!validate_admin_user()) {
				$data['errors'][] = "You are not authorized to view all short URLs.";
				template($data);
				break;
			}
			
			$data['content'] = short_urls(true);
			
			template($data);
			break;
	
		case 'logout':
			
			setcookie($cookie_name, null);
			$_SESSION['shortur_username'] = $_SESSION['shortur_user_id'] = null;
			header("Location: admin.php");
			break;
	
		default:

			$data['tab'] = 'main';
			$data['content'] .= short_urls(false);
			
			template($data);
			break;
	
	} // end switch

?>
