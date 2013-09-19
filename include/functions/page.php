<?php
	/*
		Page and Sections functions
	*/
	
	// Make accessname, removing characters outside alphanumeric character set
	// and changing spaces to underscores
	function makeAccessName($name)
	{
		$accessname = strtolower($name);
		for ($i = 0; $i < strlen($accessname); $i++)
		{
			$char = ord(substr($accessname, $i, 1));
			if (!(($char >= ord('0') && $char <= ord('9'))
					|| ($char >= ord('a') && $char <= ord('z'))
					|| $char == ord(' ') || $char == ord('_')))
			{
				$accessname = substr($accessname, 0, $i) . substr($accessname, $i + 1);
				$i--;
			}
		}
		return str_replace(" ", "_", $accessname);
	}
	
	// Return id of section given link name
	// Returns ERROR_DB on error
	function getSectionID($sectionlink)
	{
		$result = mysql_query("SELECT id FROM sections WHERE link='" . $sectionlink . "'");
		if (!$result)
			return ERROR_DB;
		else
		{
			$section = mysql_fetch_array($result);
			return $section['id'];
		}
	}
	
	// Return id of page given link name
	// Returns ERROR_DB on error
	function getPageID($pagelink)
	{
		$result = mysql_query("SELECT id FROM pages WHERE link='" . $pagelink . "'");
		if (!$result)
			return ERROR_DB;
		else
		{
			$page = mysql_fetch_array($result);
			return $page['id'];
		}
	}
	
	// Return the given section property given ID
	// Returns ERROR_DB on error
	function getSectionProperty($id, $property)
	{
		$result = mysql_query("SELECT " . mysql_real_escape_string($property) . " FROM sections WHERE id='" . $id . "'");
		if (!$result)
			return ERROR_DB;
		else
		{
			$section = mysql_fetch_array($result);
			return $section[$property];
		}
	}
	
	// Return the given page property given ID
	// Returns ERROR_DB on error
	function getPageProperty($id, $property)
	{
		$result = mysql_query("SELECT " . mysql_real_escape_string($property) . " FROM pages WHERE id='" . $id . "'");
		if (!$result)
			return ERROR_DB;
		else
		{
			$page = mysql_fetch_array($result);
			return $page[$property];
		}
	}
	
	// Returns true if there is a conflict of names with another section
	// (A name conflict is considered when the link names of two pages are the same, which would result
	// in ambiguity in deciding a page to display)
	// Returns -1 if an error occurs accessing the database
	// 	$section	- string representing the link name of a section
	function isSectionConflict($section)
	{
		$result = mysql_query("SELECT * FROM sections WHERE link='" . mysql_real_escape_string($section) . "'");
		if (!$result)
			return -1;
		else if (mysql_num_rows($result) === 0)
			return false;
		return true;
	}
	
	// Returns true if there is a conflict of names with another page in the section
	// Returns false if page does not already exist within section, or section does not exist
	// Returns -1 if an error occurs accessing the database
	// 	$sectionid	- int representing the id of the section to add to
	//		$page	- string representing the link name of the page
	function isPageConflict($sectionid, $page)
	{
		$result = mysql_query("SELECT * FROM pages WHERE section='$sectionid' AND link='" . mysql_real_escape_string($page) . "'");
		if (!$result)
			return -1;
		else if (mysql_num_rows($result) === 0)
			return false;
		return true;
	}
	
	// Returns the content of the given page
	// If an error occurs, ERROR_DB is returned
	// 	$section	- int representing id of section
	//		$page		- string representing link name of page
	function getPageContent($section, $page)
	{
		$sectionid = getSectionID($section);
		if ($sectionid < 0)
			return ERROR_DB;
		
		$result = mysql_query("SELECT * FROM pages WHERE section='" . $sectionid . "' AND link='" . mysql_real_escape_string($page) . "'");
		if (!result)
			return ERROR_DB;
		else
			return mysql_fetch_array($result);
	}
	
	// Adds a new section
	// Given the section title and type, appropriate values are generated based on the title
	// A new page is created to be the top page of the section
	// Return value:
	// 	id of new section on success (positive value)
	//		One of the Error Codes defined in start.php on failure (negative value)
	function addSection($sectiontitle, $sectiontype, $pagetitle, $linkurl, $content, $sidenavbg = 0, $gallery = 0, $captions = false)
	{
		if ($sectiontitle == "" || ($pagetitle == "" && $sectiontype != SECTIONTYPE_LINK))
			return ERROR_ADDPAGE_NONAME;
		
		// Generate link
		if ($sectiontype == SECTIONTYPE_LINK)
		{
			if ($linkurl === "")
				return ERROR_ADDPAGE_BADLINK;
			
			$link = strtolower($linkurl);
			if (stripos($link, "http://") !== 0 && stripos($link, "https://") !== 0)
				$link = "http://" . $link;
		}
		else if ($sectiontype == SECTIONTYPE_HOME)
			$linkurl = "home";
		else
		{
			if ($linkurl === "")
				$link = makeAccessName($sectiontitle);
			else
				$link = makeAccessName($linkurl);
		}
		
		if (isSectionConflict($link) === false || $sectiontype == SECTIONTYPE_LINK)
		{
			$result = mysql_query("SELECT ord FROM sections");
			if (!$result)
				return ERROR_DB;
			
			$largestord = 0;
			while ($row = mysql_fetch_array($result))
				if ($row['ord'] > $largestord)
					$largestord = $row['ord'];
			
			$result = mysql_query("INSERT INTO sections (name, link, displayname, toppage, type, sidenavbg, ord) VALUES ('"
				. mysql_real_escape_string($sectiontitle) . "', '"
				. mysql_real_escape_string($link) . "', '"
				. strtoupper(mysql_real_escape_string($sectiontitle)) . "', '0', '" // Links do not have a top page
				. "$sectiontype', '$sidenavbg', '"
				. ($largestord + 1) . "')");
			if (!$result)
				return ERROR_DB;
			
			if ($sectiontype == SECTIONTYPE_LINK)
				return mysql_insert_id();
			else
			{
				$sectionid = mysql_insert_id();
				
				$result = addPage($sectionid, $pagetitle, "", $content, $gallery, $captions);
				if ($result <= 0)
					return $result;
				$pageid = $result;
				
				$result = mysql_query("UPDATE sections SET toppage='$pageid' WHERE id='$sectionid'");
				if (!$result)
					return ERROR_DB;
				return $sectionid;
			}
		}
		else
			return ERROR_ADDPAGE_INUSE;
	}
	
	// Adds a new page
	// Appropriate values are generated based on the page title
	// $sectionid is the id of the section to add to
	// Return value:
	// 	id of new page on success (positive value)
	//		One of the Error Codes defined in start.php on failure (negative value)
	function addPage($sectionid, $title, $linkurl, $content, $gallery = 0, $captions = false)
	{
		if ($title == "")
			return ERROR_ADDPAGE_NONAME;
		
		if ($linkurl == "")
			$link = makeAccessName($title);
		else
			$link = makeAccessName($linkurl);
		
		if (isPageConflict($sectionid, $link) === false)
		{
			$result = mysql_query("SELECT ord FROM pages WHERE section='$sectionid'");
			$largestord = 0;
			while ($row = mysql_fetch_array($result))
				if ($row['ord'] > $largestord)
					$largestord = $row['ord'];
			
			$result = mysql_query("INSERT INTO pages (title, link, section, content, gallery, showcaptions, ord) "
				. "VALUES ('"
				. mysql_real_escape_string($title) . "', '"
				. mysql_real_escape_string($link) . "', '$sectionid', '"
				. mysql_real_escape_string($content) . "', '$gallery', '$showcaptions', '"
				. ($largestord + 1) . "')");
			
			if (!$result)
				return ERROR_DB;
			else
				return mysql_insert_id();
		}
		else
			return ERROR_ADDPAGE_INUSE;
	}
	
	// Update section information
	// Given id of section, the section information is updated,
	// and the title and content of the top page of the section is also updated
	// Returns:
	// 	0 on success
	// 	One of the error codes defined in start.php on error
	function updateSection($sectionid, $sectiontitle, $sectiontype, $pagetitle, $linkurl, $content, $sidenavbg, $gallery, $captions)
	{
		if ($sectiontitle == "")
			return ERROR_ADDPAGE_NONAME;
		
		// Generate link
		if ($sectiontype == SECTIONTYPE_LINK)
		{
			if ($linkurl == "")
				return ERROR_ADDPAGE_BADLINK;
			
			$link = strtolower($linkurl);
			if (stripos($link, "http://") !== 0 && stripos($link, "https://") !== 0)
				$link = "http://" . $link;
		}
		else
		{
			if ($linkurl == "")
				$link = makeAccessName($sectiontitle);
			else
				$link = makeAccessName($linkurl);
		}
		
		$result = mysql_query("UPDATE sections SET "
				. "name='" . mysql_real_escape_string($sectiontitle)
				. "', displayname='" . strtoupper(mysql_real_escape_string($sectiontitle))
				. "', link='" . mysql_real_escape_string($link)
				. "', type='$sectiontype', sidenavbg='$sidenavbg' WHERE id='$sectionid'");
		if (!$result)
			return ERROR_DB;
		
		// Update top page
		if ($sectiontype != SECTIONTYPE_LINK)
		{
			$sectionresult = mysql_query("SELECT * FROM sections WHERE id='$sectionid'");
			if (!$sectionresult)
				return ERROR_DB;
			$section = mysql_fetch_array($sectionresult);
			
			// First figure out if top page exists, creating it if it doesn't yet exist (i.e. section used to be a link)
			$result = mysql_query("SELECT * FROM pages WHERE id='" . $section['toppage'] . "'");
			if (!$result)
				return ERROR_DB;
			
			if (mysql_num_rows($result) === 0)
			{
				// Add new top page
				$result = addPage($sectionid, $pagetitle, "", $content, $gallery, $captions);
				if ($result <= 0)
					return $result;
				$pageid = $result;
				
				$result = mysql_query("UPDATE sections SET toppage='$pageid' WHERE id='$sectionid'");
				if (!$result)
					return ERROR_DB;
				return 0;
			}
			else
			{
				// Update top page
				$result = updatePage($section['toppage'], $pagetitle, "", $content, $gallery, $captions, $section['id']);
				if ($result !== 0)
					return $result;
				return 0;
			}
		}
		else
			return 0;
	}
	
	// Update page information
	// If the page does not exist, the page is created
	// Pass in the section if the page may need to be created
	// Returns:
	// 	0 on success
	// 	One of the error codes defined in start.php on error
	function updatePage($pageid, $title, $linkurl, $content, $gallery, $captions, $section = 0)
	{
		if ($title == "")
			return ERROR_ADDPAGE_NONAME;
		
		$result = mysql_query("SELECT * FROM pages WHERE id='$pageid'");
		if (!$result)
			return ERROR_DB;
		$page = mysql_fetch_array($result);
		
		if ($linkurl == "")
			$link = makeAccessName($title);
		else
			$link = makeAccessName($linkurl);
		
		if ($page['id'] != $pageid && isPageConflict($page['section'], $link))
		{
			if ($section > 0)
			{
				$result = addPage($section, $title, $linkurl, $content, $gallery, $captions);
				if ($result < 0)
					return $result;
				return 0;
			}
			else
				return ERROR_ADDPAGE_NOSECTION;
		}
		else
		{
			$result = mysql_query("UPDATE pages SET title='" . mysql_real_escape_string($title)
				. "', link='" . mysql_real_escape_string($link)
				. "', content='" . mysql_real_escape_string($content)
				. "', gallery='$gallery', showcaptions='$captions' WHERE id='$pageid'");
			
			if (!$result)
				return ERROR_DB;
			return 0;
		}
	}
	
	// Delete section
	// Returns true on success, false on error
	function deleteSection($sectionid)
	{
		$sectionresult = mysql_query("SELECT * FROM sections WHERE id='$sectionid'");
		if (!$sectionresult)
			return false;
		$section = mysql_fetch_array($sectionresult);
		
		$pageresult = mysql_query("SELECT * FROM pages WHERE section='" . $sectionid . "'");
		if (!$pageresult)
			return false;
		
		// Go ahead and delete section if the top page does not exist
		// (We don't want to have a section that cannot be deleted)
		if (mysql_num_rows($pageresult) === 0)
		{
			$result = mysql_query("DELETE FROM sections WHERE id='$sectionid'");
			if (!$result)
				return false;
			
			return true;
		}
		else
		{
			$success = true;
			while ($row = mysql_fetch_array($pageresult))
				$success = $success && deletePage($row['id']);
			
			if ($success)
			{
				$result = mysql_query("DELETE FROM sections WHERE id='$sectionid'");
				if (!$result)
					return false;
				return true;
			}
			else
				return false;
		}
	}
	
	// Delete page
	// Returns true on success, false on error
	function deletePage($pageid)
	{
		if (!$pageid)
			return false;
		
		$result = mysql_query("DELETE FROM pages WHERE id='$pageid'");
		if (!$result)
			return false;
		return true;
	}
	
	// Change order of section or page
	// $table should either be "sections" or "pages"
	// If $direction == "up", section will be moved up
	// If $direction == "dn", section will be moved down
	// Returns true on success, false on error
	function changeOrder($table, $id, $direction)
	{
		// Get an array of the section/page indices in order
		$query = "SELECT * FROM $table";
		if ($table == "pages")
		{
			// Only look at the pages within this page's section, and avoid the section's top page
			$result = mysql_query("SELECT section FROM pages WHERE id='$id'");
			if (!$result || mysql_num_rows($result) === 0)
				return false;
			$page = mysql_fetch_array($result);
			$result = mysql_query("SELECT toppage FROM sections WHERE id='" . $page['section'] . "'");
			if (!$result || mysql_num_rows($result) === 0)
				return false;
			$section = mysql_fetch_array($result);
			
			$query .= " WHERE section='" . $page['section'] . "' AND id!='" . $section['toppage'] . "'";
		}
		$query .= " ORDER BY ord";
		$result = mysql_query($query) or die(mysql_error());
		if (!$result)
			return false;
		
		$order = 0;
		while ($row = mysql_fetch_array($result))
		{
			$order++;
			$list[$order] = $row['id'];
		}
		if (!isset($list))
			return false;
		
		// Switch in specified direction
		if ($direction == "up")
		{
			$switched = false;
			for ($i = 2; $i <= $order && !$switched; $i++)
			{
				// Switch with the previous index
				if ($list[$i] == $id)
				{
					$temp = $list[$i - 1];
					$list[$i - 1] = $list[$i];
					$list[$i] = $temp;
					$switched = true;
				}
			}
		}
		else if ($direction == "dn")
		{
			$switched = false;
			for ($i = 1; $i < $order && !$switched; $i++)
			{
				// Switch with the next index
				if ($list[$i] == $id)
				{
					$temp = $list[$i + 1];
					$list[$i + 1] = $list[$i];
					$list[$i] = $temp;
					$switched = true;
				}
			}
		}
		else
			return false;
		
		// Update database
		for ($i = 1; $i <= $order; $i++)
		{
			$updateresult = mysql_query("UPDATE $table SET ord='$i' WHERE id='" . $list[$i] . "'");
			if (!$updateresult)
				return false;
		}
		
		return true;
	}
?>