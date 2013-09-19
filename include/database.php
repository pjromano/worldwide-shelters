<?php
	function db_connect($server, $user, $pass, $db)
	{
		mysql_connect($server, $user, $pass) or die(mysql_error());
		mysql_select_db($db) or die(mysql_error());
	}
?>

