<?php
	// Add a post to the blog table
	// 	$author - username of the author
	// Returns if successful
	function addBlogPost($author, $title, $content)
	{
		$authorresult = mysql_query("SELECT id FROM users WHERE username='$author'");
		if (!$authorresult || mysql_num_rows($authorresult) == 0)
			return false;
		
		$author = mysql_fetch_array($authorresult);
		return mysql_query("INSERT INTO blog (title, author, date, content) VALUES ('"
				. mysql_real_escape_string($title) . "', '"
				. $author['id'] . "', '"
				. date("Y-m-d H:i:s") . "', '"
				. mysql_real_escape_string($content) . "')");
	}
	
	// Add a comment to the comments table
	//		$postid - ID of post that this comment is associated with
	// 	$author - username of the author
	// Returns if successful
	function addComment($postid, $author, $content, $display = 0)
	{
		if (!$postid || !$author || !$content)
			return false;
		
		return mysql_query("INSERT INTO comments (author, content, date, blogpost, display) VALUES ('"
				. mysql_real_escape_string($author) . "', '"
				. mysql_real_escape_string($content) . "', '"
				. date("Y-m-d H:i:s") . "', '"
				. $postid . "', '$display')");
	}
	
	// Delete post specified by ID
	function deleteBlogPost($postid)
	{
		$result = mysql_query("DELETE FROM blog WHERE id='$postid'");
		if ($result)
			$result = mysql_query("DELETE FROM comments WHERE blogpost='$postid'");
		return $result;
	}
	
	// Delete comment specified by ID
	function deleteComment($commentid)
	{
		return mysql_query("DELETE FROM comments WHERE id='$commentid'");
	}
	
	// Sets comment to be approved (displayed on front)
	function approveComment($commentid)
	{
		return mysql_query("UPDATE comments SET display='1' WHERE id='$commentid'");
	}
?>
