<?php

	require_once('default.inc.php');
	
	if ($_REQUEST['submit']) {
	
		if (!$_REQUEST['in'])
			$data['errors'][] = "You must enter a username or email address.";
		else if (trim($_REQUEST['in']) == 'admin')
			$data['errors'][] = "The password for the admin account cannot be reset with this form.";
		else {
		
			$u = q("select * from users where username = '" . s($_REQUEST['in']) . "'");
			
			if (!$u[0]->id)
				$n = q("select * from users where email = '" . s($_REQUEST['in']) . "'");
				
			if (!$u[0]->id)
				$data['errors'][] = "No users with matching username or email address found.";
			else {
				
				// debug($u[0]->email);
				
				$new_password = random_password();
				$new_password_db = md5($new_password);
				$id = $u[0]->id;
				q("update users set password = '$new_password_db' where id = $id");
				
				$message = <<<EOF
A request to reset your ShortUr password has been received.  Your new password is:

$new_password

You can now log in at $http_path/admin.php with your new password.

EOF;

				$headers = 'From: ' . $from_email . "\r\n" .
					'Reply-To: ' . $from_email . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
				$subject = 'ShortUr: Password reset for ' . $u[0]->username;
				
				mail($u[0]->email, $subject, $message, $headers) or die('There is a problem with mail configuration on this server.  The email was not sent.');
				
				$data['content'] = <<<EOF
		
		<div class='table'>
			
			<div class='table_header'>Password Reset</div>
			<div class='line_item'>Your new password has been emailed to you.</div>
			
		</div>
		
EOF;
				
				template($data);
				
			}
		}
	
		
	
		
	
	
	}

	$data['content'] =<<<EOF
	
		<form name='login' action='forgot.php' method='post'>
			
			<div class='table'>
			
				<div class='table_header'>Password Reset</div>
				
				<div class='line_item'>
					Enter your username or email address below and a new password will be sent to your email address.
				</div>
				
				<div class='line_item'>
					<b>Username/Email:</b> <input type='text' name='in' id='in' />
				</div>
			
				<div class='line_item'>
					<input type='submit' name='submit' value='Reset Password' />
				</div>
				
			</div>
		</form>		
			
EOF;
		
	template($data);
	
?>