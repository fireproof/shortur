<?php

	require_once('Shortur.php');
	

	if (!Shortur($_REQUEST['q'])) {
		if ($external_404_page) {
		
			header("Location: $external_404_page");
			exit;
			
		} else {
			
			$data = array();
			
			$request_url = $domain . $_SERVER['REQUEST_URI'];
			
			$data['content'] =<<<EOF
				<div class='table'>
					<div class='table_header_error'>
						Page Not Found!
					</div>
					<div class='line_item' align='center'>
						<p>The page you requested:</p>
						<p><b>$request_url</b></p>
						<p>is not a valid URL</p>
					</div>
				</div>
EOF;
			template($data, false);
		}
	}
		
?>
