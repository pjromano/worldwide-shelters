<?php
	header("Content-Type: image/jpeg");
	
	header("Pragma-Directive: no-cache");
	header("Cache-Directive: no-cache");
	header("Cache-control: no-cache");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	readfile("preview.jpg");
?>
