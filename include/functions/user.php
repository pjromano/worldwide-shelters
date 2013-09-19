<?php
	/*
		User-Management Functions
	*/
	
	// Adds a user, returning the id of the newly added entry
	function addUser($username, $displayname, $password, $privileges)
	{
		$passentry = encryptPassword($password);
		$result = mysql_query("INSERT INTO users (username, displayname, password, privileges)
			VALUES('$username', '$displayname', '$passentry', '$privileges')");
		if (!$result)
			return -1;
		return mysql_insert_id();
	}
	
	// Returns whether the UPDATE succeeded
	function changePassword($userid, $password)
	{
		if (!$password)
			return false;
		
		$passentry = encryptPassword($password);
		return mysql_query("UPDATE users SET password='$passentry' WHERE id='$userid'");
	}
	
	// Returns whether UPDATE succeeded
	function changeDisplayName($userid, $displayname)
	{
		if (!$displayname)
			return false;
		return mysql_query("UPDATE users SET displayname='$displayname' WHERE id='$userid'");
	}
	
	// Returns whether DELETE succeeded
	function removeUser($userid)
	{
		return mysql_query("DELETE FROM users WHERE id='$userid'");
	}
	
	// Returns true if the username is taken
	// If a database error occurs, returns true (assume the worst)
	function isUsernameTaken($username)
	{
		$result = mysql_query("SELECT * FROM users");
		if (!$result)
			return true;
		
		$found = false;
		while (($row = mysql_fetch_assoc($result)) && !$found)
		{
			if (strtolower($row['username']) == strtolower($username))
				$found = true;
		}
		return $found;
	}
	
	function authenticateUser($username, $password)
	{
		$result = mysql_query("SELECT * FROM users WHERE username='$username'");
		
		if (!$result || mysql_num_rows($result) == 0)
			return LOGIN_USERFAIL;
		
		$row = mysql_fetch_array($result);
		
		$salt = substr($row['password'], 0, 8);
		if ($salt . "/" . sha1($salt . $password) == $row['password'])
			return LOGIN_SUCCESS;
		return LOGIN_PASSFAIL;
	}
	
	function getUserPrivileges($username)
	{
		$result = mysql_query("SELECT * FROM users WHERE username='$username'");
		if (!$result)
			return -1;
		$row = mysql_fetch_array($result);
		return $row['privileges'];
	}
	
	// Returns if change is successful
	function changeUserPrivileges($userid, $privileges)
	{
		return mysql_query("UPDATE users SET privileges='$privileges' WHERE id='$userid'");
	}
	
	function encryptPassword($pass)
	{
		$salt = generateSalt(8);
		$encrypted = $salt . "/" . sha1($salt . $pass);
		return $encrypted;
	}
	
	function generateSalt($length)
	{
		$salt = "";
		for ($i = 0; $i < $length; $i++)
		{
			$range = rand(0, 2);
			if ($range == 0)
				$salt .= chr(rand(48,57)); // 0 - 9
			else if ($range == 1)
				$salt .= chr(rand(65,90)); // A - Z
			else
				$salt .= chr(rand(97,122)); // a - z
		}
		return $salt;
	}
	
	// Gets the display name for the given user id or username
	// Returns "No Author" if user id is nonexistent, or an error occurred
	function getDisplayName($userid)
	{
		$result = mysql_query("SELECT * FROM users WHERE id='$userid'");
		if (!$result || mysql_num_rows($result) == 0)
			return "No Author";
		else
		{
			$user = mysql_fetch_array($result);
			return $user['displayname'];
		}
	}
	
	// Gets the display name for the given username
	// Returns "No Author" if username is nonexistent, or an error occurred
	function getDisplayNameFromUsername($user)
	{
		$result = mysql_query("SELECT * FROM users WHERE username='$user'");
		if (!$result || mysql_num_rows($result) == 0)
			return "No Author";
		else
		{
			$user = mysql_fetch_array($result);
			return $user['displayname'];
		}
	}
?>
