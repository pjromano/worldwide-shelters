<?php
	include("../include/start.php");
	
	$flag = REGISTER_NULL;
	
	if ($_POST['register'] == true)
	{
		$register = REGISTER_SUCCESS;
		
		if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
		{
			if (isUsernameTaken($_POST['username']))
				$register = REGISTER_USEREXISTS;
			else
			{
				if ($_POST['pass'] == "")
					$register = REGISTER_PASSEMPTY;
				else if ($_POST['pass'] == $_POST['passcheck'])
				{
					$privileges = 0;
					if ($_POST['privblog'] == "on")	$privileges = $privileges | PRIV_BLOG;
					if ($_POST['privmod'] == "on")	$privileges = $privileges | PRIV_MOD;
					if ($_POST['privadmin'] == "on") $privileges = $privileges | PRIV_ADMIN;
					
					if (addUser($_POST['username'], $_POST['displayname'], $_POST['pass'], $privileges) == -1)
						$register = REGISTER_DATAFAIL;
				}
				else
					$register = REGISTER_PASSNOMATCH;
			}
		}
		else
			$register = REGISTER_AUTHFAIL;
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<base href="<?php echo SITE_BASE; ?>">
	<title><?php echo SITE_TITLE; ?> - Administration - Manage Users</title>
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
			<li class="menu"><a href="admin/index.php">Admin Index</a></li>
			<li class="menu"><a href="admin/content.php">Content</a></li>
			<li class="menu"><a href="admin/media.php">Media</a></li>
			<li class="menu"><a href="admin/blog.php">Blog</a></li>
			<li class="menu_current"><a href="admin/manageusers.php">Users</a></li>
			<li class="menu"><a href="admin/index.php?action=logout">Log Out</a></li>
		</ul>
	</div>
<?php
	}
	else if (($_SESSION['priv'] & PRIV_MOD) == PRIV_MOD || ($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
	{ ?>
	<div class="text_center">
		<ul class="menu">
			<li class="menu"><a href="admin/index.php">Admin Index</a></li>
			<li class="menu_current"><a href="admin/blog.php">Blog</a></li>
			<li class="menu"><a href="admin/index.php?action=logout">Log Out</a></li>
		</ul>
	</div>
<?php
	}
	
	// Page Title
	if ($_GET['action'] == "pass")
	{ ?>
	<div class="head2">Edit User Password</div>
<?php }
	else if ($_GET['action'] == "priv")
	{ ?>
	<div class="head2">Edit User Privileges</div>
<?php }
	else if ($_GET['action'] == "delete")
	{ ?>
	<div class="head2">Delete User Account</div>
<?php }
	else
	{ ?>
	<div class="head2">Users</div>
<?php } ?>
	<div id="border">
<?php	
	// Privileges: Admin
	if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
	{
		// Action: Edit password
		if ($_GET['action'] == "pass")
		{
			if (isset($_GET['id']))
			{
				if ($_POST['changepass'] == true)
				{
					if ($_POST['pass'] == $_POST['passcheck'])
					{
						if (changePassword($_GET['id'], $_POST['pass']))
						{ ?>
		<div class="message">Successfully changed password.</div>
		<div class="text_center"><a href="admin/manageusers.php">Return</a></div>
<?php					}
						else
						{ ?>
		<div class="message_error">Failed to change password. Please try again.</div>
		<div class="text_center">
			<a href="admin/manageusers.php?action=pass&id=<?php echo $_GET['id']; ?>">Return</a><br>
			<a href="admin/manageusers.php">Cancel</a><br>
		</div>
<?php					}
					}
					else
					{ ?>
		<div class="message_error">The passwords you entered do not match. Please try again.</div>
		<div class="text_center">
			<a href="admin/manageusers.php?action=pass&id=<?php echo $_GET['id']; ?>">Return</a><br>
			<a href="admin/manageusers.php">Cancel</a><br>
		</div>
<?php				}
				}
				else
				{
					$result = mysql_query("SELECT * FROM users WHERE id='" . $_GET['id'] . "'");
					if (!$result)
					{ ?>
		<div class="message_error">
			An error occurred while accessing the database. Please return to the <a href="admin/manageusers.php">User List</a>.
		</div>
<?php				}
					else
					{
						$row = mysql_fetch_array($result);
						?>
		<div class="head_subsection">User <i><?php echo $row['username']; ?></i></div>
		<form method="post" action="admin/manageusers.php?action=pass&id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="changepass" value="true">
			<table style="noborder" align="center">
				<tr>
					<td class="rightalign"><div class="text_right">New Password:</div></td>
					<td><input id="pass_focus" type="password" width="50" name="pass" onKeyPress="return disableEnterKey(event)"></td>
				</tr>
				<tr>
					<td class="rightalign"><div class="text_right">Confirm:</div></td>
					<td><input type="password" width="50" name="passcheck"></td>
				</tr>
				<tr>
					<td class="centeralign" colspan="2"><input type="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
<?php				}
				}
			}
			else
			{ ?>
		<div class="text_center">
			A user ID has not been specified.<br>
			Please return to the <a href="admin/manageusers.php">Manage Users</a> page and select a user.<br>
		</div>
<?php		}
		}
		
		// Action: Delete user
		else if ($_GET['action'] == "delete")
		{
			$result = mysql_query("SELECT * FROM users WHERE id='" . $_GET['id'] . "'");
			if (!$result)
			{ ?>
		<div class="message_error">
			An error occurred while accessing the database. Please return to the <a href="admin/manageusers.php">User List</a>.
		</div>
<?php		}
			else if (mysql_num_rows($result) == 0)
			{ ?>
		<div class="message_error">
			The specified user id does not exist.<br>
			Please return to the <a href="admin/manageusers.php">User List</a>.
		</div>
<?php		}
			else
			{
				$row = mysql_fetch_array($result);
				
				if (strtolower($row['username']) == strtolower($_SESSION['username']))
				{ ?>
		<div class="message_error">
			You cannot delete your own account.<br>
			Please return to the <a href="admin/manageusers.php">User List</a>.
		</div>
<?php			}
				else if (isset($_POST['delete']) == true && $_POST['delete'] == true)
				{
					if (removeUser($_GET['id']))
					{ ?>
		<div class="message">User successfully removed.</div>
		<div class="text_center"><a href="admin/manageusers.php">Return</a></div>
<?php				}
					else
					{ ?>
		<div class="message_error">Failed to remove user. Please try again.</div>
		<div class="text_center">
			<a href="admin/manageusers.php?action=delete&id=<?php echo $_GET['id']; ?>">Try Again</a>
			<a href="admin/manageusers.php">Cancel</a>
		</div>
<?php				}
				}
				else
				{ ?>
		<div class="text_center">Are you sure you want to delete user <em><?php echo $row['username']; ?></em>?<br>
			<form method="post" action="admin/manageusers.php?action=delete&id=<?php echo $_GET['id']; ?>">
				<input type="hidden" name="delete" value="true">
				<input type="submit" value="Yes" class="submit_option">
			</form>
			<form method="post" action="admin/manageusers.php">
				<input type="submit" value="No" class="submit_option">
			</form>
		</div>
<?php			}
			}
		}
		
		// Action: Edit privileges
		else if ($_GET['action'] == "priv")
		{
			if (!isset($_GET['id']))
			{ ?>
		<div class="message_error">A user ID to edit has not been specified.</div>
<?php		}
			else if ($_POST['changepriv'] == true)
			{
				// Update user privileges
				$privileges = 0;
				if ($_POST['privblog'] == "on")	$privileges = $privileges | PRIV_BLOG;
				if ($_POST['privmod'] == "on")	$privileges = $privileges | PRIV_MOD;
				if ($_POST['privadmin'] == "on") $privileges = $privileges | PRIV_ADMIN;
				
				$success = changeUserPrivileges($_GET['id'], $privileges);
				if ($success)
				{ ?>
		<div class="message">The user privileges were successfully updated.</div>
		<div class="text_center"><a href="admin/manageusers.php">Return</a></div>
<?php			}
				else
				{ ?>
		<div class="message_error">An error occurred while updating the user privileges in the database.</div>
		<div class="text_center">
			<a href="admin/manageusers.php?action=priv&id=<?php echo $_GET['id']; ?>">Try Again</a>
			<a href="admin/manageusers.php">Cancel</a>
		</div>
<?php			}
			}
			else
			{
				// Prompt user with form
				$result = mysql_query("SELECT * FROM users WHERE id='" . $_GET['id'] . "'");
				if (!$result || mysql_num_rows($result) == 0)
				{ ?>
		<div class="message_error">An error occurred while accessing the database.</div>
<?php			}
				else
				{
					$user = mysql_fetch_array($result);
					?>
		<div class="head_subsection">User <i><?php echo $user['username']; ?></i></div>
		<form method="post" action="admin/manageusers.php?action=priv&id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="changepriv" value="true">
			<table style="noborder" align="center">
				<tr>
					<td class="rightalign"><div class="text_right">Privileges:</div></td>
					<td>
						<input type="checkbox" name="privblog"<?php if (($user['privileges'] & PRIV_BLOG) == PRIV_BLOG) echo " checked"; ?>>Post on blog<br>
						<input type="checkbox" name="privmod"<?php if (($user['privileges'] & PRIV_MOD) == PRIV_MOD) echo " checked"; ?>>Moderate blog<br>
						<input type="checkbox" name="privadmin"<?php if (($user['privileges'] & PRIV_ADMIN) == PRIV_ADMIN) echo " checked"; ?>>Site administrator<br>
					</td>
				</tr>
				<tr>
					<td class="centeralign" colspan="2"><input type="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
<?php			}
			}
		}
		
		// Action: Edit Display Name
		else if ($_GET['action'] == "display")
		{
			if (!isset($_GET['id']))
			{ ?>
		<div class="message_error">A user ID to edit has not been specified.</div>
<?php		}
			else if ($_POST['changedisp'] == true)
			{
				// Update display name
				if (changeDisplayName($_GET['id'], $_POST['displayname']))
				{ ?>
		<div class="message">The user's display name was successfully updated.</div>
		<div class="text_center"><a href="admin/manageusers.php">Return</a></div>
<?php			}
				else
				{ ?>
		<div class="message_error">An error occurred while updating the user's display name in the database.</div>
		<div class="text_center">
			<a href="admin/manageusers.php?action=display&id=<?php echo $_GET['id']; ?>">Try Again</a>
			<a href="admin/manageusers.php">Cancel</a>
		</div>
<?php			}
			}
			else
			{
				// Prompt user with form
				$result = mysql_query("SELECT * FROM users WHERE id='" . $_GET['id'] . "'");
				if (!$result || mysql_num_rows($result) == 0)
				{ ?>
		<div class="message_error">An error occurred while accessing the database.</div>
<?php			}
				else
				{
					$user = mysql_fetch_array($result);
					?>
		<div class="head_subsection">User <i><?php echo $user['username']; ?></i></div>
		<form method="post" action="admin/manageusers.php?action=display&id=<?php echo $_GET['id']; ?>">
			<input type="hidden" name="changedisp" value="true">
			<table style="noborder" align="center">
				<tr>
					<td class="rightalign"><div class="text_right">Display Name:</div></td>
					<td><input type="text" width="50" name="displayname"></td>
				</tr>
				<tr>
					<td class="centeralign" colspan="2"><input type="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
<?php			}
			}
		}
		
		// Action: Normal
		else
		{
			$result = mysql_query("SELECT * FROM users");
			if (!$result)
			{ ?>
		<div class="message_error">
			An error occurred while reading the database. Please return to the <a href="admin/manageusers.php">User List</a>.
		</div>
<?php		}
			else
			{ ?>
		<table class="userlist" align="center">
			<tr>
				<td class="coltitle">Username</td>
				<td class="coltitle">Display</td>
				<td class="coltitle">Privileges</td>
				<td class="coltitle">Password</td>
				<td class="coltitle">Remove</td>
			</tr>
<?php		
			if (mysql_num_rows($result) == 0)
			{ ?>
			<tr class="item">
				<td class="centeralign" colspan="5">No users</td>
			</tr>
<?php		}
			else
			{
				while ($row = mysql_fetch_array($result))
				{ ?>
			<tr class="item">
				<td class="leftalign"><?php echo $row['username']; ?></td>
				<td class="centeralign"><a href="admin/manageusers.php?action=display&id=<?php echo $row['id']; ?>"><?php echo $row['displayname']; ?><img src="admin/img/pass.png"></a></td>
				<td class="centeralign"><a href="admin/manageusers.php?action=priv&id=<?php echo $row['id']; ?>"><img src="admin/img/pass.png" alt="Edit"></a></td>
				<td class="centeralign"><a href="admin/manageusers.php?action=pass&id=<?php echo $row['id']; ?>"><img src="admin/img/pass.png" alt="Edit"></a></td>
<?php				if ($row['username'] != $_SESSION['username'])
					{ ?>
				<td class="centeralign"><a href="admin/manageusers.php?action=delete&id=<?php echo $row['id']; ?>"><img src="admin/img/delete.png" alt="Delete"></a></td>
<?php				}
					else
					{ ?>
				<td class="centeralign"><img src="admin/img/delete_disabled.png"></td>
<?php				} ?>
			</tr>
<?php			}
			}
			?>
		</table>
		<br>
<?php			if ($register == REGISTER_SUCCESS)
					echo "<div class=\"message\">User <i>" . $_POST['username'] . "</i> added.<br></div>\n";
				if ($register == REGISTER_PASSEMPTY)
					echo "<div class=\"message_error\">You must enter a password.</div>\n";
				if ($register == REGISTER_PASSNOMATCH)
					echo "<div class=\"message_error\">Passwords do not match.</div>\n";
				if ($register == REGISTER_USEREXISTS)
					echo "<div class=\"message_error\">The username you selected (<i>" . $_POST['username'] . "</i>) already exists.</div>\n";
				if ($register == REGISTER_AUTHFAIL)
					echo "<div class=\"message_error\">Authentication of existing administrator failed.</div>";
				if ($register == REGISTER_DATAFAIL)
					echo "<div class=\"message_error\">A database error occurred while attempting to add the user.</div>";
				?>
		<div class="head_subsection">Add User</div>
		<form method="post" action="admin/manageusers.php">
			<input type="hidden" name="register" value="true">
			<table class="tableedit" align="center">
				<tr>
					<td class="left"><div class="text_right">Username</div></td>
					<td class="right"><input type="text" width="50" name="username" onKeyPress="return disableEnterKey(event)"></td>
				</tr>
				<tr>
					<td class="left"><div class="text_right">Display Name <a href="javascript:void()" onclick="toggleHelp('helpdispname')">(?)</a></div></td>
					<td class="right"><input type="text" width="50" name="displayname" onKeyPress="return disableEnterKey(event)"></td>
				</tr>
				<tr>
					<td class="center" colspan="2">
						<div id="helpdispname" class="helpnote">The <em>Display Name</em> is the name that will be shown to the public when this user makes a post to the blog.</div>
					</td>
				</tr>
				<tr>
					<td class="left"><div class="text_right">Password</div></td>
					<td class="right"><input type="password" width="50" name="pass" onKeyPress="return disableEnterKey(event)"></td>
				</tr>
				<tr>
					<td class="left"><div class="text_right">Confirm Password</div></td>
					<td class="right"><input type="password" width="50" name="passcheck" onKeyPress="return disableEnterKey(event)"></td>
				</tr>
				<tr>
					<td class="left"><div class="text_right">Privileges</div></td>
					<td class="right">
						<input type="checkbox" name="privblog">Post on blog<br>
						<input type="checkbox" name="privmod">Moderate blog<br>
						<input type="checkbox" name="privadmin">Site administrator<br>
					</td>
				</tr>
				<tr>
					<td class="center" colspan="2"><input type="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
<?php 	}
		}
	}
	// Privileges: None (not logged in)
	else
	{ ?>
	<div class="text_center">
		You do not have privileges to access this section of the website.<br>
<?php if ($_SESSION['priv'] == PRIV_NONE)
		{ ?>
		Please either <a href="admin/index.php?action=login">log in</a> or return to the <a href="index.php">home page</a>.
<?php } ?>
	</div>
<?php	} ?>
	</div> <!-- <div class="border"> -->
<?php
	if ($_SESSION['priv'] != PRIV_NONE)
	{ ?>
	<div class="text_center">Welcome, <em><?php echo $_SESSION['username']; ?>.</em></div>
<?php	} ?>
</body>

</html>
