<?php
	include("../include/start.php");
	
	// The type of operation that was carried out
	// 0 means no operation
	$operation = 0;
	
	// Result of operation
	// If there was an error, $opresult contains a string with the error message
	$opresult = "";
	
	// Add new section or page
	if ($_GET['op'] == "new")
	{
		if ($_GET['s'] == "new")
		{
			$operation = "new";
			
			if ($_POST['stitle'] == "")
				$opresult .= "You must enter a name for the section.<br>";
			if ($_POST['stype'] != SECTIONTYPE_LINK && $_POST['ptitle'] == "")
				$opresult .= "You must enter a title for the page.<br>";
			
			if ($opresult === "")
			{
				$result = addSection($_POST['stitle'], $_POST['stype'], $_POST['ptitle'], $_POST['linkurl'], $_POST['content'], $_POST['sidenavbg'], $_POST['gallery'], ($_POST['captions'] === "on"));
				$opresult = getErrorString($result);
			}
		}
		else if ($_GET['pg'] == "new")
		{
			$operation = "new";
		
			if ($_POST['ptitle'] == "")
				$opresult .= "You must enter a title for the page.<br>";
			
			if ($opresult === "")
			{
				$result = addPage($_GET['s'], $_POST['ptitle'], $_POST['linkurl'], $_POST['content'], $_POST['gallery'], ($_POST['captions'] === "on"));
				$opresult = getErrorString($result);
			}
		}
	}
	
	// Update section or page information
	else if ($_GET['op'] == "update")
	{
		$operation = "update";
		
		if (isset($_GET['s']))
		{
			if ($_POST['stitle'] == "")
				$opresult .= "You must enter a name for the section.<br>";
			if ($_POST['stype'] != SECTIONTYPE_LINK && $_POST['ptitle'] == "")
				$opresult .= "You must enter a title for the page.<br>";
			
			if ($opresult === "")
			{
				$result = updateSection($_GET['s'], $_POST['stitle'], $_POST['stype'], $_POST['ptitle'], $_POST['linkurl'], $_POST['content'], $_POST['sidenavbg'], $_POST['gallery'], ($_POST['captions'] === "on"));
				$opresult .= getErrorString($result);
			}
		}
		else if (isset($_GET['pg']))
		{
			if ($_POST['ptitle'] == "")
				$opresult .= "You must enter a name for the section.<br>";
			
			if ($opresult === "")
			{
				$result = updatePage($_GET['pg'], $_POST['ptitle'], $_POST['linkurl'], $_POST['content'], $_POST['gallery'], ($_POST['captions'] === "on"));
				$opresult .= getErrorString($result);
			}
		}
		else
			$opresult .= "A page or section ID was not specified. Please select a page from the left to edit.<br>";
	}
	
	// Change section/page order
	else if ($_GET['op'] == "move")
	{
		$operation = "move";
		
		if (isset($_GET['s']))
		{
			
			if (!changeOrder("sections", $_GET['s'], $_GET['dir']))
				$opresult .= "A database error occured while updating the section order.<br>";
		}
		else if (isset($_GET['pg']))
		{
			if (!changeOrder("pages", $_GET['pg'], $_GET['dir']))
				$opresult .= "A database error occurred while updating the page order.<br>";
		}
		else
			$opresult .= "A page or section ID to move was not specified.<br>";
	}
	
	// Delete confirmation
	else if ($_GET['op'] == "deleteconfirm")
		$operation = "deleteconfirm";
	
	// Delete section/page
	else if ($_GET['op'] == "delete")
	{
		$operation = "delete";
		
		if (isset($_GET['s']))
		{
			if (!deleteSection($_GET['s']))
				$opresult .= "Failed to delete section from database.<br>";
		}
		else if (isset($_GET['pg']))
		{
			if (!deletePage($_GET['pg']))
				$opresult .= "Failed to delete page from database.<br>";
		}
		else
			$opresult .= "A page or section ID to delete was not specified.<br>";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<base href="<?php echo SITE_BASE; ?>">
	<title><?php
		echo SITE_TITLE . " - Administration - Content";
	?></title>
	<link rel="stylesheet" href="admin/adminstyle.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="admin/script.js"></script>
	<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript">
		tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "inlinepopups,media",
				
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,fontselect,fontsizeselect,forecolor",
				theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright,justifyfull,|,indent,outdent,|,undo,redo,|,cleanup,|,bullist,numlist,|,link,image,media",
				theme_advanced_buttons3 : "",
				theme_advanced_toolbar_location : "bottom",
				theme_advanced_toolbar_align : "center",
				theme_advanced_statusbar_location : "none",
				theme_advanced_resizing : false,
				
				external_image_list_url : "lists/image_list.php"
		});
	</script>
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
			<li class="menu_current"><a href="admin/content.php">Content</a></li>
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
			<li class="menu"><a href="admin/index.php">Admin Index</a></li>
			<li class="menu_current"><a href="admin/blog.php">Blog</a></li>
			<li class="menu"><a href="admin/index.php?action=logout">Log Out</a></li>
		</ul>
	</div>
<?php
	}
	
	// Page Title
	?>
	<div class="head2">Content</div>
	<div id="border_content">
<?php
	// Privileges: Admin
	if (($_SESSION['priv'] & PRIV_ADMIN) == PRIV_ADMIN)
	{
		/*
			Display file viewer
		*/
		?>
		<div id="fileviewer_container">
			<table id="fileviewer_table" width="100%" cellspacing="0px">
				<tr>
					<td colspan="2">Page Name</td>
					<td colspan="2">Order</td>
					<td>Delete</td>
				</tr>
<?php
		// Figure out which section is open based on $_GET variables
		$opensectionresult = 0;
		if (isset($_GET['s']) && $_GET['op'] != "move")
		{
			$opensectionresult = mysql_query("SELECT * FROM sections WHERE id='" . $_GET['s'] . "'");
			if (!$opensectionresult)
			{ ?>
			<tr>
				<td colspan="6">Failed to read sections from database.</td>
			</tr>
<?php		}
			$opensection = mysql_fetch_array($opensectionresult);
		}
		else if (isset($_GET['pg']))
		{
			$result = mysql_query("SELECT section FROM pages WHERE id='" . $_GET['pg'] . "'");
			if (!$result)
			{ ?>
				<tr>
					<td colspan="6">Failed to read pages from database.</td>
				</tr>
<?php		}
			$openpage = mysql_fetch_array($result);
			$opensectionresult = mysql_query("SELECT * FROM sections WHERE id='" . $openpage['section'] . "'");
			if (!$opensectionresult)
			{ ?>
				<tr>
					<td colspan="6">Failed to read sections from database.</td>
				</tr>
<?php		}
			$opensection = mysql_fetch_array($opensectionresult);
		}
		
		if ($opensectionresult === 0)
			$opensection = 0;
		
		$sectionresult = mysql_query("SELECT * FROM sections ORDER BY ord");
		if (!$sectionresult)
		{ ?>
				<tr>
					<td colspan="5">Failed to read sections from database.</td>
				</tr>
<?php	}
		else
		{
			$sectionnum = 1;
			$totalsections = mysql_num_rows($sectionresult);
			while ($section = mysql_fetch_array($sectionresult))
			{ ?>
				<tr class="section">
					<td class="tree_button"><?php
						if ($section['type'] == SECTIONTYPE_SUBPAGE)
						{
							echo '<img id="treebutton' . $section['id'] . '_';
							if ($section['id'] == $opensection['id'])
								echo 'open';
							echo '" src="admin/img/tree_expand.png" alt="" onclick="toggleTreeSection(' . $section['id'] . ')">';
						}
						
						// Put ellipsis if name is too long
						$sectionname = $section['name'];
						if (strlen($sectionname) > 30)
							$sectionname = substr($sectionname, 0, 27) . "...";
					?></td>
					<td class="title"><a href="admin/content.php?<?php echo 's=' . $section['id']; ?>"><?php echo $sectionname; ?></a><?php if ($section['type'] == SECTIONTYPE_HOME) echo ' <img src="admin/img/home.png">'; ?></td>
					<td class="opbutton"><?php if ($sectionnum > 1) { ?><a href="admin/content.php?op=move&dir=up&s=<?php echo $section['id']; ?>"><img src="admin/img/arrowup.png" alt="v" title="Move Up"></a><?php } ?></td>
					<td class="opbutton"><?php if ($sectionnum < $totalsections) { ?><a href="admin/content.php?op=move&dir=dn&s=<?php echo $section['id']; ?>"><img src="admin/img/arrowdown.png" alt="v" title="Move Down"></a><?php } ?></td>
					<td class="opbutton"><a href="admin/content.php?op=deleteconfirm&s=<?php echo $section['id']; ?>"><img src="admin/img/delete.png" alt="Delete" title="Delete"></a></td>
				</tr>
<?php			if ($section['type'] == SECTIONTYPE_SUBPAGE)
				{
					$pageresult = mysql_query("SELECT * FROM pages WHERE section='" . $section['id'] . "' AND id!='" . $section['toppage'] . "' ORDER BY ord");
					if (!$pageresult)
					{ ?>
				<tr>
					<td colspan="6">Failed to read subpages from database.</td>
				</tr>
<?php				}
					else
					{
						$rowid = 0;
						$totalpages = mysql_num_rows($pageresult);
						while ($page = mysql_fetch_array($pageresult))
						{
							$pagetitle = $page['title'];
							if (strlen($pagetitle) > 30)
								$pagetitle = substr($pagetitle, 0, 27) . "...";
							?>
				<tr id="treerow<?php
							echo $section['id'] . '_' . $rowid;
							if ($section['id'] == $opensection['id'])
								echo '_open';
				?>">
					<td class="treelist"></td>
					<td class="title_indent"><a href="admin/content.php?<?php echo 's=' . $section['id'] . '&pg=' . $page['id']; ?>"><?php echo $pagetitle; ?></a></td>
					<td class="opbutton"><?php if ($rowid > 0) { ?><a href="admin/content.php?op=move&dir=up&pg=<?php echo $page['id']; ?>"><img src="admin/img/arrowup.png" alt="v" title="Move Up"></a><?php } ?></td>
					<td class="opbutton"><?php if ($rowid < $totalpages - 1) { ?><a href="admin/content.php?op=move&dir=dn&pg=<?php echo $page['id']; ?>"><img src="admin/img/arrowdown.png" alt="v" title="Move Down"></a><?php } ?></td>
					<td class="opbutton"><a href="admin/content.php?op=deleteconfirm&pg=<?php echo $page['id']; ?>"><img src="admin/img/delete.png" alt="X" title="Delete"></a></td>
				</tr>
<?php						$rowid++;
						} ?>
				<tr id="treerow<?php
						echo $section['id'] . '_add';
						if ($section['id'] == $opensection['id'])
							echo '_open';
				?>">
					<td class="treelist_last"></td>
					<td class="title_indent" colspan="4"><a href="admin/content.php?s=<?php echo $section['id']; ?>&pg=new"><em>Add Page</em></a></td>
				</tr>
<?php				}
				}
				$sectionnum++;
			} ?>
				<tr class="section">
					<td class="tree_button"></td>
					<td class="title" colspan="4"><a href="admin/content.php?s=new"><em>Add Section</em></a></td>
				</tr>
<?php	} ?>
			</table>
		</div>
		<div id="editor_container">
<?php
		/*
			Display editor column
		*/
		
		// No operation has been completed yet; otherwise we will display the result of the operation
		if ($operation === 0)
		{
			// If we are creating a new page or section
			if ($_GET['s'] == "new" || $_GET['pg'] == "new")
			{
				$ispage = ($_GET['pg'] == "new");
				?>
			<div class="head_subsection">New <?php if ($ispage) echo "Page"; else echo "Section"; ?></div>
			<form method="post" action="<?php
				if ($ispage)
					echo 'admin/content.php?op=new&s=' . $_GET['s'] . '&pg=new';
				else
					echo 'admin/content.php?op=new&s=new';
				?>">
				<table class="tableedit">
<?php			if ($ispage)
				{ ?>
					<tr>
						<td class="left">Title</td>
						<td class="right"><input type="text" name="ptitle" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
<?php			}
				else
				{ ?>
					<tr>
						<td class="left">Section Title</td>
						<td class="right"><input type="text" name="stitle" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Page Title</td>
						<td class="right"><input type="text" name="ptitle" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Section Type</td>
						<td class="right">
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_HOME; ?>" onclick="updateURLEnabled()">Home<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_SUBPAGE; ?>" onclick="updateURLEnabled()" checked>Normal<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_LINK; ?>" onclick="updateURLEnabled()">Link<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_BLOG; ?>" onclick="updateURLEnabled()">Blog<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_DONATE; ?>" onclick="updateURLEnabled()">Donation<br>
						</td>
					</tr>
<?php			} ?>
					<tr>
						<td class="left">URL <a href="javascript:void()" onclick="toggleHelp('helpurl')">(?)</a></td>
						<td class="right" colspan="2">
							<input type="text" name="linkurl" id="linkurl" onKeyPress="return disableEnterKey(event)">
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="helpnote" id="helpurl"><?php
								if (!$ispage)
									echo "You must specify an external URL if this section is to be a link to an outside website. For sections that are not links to external websites, the";
								else
									echo "The"; ?> URL is the name used in the address bar to refer to a specific page. For this reason, URLs of each page and section must be unique.</div>
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<textarea name="content" class="texteditor"></textarea>
						</td>
					</tr>
<?php				if (!$ispage)
					{ ?>
					<tr>
						<td class="left">SideNav Background</td>
						<td class="right">
<?php							$sidenavresult = mysql_query("SELECT * FROM images WHERE sidenav='1'");
								if (!$sidenavresult)
								{ ?>
							<em>Failed to read images from database</em>
<?php							}
								else
								{ ?>
							<select name="sidenavbg">
								<option value="0">None (White)
<?php								while ($sidenav = mysql_fetch_array($sidenavresult))
									{ ?>
								<option value="<?php echo $sidenav['id']; ?>"><?php if ($sidenav['caption']) echo $sidenav['caption']; else echo $sidenav['path']; ?>
<?php								} ?>
							</select>
<?php							} ?>
						</td>
					</tr>
<?php				} ?>
					<tr>
						<td class="left">Display Gallery</td>
						<td class="right">
<?php							$galleryresult = mysql_query("SELECT * FROM galleries");
								if (!$galleryresult)
								{ ?>
							<em>Failed to read galleries from database</em>
<?php							}
								else
								{ ?>
							<select name="gallery">
								<option value="0">No gallery
<?php								while ($gallery = mysql_fetch_array($galleryresult))
									{ ?>
								<option value="<?php echo $gallery['id']; ?>"><?php echo $gallery['name']; ?>
<?php								} ?>
							</select>
<?php							} ?>
						</td>
					</tr>
					<tr>
						<td class="left">Show Captions</td>
						<td class="right"><input type="checkbox" name="captions" checked></td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Add <?php if ($ispage) echo 'Page'; else echo 'Section'; ?>"></td>
					</tr>
				</table>
			</form>
<?php		}
			
			// If we are editing a section
			// Show form to edit section properties and also the properties of the top page of the section
			else if (isset($_GET['s']) && !isset($_GET['pg']))
			{
				$success = false;
				$sectionresult = mysql_query("SELECT * FROM sections WHERE id='" . $_GET['s'] . "'");
				if ($sectionresult)
				{
					$section = mysql_fetch_array($sectionresult);
					$pageresult = mysql_query("SELECT * FROM pages WHERE id='" . $section['toppage'] . "'");
					if ($pageresult)
					{
						$page = mysql_fetch_array($pageresult);
						$success = true;
					}
				}
				
				if (!$success)
				{ ?>
			<div class="message_error">
				Failed to read page information from the database. Please try again.
			</div>
<?php			}
				else
				{ ?>
			<div class="head_subsection">Editing Section <i><?php echo $section['name']; ?></i></div>
			<form method="post" action="admin/content.php?op=update&s=<?php echo $_GET['s']; ?>">
				<input type="hidden" name="linkorig" id="linkorig" value="<?php echo $section['link']; ?>">
				<table class="tableedit">
					<tr>
						<td class="left">Section Title</td>
						<td class="right"><input type="text" name="stitle" value="<?php echo $section['name']; ?>" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Page Title</td>
						<td class="right"><input type="text" name="ptitle" value="<?php echo $page['title']; ?>" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">Section Type</td>
						<td class="right">
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_HOME; ?>" onclick="updateURLEnabled()"<?php if ($section['type'] == SECTIONTYPE_HOME) echo " checked"; ?>>Home<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_SUBPAGE; ?>" onclick="updateURLEnabled()"<?php if ($section['type'] == SECTIONTYPE_SUBPAGE) echo " checked"; ?>>Normal<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_LINK; ?>" onclick="updateURLEnabled()"<?php if ($section['type'] == SECTIONTYPE_LINK) echo " checked"; ?>>Link<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_BLOG; ?>" onclick="updateURLEnabled()"<?php if ($section['type'] == SECTIONTYPE_BLOG) echo " checked"; ?>>Blog<br>
							<input type="radio" name="stype" value="<?php echo SECTIONTYPE_DONATE; ?>" onclick="updateURLEnabled()"<?php if ($section['type'] == SECTIONTYPE_DONATE) echo " checked"; ?>>Donation<br>
						</td>
					</tr>
					<tr>
						<td class="left">URL <a href="javascript:void()" onclick="toggleHelp('helpurl')">(?)</a></td>
						<td class="right" colspan="2">
							<input type="text" name="linkurl" id="linkurl" value="<?php echo htmlentities($section['link']); ?>" onKeyPress="return disableEnterKey(event)">
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="helpnote" id="helpurl">You must specify an external URL if this section is to be a link to an outside website. For sections that are not links to external websites, the URL is the name used in the address bar to refer to a specific page. For this reason, URLs of each section must be unique.</div>
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<textarea name="content" class="texteditor">
								<?php echo $page['content']; ?>
							</textarea>
						</td>
					</tr>
					<tr>
						<td class="left">SideNav Background</td>
						<td class="right">
<?php							$sidenavresult = mysql_query("SELECT * FROM images WHERE sidenav='1'");
								if (!$sidenavresult)
								{ ?>
							<em>Failed to read images from database</em>
<?php							}
								else
								{ ?>
							<select name="sidenavbg">
								<option value="0">None (White)
<?php								while ($sidenav = mysql_fetch_array($sidenavresult))
									{ ?>
								<option value="<?php echo $sidenav['id']; ?>"<?php if ($section['sidenavbg'] == $sidenav['id']) echo " selected"; ?>><?php if ($sidenav['caption']) echo $sidenav['caption']; else echo $sidenav['path']; ?>
<?php								} ?>
							</select>
<?php							} ?>
						</td>
					</tr>
					<tr>
						<td class="left">Display Gallery</td>
						<td class="right">
<?php							$galleryresult = mysql_query("SELECT * FROM galleries");
								if (!$galleryresult)
								{ ?>
							<em>Failed to read galleries from database</em>
<?php							}
								else
								{ ?>
							<select name="gallery">
								<option value="0">No gallery
<?php								while ($gallery = mysql_fetch_array($galleryresult))
									{ ?>
								<option value="<?php echo $gallery['id']; ?>"<?php if ($page['gallery'] == $gallery['id']) echo " selected"; ?>><?php echo $gallery['name']; ?>
<?php								} ?>
							</select>
<?php							} ?>
						</td>
					</tr>
					<tr>
						<td class="left">Show Captions</td>
						<td class="right"><input type="checkbox" name="captions"<?php if ($page['showcaptions']) echo " checked"; ?>></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<input type="submit" value="Update">
						</td>
					</tr>
				</table>
			</form>
<?php			}
			}
			
			// If we are editing a subpage
			else if (isset($_GET['pg']))
			{
				$pageresult = mysql_query("SELECT * FROM pages WHERE id='" . $_GET['pg'] . "'");
				if (!$pageresult)
				{ ?>
			<div class="message_error">
				Failed to read page information from the database. Please try again.
			</div>
<?php			}
				else
				{
					$page = mysql_fetch_array($pageresult);
					?>
			<div class="head_subsection">Editing Page <i><?php echo $page['title']; ?></i></div>
			<form method="post" action="admin/content.php?op=update&pg=<?php echo $_GET['pg']; ?>">
				<table class="tableedit">
					<tr>
						<td class="left">Page Title</td>
						<td class="right"><input type="text" name="ptitle" value="<?php echo $page['title']; ?>" onKeyPress="return disableEnterKey(event)"></td>
					</tr>
					<tr>
						<td class="left">URL <a href="javascript:void()" onclick="toggleHelp('helpurl')">(?)</a></td>
						<td class="right" colspan="2">
							<input type="text" name="linkurl" id="linkurl" value="<?php echo $page['link']; ?>" onKeyPress="return disableEnterKey(event)">
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<div class="helpnote" id="helpurl">The URL is the name used in the address bar to refer to a specific page. For this reason, URLs of each page and section must be unique.</div>
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<textarea name="content" class="texteditor">
								<?php echo $page['content']; ?>
							</textarea>
						</td>
					</tr>
					<tr>
						<td class="left">Display Gallery</td>
						<td class="right">
<?php							$galleryresult = mysql_query("SELECT * FROM galleries");
								if (!$galleryresult)
								{ ?>
							<em>Failed to read galleries from database</em>
<?php							}
								else
								{ ?>
							<select name="gallery">
								<option value="0">No gallery<br>
<?php								while ($gallery = mysql_fetch_array($galleryresult))
									{ ?>
								<option value="<?php echo $gallery['id']; ?>"<?php if ($page['gallery'] == $gallery['id']) echo " selected"; ?>><?php echo $gallery['name']; ?><br>
<?php								} ?>
							</select>
<?php							} ?>
						</td>
					</tr>
					<tr>
						<td class="left">Show Captions</td>
						<td class="right"><input type="checkbox" name="captions"<?php if ($page['showcaptions']) echo " checked"; ?>></td>
					</tr>
					<tr>
						<td class="center" colspan="2">
							<input type="submit" value="Update">
						</td>
					</tr>
				</table>
			</form>
<?php			}
			}
			
			// Display default message for this page
			else
			{ ?>
			<div class="message_editor">
				Please select a page to edit from the list on the left.
			</div>
<?php		}
		}
		
		// Display confirmation message for deleting page or section
		else if ($operation == "deleteconfirm")
		{
			if (isset($_GET['s']))
			{
				$name = getSectionProperty($_GET['s'], "name");
				if ($name === ERROR_DB)
				{ ?>
			<div class="message_error_editor">
				An internal database error occurred while accessing the section title.<br>
				Please press back in your browser to try again.
			</div>
<?php			}
				else
				{ ?>
			<div class="message_editor">
				Are you sure you want to delete section <em><?php echo $name; ?></em>?<br>
				<form method="post" action="admin/content.php?op=delete&s=<?php echo $_GET['s']; ?>">
					<input type="submit" value="Yes">
				</form>
				<form method="post" action="admin/content.php">
					<input type="submit" value="No">
				</form>
			</div>
<?php			}
			}
			else if (isset($_GET['pg']))
			{
				$name = getPageProperty($_GET['pg'], "title");
				if ($name === ERROR_DB)
				{ ?>
			<div class="message_error_editor">
				An internal database error occurred while accessing the page title.<br>
				Please press back in your browser to try again.
			</div>
<?php			}
				else
				{ ?>
			<div class="message_editor">
				Are you sure you want to delete page <em><?php echo $name; ?></em>?<br>
				<form method="post" action="admin/content.php?op=delete&pg=<?php echo $_GET['pg']; ?>">
					<input type="submit" value="Yes">
				</form>
				<form method="post" action="admin/content.php">
					<input type="submit" value="No">
				</form>
			</div>
<?php			}
			}
			else
			{ ?>
			<div class="message_error_editor">
				A section or page ID to delete was not specified.<br>
				Please press back in your browser to try again.
			</div>
<?php		}
		}
		
		// Output operation result information
		else
		{
			// Create messages according to operation type
			if ($operation == "new")
			{
				$opsuccess = "The page was successfully added to the database.";
				$opfail = "An error occurred while adding the new page to the database:";
			}
			else if ($operation == "update")
			{
				$opsuccess = "The page was successfully updated.";
				$opfail = "An error occurred while updating the page information:";
			}
			else if ($operation == "delete")
			{
				$opsuccess = "The page was successfully deleted.";
				$opfail = "An error occurred while deleting the page from the database:";
			}
			else if ($operation == "move")
			{
				$opsuccess = "The order of the pages was successfully updated.";
				$opfail = "An error occurred while updating the page order:";
			}
			
			if ($opresult === "")
			{ ?>
			<div class="message_editor">
				<?php echo $opsuccess; ?>
			</div>
<?php		}
			else
			{ ?>
			<div class="message_error_editor">
				<?php echo $opfail; ?><br>
				<?php echo $opresult; ?><br>
				Please press back in your browser to try again.
			</div>
<?php		}
		} ?>
		</div>
		<div class="clear"></div>
<?php
	}
	
	// Privileges: None
	else
	{ ?>
		<div class="text_center">
			You do not have privileges to access this section of the website.<br>
<?php if ($_SESSION['priv'] == PRIV_NONE)
		{ ?>
		Please either <a href="admin/index.php?action=login">log in</a> or return to the <a href="index.php">home page</a>.
<?php } ?>
		</div>
<?php } ?>
	</div>
<?php
	if ($_SESSION['priv'] != PRIV_NONE)
		{ ?>
	<div class="text_center">Welcome, <em><?php echo $_SESSION['username']; ?>.</em></div>
<?php	} ?>
</body>

</html>
