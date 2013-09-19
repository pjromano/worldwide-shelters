<?php
	include("../include/start.php");
	
	$loginflag = LOGIN_NULL;
	
	if ($_POST['authenticate'])
	{
		$loginflag = authenticateUser($_POST['username'], $_POST['pass']);
		if ($loginflag == LOGIN_SUCCESS)
		{
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['priv'] = getUserPrivileges($_SESSION['username']);
		}
	}
	
	if ($_GET['action'] == "logout")
	{
		$_SESSION['username'] = "Anonymous";
		$_SESSION['priv'] = PRIV_NONE;
	}
	
	function printIndex()
	{
		echo
"		<div class=\"text_left\">
			From the backend, you can edit the content of the site's pages, create and modify sections of the website,
			upload and manage images, and moderate comments on the blog.
		</div>\n";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<base href="<?php echo SITE_BASE; ?>">
	<title><?php echo SITE_TITLE; ?> - Administration</title>
	<link rel="stylesheet" href="admin/adminstyle.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="admin/script.js"></script>
</head>

<body>
	<div class="head1"><?php echo SITE_TITLE; ?></div>
<?php
	if ($_SESSION['priv'] != PRIV_NONE)
	{ ?>
	<div class="text_center"><a href="<?php echo SITE_BASE; ?>">View Site</a></div>
<?php
	}
	// Top Menu
	if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
	{ ?>
	<div class="text_center">
		<ul class="menu">
			<li class="menu_current"><a href="admin/index.php">Admin Index</a></li>
			<li class="menu"><a href="admin/content.php">Content</a></li>
			<li class="menu"><a href="admin/media.php">Media</a></li>
			<li class="menu"><a href="admin/blog.php">Blog</a></li>
			<li class="menu"><a href="admin/manageusers.php">Users</a></li>
			<li class="menu"><a href="admin/index.php?action=logout">Log Out</a></li>
		</ul>
	</div>
<?php
	}
	else if (($_SESSION['priv'] & PRIV_MOD) == PRIV_MOD || ($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
	{ ?>
	<div class="text_center">
		<ul class="menu">
			<li class="menu_current"><a href="admin/index.php">Admin Index</a></li>
			<li class="menu"><a href="admin/blog.php">Blog</a></li>
			<li class="menu"><a href="admin/index.php?action=logout">Log Out</a></li>
		</ul>
	</div>
<?php
	}
	
	// Page Title
	if ($_GET['action'] == "login")
	{ ?>
	<div class="head2">Admin Login</div>
<?php }
	else
	{ ?>
	<div class="head2">Administration</div>
<?php } ?>
	<div id="border">
<?php
	// 404 error page
	if ($_GET['page'] == "404")
	{ ?>
		<div class="message_error">
			Sorry, but the page you are looking for cannot be found. Please return to the <a href="admin/index.php">index</a>.
		</div>
<?php
	}
	else
	{
		// Action: Log in
		if ($_GET['action'] == "login")
		{
			if ($_POST['authenticate'])
			{
				if ($loginflag == LOGIN_SUCCESS)
				{ ?>
		<div class="message">
			You have been successfully logged in.
		</div>
<?php				if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
						printIndex();
					else if (($_SESSION['priv'] & PRIV_MOD) == PRIV_MOD || ($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
					{ ?>
		<div class="text_center">
			You can moderate the blog if you visit the above link named "Blog".
		</div>
<?php				}
					else
					{ ?>
		<div class="text_center">
			You do not have sufficient privileges to administer the website. Please select one of the following:<br>
			<a href="home">Home Page</a><br>
			<a href="admin/index.php?action=logout">Log Out</a><br>
		</div>
<?php				}
				}
				if ($loginflag == LOGIN_USERFAIL)
				{ ?>
		<div class="text_center">
			The username you entered does not exist. Please try again.<br>
		</div>
<?php			}
				else if ($loginflag == LOGIN_PASSFAIL)
				{ ?>
		<div class="text_center">
			The password you entered does not match. Please try again.<br>
		</div>
<?php			}
				if ($loginflag == LOGIN_USERFAIL || $loginflag == LOGIN_PASSFAIL)
				{ ?>
		<div class="text_center">
			<form action="admin/index.php?action=login" method="post">
				<input type="hidden" name="authenticate" value="true">
				<table class="tableedit" align="center">
					<tr>
						<td class="left">Username</td>
						<td class="right"><input id="name_focus" type="text" name="username" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Password</td>
						<td class="right"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Submit"></td>
					</tr>
				</table>
			</form>
		</div>
<?php			}
			}
			else if ($_SESSION['priv'] == PRIV_NONE)
			{ ?>
		<div class="text_center">
			<form action="admin/index.php?action=login" method="post">
				<input type="hidden" name="authenticate" value="true">
				<table class="tableedit" align="center">
					<tr>
						<td class="left">Username</td>
						<td class="right"><input id="name_focus" type="text" name="username" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Password</td>
						<td class="right"><input type="password" name="pass"></td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Submit"></td>
					</tr>
				</table>
			</form>
		</div>
<?php		}
			else
			{ ?>
		<div class="text_center">
			You are already logged in.<br>
			Please return to the <a href="admin/index.php">admin index</a>.<br>
		</div>
<?php 	}
		}
		else if ($_GET['action'] == "logout")
		{
			if ($_SESSION['priv'] == PRIV_NONE)
			{ ?>
		<div class="text_center">
			You have been successfully logged out.<br>
			Either return to the <a href="admin/index.php">Admin Index</a> or the <a href="index.php">home page</a>.<br>
		</div>
<?php		}
			else
			{ ?>
		<div class="text_center">
			Log out failed.<br>
			Please return to the <a href="admin/index.php">Admin Index</a>.<br>
		</div>
<?php		}
		}
	
		// Action: normal
		else
		{
			if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
				printIndex();
			else if (($_SESSION['priv'] & PRIV_MOD) == PRIV_MOD || ($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
			{ ?>
		<div class="text_center">
			You can moderate the blog if you visit the above link named "Blog".
		</div>
<?php		}
			else
			{ ?>
		<div class="text_center">
			You do not have privileges to access this section of the website.<br>
<?php if ($_SESSION['priv'] == PRIV_NONE)
		{ ?>
		Please either <a href="admin/index.php?action=login">log in</a> or return to the <a href="index.php">home page</a>.
<?php } ?>
		</div>
<?php		}
		} ?>
	</div> <!-- <div class="border"> -->
<?php
	}
	if ($_SESSION['priv'] != PRIV_NONE)
	{ ?>
	<div class="text_center">Welcome, <em><?php echo $_SESSION['username']; ?>.</em></div>
<?php	} ?>
</body>

</html>
