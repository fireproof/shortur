<?php

	require_once('Shortur.php');
	
	if (!Shortur($_REQUEST['q'])) {
		print "FAIL";
	}
		
?>
