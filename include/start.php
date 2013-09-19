<?php
	session_start();
	putenv("TZ=US/Eastern");
	
	// String constants
	define("SITE_TITLE", "Worldwide Shelters");
	define("SITE_BASE", "http://www.worldwideshelters.org/");
	define("SITE_TOPDIRECTORY", "/home/shelters/public_html/");
	define("SITE_MEDIADIRECTORY", SITE_TOPDIRECTORY . "media/");
	
	// User Privilege flags
	define("PRIV_NONE", 0);		// Anonymous user
	define("PRIV_BLOG", 1);		// Post on blogs
	define("PRIV_MOD", 2);		// Blog moderator (allow anonymous comments to be visible)
	define("PRIV_ADMIN", 4);	// Site admin (log into back-end)
	
	// Register user flags
	define("REGISTER_NULL", 0);
	define("REGISTER_SUCCESS", 1);
	define("REGISTER_PASSEMPTY", 2);
	define("REGISTER_PASSNOMATCH", 4);
	define("REGISTER_USEREXISTS", 8);
	define("REGISTER_AUTHFAIL", 16);
	define("REGISTER_DATAFAIL", 32);
	
	// Login flags
	define("LOGIN_NULL", 0);
	define("LOGIN_SUCCESS", 1);
	define("LOGIN_PASSFAIL", 2);
	define("LOGIN_USERFAIL", 4);
	
	// Section Type flags
	define("SECTIONTYPE_HOME", 0);
	define("SECTIONTYPE_SUBPAGE", 1);
	define("SECTIONTYPE_LINK", 2);
	define("SECTIONTYPE_BLOG", 3);
	define("SECTIONTYPE_DONATE", 4);
	
	// Error Codes
	define("ERROR_DB", -1);
	define("ERROR_ADDPAGE_NONAME", -2);
	define("ERROR_ADDPAGE_INUSE", -3);
	define("ERROR_ADDPAGE_BADLINK", -4);
	define("ERROR_NOFILE", -5);
	define("ERROR_WRITEFILE", -6);
	define("ERROR_OPENIMAGE", -7);
	
	define("ERROR_ADDIMG_NOFILE", -10);
	define("ERROR_ADDIMG_EXISTS", -11);
	define("ERROR_ADDIMG_RESIZE", -12);
	define("ERROR_ADDIMG_MAKETHUMB", -13);
	define("ERROR_ADDIMG_APPLYEFFECT", -14);
	define("ERROR_ADDIMG_TOOSMALL", -15);
	define("ERROR_ADDGALLERY_NONAME", -16);
	define("ERROR_ADDGALLERY_INUSE", -17);
	define("ERROR_DELETEFILE", -18);
	define("ERROR_DELETEIMAGES", -19);			// When deleting a gallery, failed to delete all of the associated images
	
	include("database.php");
	include("functions.php");
	
	db_connect("localhost", "shelters_admin", "40WShelter", "shelters_data");
	
	if (!isset($_SESSION['username']))
	{
		$_SESSION['priv'] = PRIV_NONE;
		$_SESSION['username'] = "Anonymous";
	}
	
	// If requesting index.html or index.php, display home page
	if (!isset($_GET['section'])
			|| $_GET['section'] == "index.php"
			|| $_GET['section'] == "index.html")
		$_GET['section'] = "home";
	
	if ($_GET['page'] == "")
		unset($_GET['page']);
	
	// Temporary redirects for requests from external Kintera Donate page
	// Fix this when the external page has been updated
	switch ($_GET['section'])
	{
		case "shelters.html":
			$_GET['section'] = "transitional_shelters";
			break;
		case "programs.html":
			$_GET['section'] = "projects";
			break;
		case "about.html":
			$_GET['section'] = "about";
			break;
		case "who_we_help.html":
			$_GET['section'] = "who_we_help";
			break;
		case "shelters_in_use.html":
			$_GET['section'] = "shelters_in_use";
			break;
		case "contact.html":
			$_GET['section'] = "contact";
			break;
		default:
			break;
	}
?>
