<?php
define('GETID3_DB_HOST','localhost');
define('GETID3_DB_USER','user_name');
define('GETID3_DB_PASS','password');
define('GETID3_DB_DB','db_name');
define('GETID3_DB_TABLE','mp_id3_tags');
define('GETINDEX_DB_TABLE','mp_id3_index');
define('GETSUM_DB_TABLE','mp_admin_summary');
define('GETERR_DB_TABLE', 'mp_admin_errors');

if (!@mysql_connect(GETID3_DB_HOST, GETID3_DB_USER, GETID3_DB_PASS)) 
	{
		die('Could not connect to MySQL host: <blockquote style="background-color: #FF9933; padding: 10px;">'.mysql_error().'</blockquote>');
	}
if (!@mysql_select_db(GETID3_DB_DB)) 
	{
		die('Could not select database: <blockquote style="background-color: #FF9933; padding: 10px;">'.mysql_error().'</blockquote>');
	}
if (!@include_once('getid3/getid3.php'))
	{
	die('Cannot open '.realpath('getid3/getid3.php'));
	}
?>