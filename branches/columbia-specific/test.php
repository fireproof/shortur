<?php
	// this file exists solely to check mod_rewrite
	// bpz 2-18-2008

	if ($_REQUEST['check'] == 'enabled')
		echo 'enabled';
	else
		echo 'disabled';
?>
