<?php
	include("functions/user.php");
	include("functions/page.php");
	include("functions/image.php");
	include("functions/blog.php");
	
	// Return an error string corresponding to the given error code
	// Returns an empty string if there is no error (0)
	function getErrorString($errorcode)
	{
		if ($errorcode === ERROR_DB)
			return "An internal database error occurred.<br>";
		else if ($errorcode === ERROR_ADDPAGE_NONAME)
			return "You must enter a title for the page.<br>";
		else if ($errorcode === ERROR_ADDPAGE_INUSE)
			return "The section or page link name (URL) you entered already exists. Link names must be unique between pages and sections.<br>";
		else if ($errorcode === ERROR_ADDPAGE_BADLINK)
			return "You must provide a valid URL.<br>";
		else if ($errorcode === ERROR_NOFILE)
			return "The file requested does not exist.<br>";
		else if ($errorcode === ERROR_WRITEFILE)
			return "The file could not be opened for writing.<br>";
		else if ($errorcode === ERROR_OPENIMAGE)
			return "The image file could not be opened.<br>";
		
		else if ($errorcode === ERROR_ADDIMG_NOFILE)
			return "The file name to be added to the database does not exist on the server.<br>";
		else if ($errorcode === ERROR_ADDIMG_EXISTS)
			return "A file with the given name already exists on the server.<br>";
		else if ($errorcode === ERROR_ADDIMG_RESIZE)
			return "The image failed to resize.<br>";
		else if ($errorcode === ERROR_ADDIMG_MAKETHUMB)
			return "A thumbnail version of the image failed to be created.<br>";
		else if ($errorcode === ERROR_ADDIMG_APPLYEFFECT)
			return "The image effect failed to be applied.<br>";
		else if ($errorcode === ERROR_ADDIMG_TOOSMALL)
			return "The image effect could not be applied because the provided image was too small. The image must be at least 275 x 575 pixels.<br>";
		
		else if ($errorcode === ERROR_ADDGALLERY_NONAME)
			return "You must enter a name for the gallery.<br>";
		else if ($errorcode === ERROR_ADDGALLERY_INUSE)
			return "The gallery name you entered already exists. Gallery names must be unique.<br>";
		else if ($errorcode === ERROR_DELETEFILE)
			return "Failed to physically delete the given file.<br>";
		else if ($errorcode === ERROR_DELETEIMAGES)
			return "The images associated with the gallery to be deleted could not be removed.<br>";
		
		return "";
	}
?>
