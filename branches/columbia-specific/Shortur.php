<?php

	require_once('default.inc.php');
	
	function Shortur($q) {
	
		global $http_path;
	
		// if there's no query, pass the user on to the admin site
		if (!$q) {
			header("Location: " . $http_path . "admin.php");
		}
	
		// get the short url from the query string, clean it up and find it in 
		// the database
		$q = preg_replace("/\/$/", "", $q);
		$q = preg_replace("/^\//", "", $q);
		
		list($item) = q('select * from entries where short_url = "' . $q . '"');
		
		if ($item->id) {
		
			// forward to the target
			header("Location: $item->target");
			
		} else {
			
			return false;
	
		}
	
	}
				
?>
