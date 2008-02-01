<?php

	require_once('default.inc.php');

	$data = array();	

	$data['content'] = <<<EOF

		<div class='table'>
		
			<div class='table_header'>
				Table of Contents
			</div>
			<div class='line_item'>			
				<ol>
					<li><a href="#what_is_shortur">What is ShortUr?</a></li>
					<li><a href="#requirements">Requirements</a></li>
					<li><a href="#installation">Installation</a></li>
					<li><a href="#configuration">Configuration</a></li>
					<li><a href="#migrating_from_shorty">Migrating from Shorty</a></li>
					<li><a href="#credits">Credits</a></li>
				</ol>
			</div>			
		</div class='table'>
	
		<a name='what_is_shortur'>
		<div class='table'>
			<div class='table_header'>What is ShortUr?</div>
			<div class='line_item'>
				ShortUr is a utility that allows long, convoluted URLs to be accessed by short, meaningful URLs on your site.
			</div>
		</div>
		
		
		<a name='requirements'>
		<div class='table'>
		
			<div class='table_header'>Requirements</div>
			<div class='line_item'>
				ShortUr requires PHP, Apache, mod_rewrite and MySQL.
			</div>
		
		</div>
		
		
		<a name='installation'>
		<div class='table'>
			<div class='table_header'>Installation</div>
			<div class='line_item'>
				<ol>
					<li>Download a copy of ShortUr</li>
					<li>Unpack the file and upload the contents to where you want it to reside on your web server.</li>
					<li>Run the install script, located at <em>http://yourdomain.com/path/to/shortur/install.php</em>
						<ul>
							<li><b>Installation Path:</b> The web path where ShortUr will be installed.</li>
							<li><b>Database Host:</b> The host where the ShortUr MySQL database will be located.</li>
							<li><b>Database Username:</b> The username for your MySQL database.</li>
							<li><b>Database Password:</b> The password for your MySQL database.</li>
							<li><b>Database Name:</b> The name of your MySQL database. Your MySQL username and password might not have enough access to create a database.
							<li><b>This database has already been created:</b> Your MySQL username and password might not have enough access permissions to create a database using this installer.  If you get this error message, create the database using whatever method your web host requires first, then run the installler again and check this box.</li>
							<li><b>Domain:</b> The domain ShortUr will be running on.</li>
							<li><b>Admin Password:</b> The initial passsword for user 'admin'.</li>
						</ul>
					</li>
					<li>Alter your apache installation to point the error document to the ShortUr directory. Where the Apache configuration file is located will differ from system to system.  Change the 'ErrorDocument 404' file to the ShortUr index.php file.</li>
					<li>Restart Apache</li>
					<li>Login to the admin site at http://yourdomain.com/path/to/shortur/</li>
				</ol>
			</div>
		</div>
		
		<a name='configuration'>
		<div class='table'>
			<div class='table_header'>Configuration</div>
			<div class='line_item'>
				All the configuration information is kept in config.php.  If you need to make changes to your ShortUr installation, edit config.php.
			</div>
		</div>
		
		<a name='migrating_from_shorty'>
		<div class='table'>
			<div class='table_header'>Migrating from Shorty</div>
			<div class='line_item'>
				ShortUr was written to replace Shorty.  The file migrate_from_shorty.php will accomplish this with ease.
			</div>
		</div>
		
		<a name='credits'>
		<div class='table'>
			<div class='table_header'>Credits</div>
			<div class='line_item'>
				ShortUr was written by Bryan Zera (<a href='mailto:bzera@colum.edu'>bzera@colum.edu</a>) with design help from Ivan Brunetti (<a href='mailto:ibrunetti@colum.edu'>ibrunetti@colum.edu</a>) and the generous support of <a href='http://www.colum.edu/'>Columbia College Chicago</a>.
			</div>
		</div>
EOF;
		
		template($data);
?>
