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
					<li><a href="#how_it_works">How it Works</a></li>
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
				<p>ShortUr is a utility that allows long, convoluted URLs to be accessed by short, meaningful URLs on your site.</p>
			</div>
		</div>
		
		
		<a name='requirements'>
		<div class='table'>
		
			<div class='table_header'>Requirements</div>
			<div class='line_item'>
				ShortUr requires PHP, Apache, mod_rewrite and MySQL.
			</div>
		
		</div>
		
		<a name='how_it_works'>
		<div class='table'>
			<div class='table_header'>How it Works</div>
			<div class='line_item'>
				A typical ShortUr session works as follows:
				<ol>
					<li>A request is made to Apache for a page that doesn't exist.</li>
					<li>Apache handles the request with the page specified in the 'ErrorDocument 404' directive, which is set to the ShortUr index script.</li>
					<li>The .htaccess file parses the request path into a query string and feeds it to Shortur's index.php file.</li>
					<li>If ShortUr finds a match for the request path, it forwards the client to the target URL.</li>
					<li>If ShortUr does not find a match, it generates a generic 404 page or the custom external 404 page.</li>
				</ol>
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
							<li><b>External 404 Page:</b> If ShortUr is asked for, but cannot find a match for a URL, it will spit out a generic "Page Not Found" message.  Setting this URL will forward all failed requests to this URL.</li>
						</ul>
					</li>
					<li>
					 	Alter your Apache configuration file, assigning the ShortUr index.php file to 'ErrorDocument 404'.<br/>
					 	
						<pre>ErrorDocument 404 /shortur/index.php</pre>
					
					</li>
					<li>Delete 'install.php'</li>
					<li>Change the file permissions so that 'config.php' is not writeable by the web server.</li>
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
				<ol>
					<li>Install ShortUr using the <a href='#installation'>instructions above</a>.</li>
					<li>Run http://yourdomain.com/path/to/shortur/migrate_from_shorty.php</li>
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
