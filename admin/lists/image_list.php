<?php
	include("../../include/start.php");
	
	$output = "var tinyMCEImageList = new Array(";
	
	$result = mysql_query("SELECT * FROM images WHERE isthumb=0 AND sidenav=0");
	if ($result)
	{
		while ($row = mysql_fetch_array($result))
		{
			if ($row['caption'] == "")
				$append = $row['path'];
			else
				$append = $row['caption'];
			
			if (strlen($append) > 35)
				$append = substr($append, 0, 30) . "...";
			
			$output .= '["' . $append . '", "media/' . $row['path'] . '"], ';
		}
		
		$output = substr($output, 0, -2);
	}
	
	$output .= ");";
	echo $output;
?>
