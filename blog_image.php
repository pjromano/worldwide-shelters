<?php
	include("include/start.php");
	
	$output = "var tinyMCEImageList = new Array(";
	
	if (is_dir("media/blog"))
		$files = scandir("media/blog");
	
	if ($files)
	{
		foreach ($files as $file)
		{
			try {
				$imagetest = new Imagick("media/blog/" . $file);
				$imagetest->destroy();
				$append = $file;
			} catch (ImagickException $e) {
				$append = false;
			}
			
			if ($append)
			{
				if (strlen($append) > 35)
					$append = substr($append, 0, 30) . "...";
				$output .= '["' . $append . '", "media/blog/' . $file . '"], ';
			}
		}
		
		$output = substr($output, 0, -2);
	}
	
	$output .= ");";
	echo $output;
?>
