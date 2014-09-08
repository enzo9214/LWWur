<?
error_reporting(E_ALL ^ E_NOTICE);
function error_handler($number, $string, $file, $line)
	{
		if(strpos($file,"getid3")===false||strpos($file,"getid3")===""||strpos($file,"getid3")===0)
		error_log("Error ($number) on line $line in file $file. The error was \"$string\"\n", 1, "your_email_address");
	}
ini_set('display_errors',0);
set_error_handler("error_handler");
?>