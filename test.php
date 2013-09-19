<?php
	include("include/start.php");
	
	// $sectiondata is 0 if the page does not exist
	// Otherwise it is the information associated with the section
	$result = mysql_query("SELECT * FROM sections WHERE link='" . $_GET['section'] . "'");
	if (mysql_num_rows($result) === 0)
	{
		$sectiondata = 0;
		$pagedata = 0;
	}
	else
	{
		$sectiondata = mysql_fetch_array($result);
		
		if (!isset($_GET['page']))
			$pageresult = mysql_query("SELECT * FROM pages WHERE id='" . $sectiondata['toppage'] . "' AND section='" . $sectiondata['id'] . "'");
		else
			$pageresult = mysql_query("SELECT * FROM pages WHERE link='" . $_GET['page'] . "' AND section='" . $sectiondata['id'] . "'");
		
		// Get data about page
		if (!$pageresult || mysql_num_rows($pageresult) === 0)
			$pagedata = 0;
		else
			$pagedata = mysql_fetch_array($pageresult);
	}
	
	// User log in/out
	if ($_POST['authenticate'])
	{
		$loginflag = authenticateUser($_POST['username'], $_POST['pass']);
		if ($loginflag == LOGIN_SUCCESS)
		{
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['priv'] = getUserPrivileges($_SESSION['username']);
		}
	}
	else if ($_POST['logout'])
	{
		$_SESSION['username'] = "Anonymous";
		$_SESSION['priv'] = PRIV_NONE;
	}
	
	// Add new post to blog
	if ($_POST['newpost'] && (($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG))
	{
		$postresult = false;
		if (!addBlogPost($_SESSION['username'], $_POST['title'], $_POST['content']))
			$postresult = "Post was not successfully added.";
	}
	
	// Add new comment to blog
	if ($_POST['newcomment'])
	{
		if ($_SESSION['priv'] == PRIV_NONE)
			$commentmessage = "Thank you. Your comment will be visible once it has been approved.";
		else
			$commentmessage = false;
		
		$display = ($_SESSION['priv'] == PRIV_NONE) ? 0 : 1;
		if (!addComment($_POST['postid'], $_POST['author'], $_POST['content'], $display))
			$commentmessage = "We are sorry, your comment was not successfully added.";
	}
	
	// Upload image for blog
	if ($_POST['upload'] && (($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG))
	{
		$success = true;
		foreach ($_FILES['uploadfile']['error'] as $key => $error)
		{
			if ($error == UPLOAD_ERROR_OK)
			{
				if (!is_dir(SITE_MEDIADIRECTORY . "blog/"))
					mkdir(SITE_MEDIADIRECTORY . "blog/", 0755);
				
				$filename = SITE_MEDIADIRECTORY . "blog/" . basename($_FILES['uploadfile']['name'][$key]);
				if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key], $filename))
					$success = false;
				else
					$success = (resizeImageBlog($filename) == 0);
			}
			else
				$success = false;
		}
		$uploadresult = false;
		if (!$success)
			$uploadresult = "One or more of the files was not successfully uploaded.";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<base href="<?php echo SITE_BASE; ?>">
<?php
	if ($sectiondata === 0)
	{ ?>
	<title>Worldwide Shelters - Page Not Found</title>
<?php }
	else
	{ ?>
	<title>Worldwide Shelters - <?php echo $sectiondata['name']; ?><?php if (isset($_GET['page'])) echo " - " . $pagedata['title']; ?></title>
<?php } ?>
	<link rel="stylesheet" type="text/css" href="global.css">
<?php
	if ($sectiondata['type'] == SECTIONTYPE_HOME)
	{ ?>
	<link rel="stylesheet" type="text/css" href="home.css">
<?php }
	else
	{ ?>
	<link rel="stylesheet" type="text/css" href="main.css">
<?php } ?>
	<script src="Scripts/AC_RunActiveContent.js" type="text/javascript"></script>
<?php
	// Include FancyBox scripts and styles only if there is a gallery on this page, or it is the blog
	if ($pagedata['gallery'] > 0 || $sectiondata['type'] == SECTIONTYPE_BLOG)
	{ ?>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="com/fancybox1.3.4/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
	<script type="text/javascript" src="com/fancybox1.3.4/fancybox/jquery.easing-1.3.pack.js"></script>
	<link rel="stylesheet" type="text/css" href="com/fancybox1.3.4/fancybox/jquery.fancybox-1.3.4.css" media="screen">
	<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="blog.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			
			/* Using custom settings */
			
			$("a#inline").fancybox({
				'hideOnContentClick': true
			});

			/* Apply fancybox to multiple items */
			
			$("a.gallery").fancybox({
				'transitionIn'		:	'elastic',
				'transitionOut'	:	'elastic',
				'easingIn'			:	'easeOutBack',
				'easingOut'			:	'easeInBack',
				'speedIn'			:	300,
				'speedOut'			:	300,
				'overlayShow'		:	false,
				'titlePosition' 	:	'over',
				'centerOnScroll'	:	true,
				'titleFormat'		:	function(title, currentArray, currentIndex, currentOpts) {
					return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
				}
			});
			
		});
		
		tinyMCE.init({
				mode : "textareas",
				theme : "advanced",
				plugins : "inlinepopups,media",
				
<?php if (($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
		{ ?>
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,fontselect,fontsizeselect,forecolor",
				theme_advanced_buttons2 : "justifyleft,justifycenter,justifyright,justifyfull,|,undo,redo,|,cleanup,|,bullist,numlist,|,link,image,media",
				theme_advanced_buttons3 : "",
<?php }
		else
		{ ?>
				theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,link",
				theme_advanced_buttons2 : "",
				theme_advanced_buttons3 : "",
<?php	} ?>
				theme_advanced_toolbar_location : "bottom",
				theme_advanced_toolbar_align : "center",
				theme_advanced_statusbar_location : "none",
				theme_advanced_resizing : false,
				
				width : "100%",
<?php if (($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
		{ ?>
				height : "300px",
<?php }
		else
		{ ?>
				height : "100px",
<?php } ?>
				
				external_image_list_url : "blog_image.php"
		});
	</script>
<?php
	} ?>
</head>

<body>

<div id="headgraphic_container">
<?php
	if ($sectiondata['type'] == SECTIONTYPE_HOME)
	{ ?>
	<script type="text/javascript">
		AC_FL_RunContent('codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0','width','950','height','297','align','top','title','','src','Flash/header2','quality','high','pluginspage','http://www.macromedia.com/go/getflashplayer','bgcolor','0','movie','Flash/header2'); //end AC code
	</script>
	<noscript>
		<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="950" height="297" align="top" title="">
			<param name="movie" value="Flash/header2.swf" />
			<param name="quality" value="high" />
			<param name="BGCOLOR" value="0" />
			<embed src="Flash/header2.swf" width="950" height="297" align="top" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" bgcolor="0"></embed>
		</object>
	</noscript>
<?php }
	else
	{ ?>
	<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="950" height="130" align="top" title="">
		<param name="movie" value="Flash/header_main.swf" />
		<param name="quality" value="high" />
		<param name="BGCOLOR" value="0" />
		<embed src="Flash/header_main.swf" width="950" height="130" align="top" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" bgcolor="0"></embed>
	</object>
<?php } ?>
</div>

<?php include("include/menu.php"); ?>

<div id="page_container">
<?php
	// If section does not exist, display 404 message
	if ($sectiondata === 0)
	{
		?>
	<div class="header_center">
		Page Not Found
	</div>
	<div class="text_left">
		We are sorry, but the page you are looking for cannot be found.
		If you reached this page via a link on this website, please contact the Worldwide Shelters web administrator
		with information regarding the link leading to this page and the current URL.
	</div>
<?php }
	// Display page
	else
	{
		// If a there was a problem getting page contents, show an error message
		if (!$pagedata)
		{ ?>
	<div class="text_center">
		We are sorry, but the requested page could not be loaded. Either:<br>
		<ul>
			<li>The page does not exist.</li>
			<li>An error occurred on the web server.</li>
		</ul>
		Please press Back in your browser or return to the <a href="home">Home Page</a> to continue browsing.
	</div>
<?php	}
		
		// Otherwise, start displaying page content
		else
		{
			// Display side navigation bar
			if ($sectiondata['type'] != SECTIONTYPE_HOME)
			{
				$bgresult = mysql_query("SELECT path FROM images WHERE id='" . $sectiondata['sidenavbg'] . "'");
				if (!$bgresult || mysql_num_rows($bgresult) == 0)
					$sidenavbg = 0;
				else
					$sidenavbg = mysql_fetch_array($bgresult);
				
				$subpageresult = mysql_query("SELECT * FROM pages WHERE section='" . $sectiondata['id'] . "' ORDER BY ord");
			}
			
			if ($sectiondata['type'] == SECTIONTYPE_SUBPAGE || $sectiondata['type'] == SECTIONTYPE_DONATE)
			{ ?>
	<div id="sidenav_container"<?php if ($sidenavbg) { ?> style="background-image:url('media/<?php echo $sidenavbg['path']; ?>');"<?php } ?>>
<?php			while ($subpage = mysql_fetch_array($subpageresult))
				{ 
					if ($sectiondata['toppage'] != $subpage['id'])
					{ ?>
		<div class="sidenav<?php if ($subpage['id'] == $pagedata['id']) echo '_on'; ?>">
			<div class="sidenavcell">
				<div class="sidenavtext">
					<a href="<?php echo $sectiondata['link'] . '/' . $subpage['link']; ?>"><?php echo $subpage['title']; ?></a>
				</div>
			</div>
		</div>
<?php				}
				} ?>
	</div>
<?php		}
			
			else if ($sectiondata['type'] == SECTIONTYPE_BLOG)
			{ ?>
	<div id="sidenav_container"<?php if ($sidenavbg) { ?> style="background-image:url('media/<?php echo $sidenavbg['path']; ?>');"<?php } ?>>
		<div class="sidenav_archive">
			<strong>Archive</strong><br>
<?php			$archiveresult = mysql_query("SELECT date FROM blog");
				if ($archiveresult && mysql_num_rows($archiveresult) > 0)
				{
					$years = array();
					$months = array();
					while ($row = mysql_fetch_array($archiveresult))
					{
						$currenttime = strtotime($row['date']);
						$currentyr = date("Y", $currenttime);
						$currentmo = date("m", $currenttime);
						
						$yearused = false;
						for ($i = 0; $i < count($years) && !$yearused; $i++)
							if ($currentyr == $years[$i])
								$yearused = true;
						if (!$yearused)
						{
							$years[count($years)] = $currentyr;
							$months[$currentyr] = array();
						}
						
						$monthused = false;
						for ($i = 0; $i < count($months[$currentyr]); $i++)
							if ($currentmo == $months[$currentyr][$i])
								$monthused = true;
						if (!$monthused)
							$months[$currentyr][count($months[$currentyr])] = $currentmo;
					}
					
					foreach ($years as $yr)
					{ ?>
			<a href="javascript:void()" onclick="openArchiveYear(<?php echo $yr; ?>)"><?php echo $yr; ?></a><br>
			<div id="year<?php echo $yr; if ($_GET['yr'] == $yr) echo "_open"; ?>" class="archivelist">
<?php					foreach ($months[$yr] as $mo)
						{ ?>
				<a href="blog?yr=<?php echo $yr; ?>&mo=<?php echo $mo; ?>"><?php echo date("F", mktime(0, 0, 0, $mo)); ?></a><br>
<?php					} ?>
			</div>
<?php				}
				}
				else
				{ ?>
			<em>No Archives</em>
<?php			} ?>
		</div>
		<div class="sidenav_login">
			<div class="sidenavcell">
				<div class="sidenavtext">
<?php			if ($_SESSION['priv'] == PRIV_NONE)
				{ ?>
					<div id="loginformbtn"><a href="javascript:void()" onclick="openNewPost('loginform','loginformbtn')">Log In</a></div>
					<?php if ($_POST['authenticate'] && $loginflag != LOGIN_SUCCESS) echo "<em>Log in failed.</em>"; ?>
					<div id="loginform" class="nodisplay">
						<form method="post" action="<?php echo $sectiondata['link']; ?>">
							<input type="hidden" name="authenticate" value="true">
							<input id="loginname" type="text" name="username" width="50" onKeyPress="return disableEnterKey(event)"><br>
							<input id="loginpass" type="password" name="pass" width="50"><br>
							<input type="submit" value="Log In">
						</form>
					</div>
<?php			}
				else
				{ ?>
					Welcome, <em><?php echo $_SESSION['username']; ?></em>.<br>
					<form method="post" action="blog?action=logout">
						<input type="hidden" name="logout" value="true">
						<input type="submit" value="Log Out">
					</form>
<?php			} ?>
				</div>
			</div>
		</div>
	</div>
<?php		} ?>
	
	<div id="content_container">
<?php
			if ($sectiondata['type'] == SECTIONTYPE_HOME)
			{ ?>
		<div class="header">
			<?php echo $pagedata['title'] . "\n"; ?>
		</div>
		<div id="leftcolumn">
			<div class="leftimage">
				<img src="images/hands.jpg" alt="Teamwork">
			</div>
			<div class="borderbox_container">
				<div class="borderbox_top"></div>
				<div class="borderbox_text">
					<div class="borderbox_head">100%</div>
					of your donation goes directly to shelter solutions. All operating and overhead costs have been funded by separate donors.
				</div>
				<div class="borderbox_bottom"></div>
			</div>
			<div class="borderbox_container">
				<div class="borderbox_top"></div>
				<div class="borderbox_text">
					<div class="borderbox_head">501(c)(3)</div>
					gifts are tax deductible. 100% of your donation is tax deductible as a charitable donation.
				</div>
				<div class="borderbox_bottom"></div>
			</div>
		</div>
		<div id="rightcolumn">
<?php		}
			else if ($sectiondata['type'] == SECTIONTYPE_BLOG)
			{ ?>
			<div class="header_center">
				<?php echo $pagedata['title'] . "\n"; ?>
			</div>
<?php		}
			else
			{ ?>
			<div class="header_small">
				<?php echo $pagedata['title'] . "\n"; ?>
			</div>
<?php		}
			
			// Display blog, if current section type is the blog
			if ($sectiondata['type'] == SECTIONTYPE_BLOG)
			{
				$rangemessage = false;
				if (isset($_GET['yr']) && isset($_GET['mo']))
				{
					$query = "SELECT * FROM blog WHERE date BETWEEN "
							. $_GET['yr'] . $_GET['mo'] . "01000000 AND " . $_GET['yr'] . $_GET['mo'] . "31235959"
							. " ORDER BY date DESC";
					$rangemessage = "Displaying posts from " . date("F", mktime(0, 0, 0, $_GET['mo'])) . " " . $_GET['yr'];
				}
				else
					$query = "SELECT * FROM blog ORDER BY date DESC LIMIT 0,10";
				$blogresult = mysql_query($query);
				
				if (!$blogresult)
				{ ?>
			<div class="text_center">
				We are sorry, but the blog could not be loaded at this time due to a database error.<br>
				Please contact the web adminstrator if this problem persists.
			</div>
<?php			}
				else
				{
					if ($rangemessage)
					{ ?>
			<div class="rangemessage"><?php echo $rangemessage; ?></div>
<?php				}
					
					if (($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
					{ ?>
			<div id="newpostbtn"><a href="javascript:void()" onclick="openNewPost('newpost','newpostbtn')">New Post</a></div>
			<div id="newpost" class="newpost_container">
				<form method="post" action="blog">
					<input type="hidden" name="newpost" value="true">
					Title<br>
					<input type="text" name="title" onKeyPress="return disableEnterKey(event)"><br>
					Body<br>
					<textarea name="content"></textarea>
					<input type="submit" value="Post">
				</form>
			</div>
			<div id="uploadbtn"><a href="javascript:void()" onclick="openNewPost('upload','uploadbtn')">Upload Images</a><?php if ($_POST['upload'] && $uploadresult) echo "<br><em>Error - " . $uploadresult . "</em>"; else if ($_POST['upload']) echo "<br><em>Successfully uploaded files</em>"; ?></div>
			<div id="upload" class="upload_container">
				<form method="post" action="blog" enctype="multipart/form-data">
					<input type="hidden" name="upload" value="true">
					<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
					Upload Images<br>
					<div id="file1">File 1: <input type="file" name="uploadfile[]"></div>
					<div id="file2"><a href="javascript:void()" onclick="addFile(2)">Add File 2</a></div>
					<input type="submit" value="Upload">
				</form>
			</div>
<?php					if ($postresult)
						{ ?>
			<div class="post_container">
				<?php echo $postresult; ?>
			</div>
<?php					}
					}
					
					if (mysql_num_rows($blogresult) == 0)
					{ ?>
			<div class="text_center">
				Currently there are no posts on the blog. Please keep updated and check back later.
			</div>
<?php				}
					else
					{
						while ($post = mysql_fetch_array($blogresult))
						{
							$postdate = date("D, M j, Y \\a\\t g:ia", strtotime($post['date']));
							?>
			<div class="post_container">
				<div class="postdate">on <?php echo $postdate; ?></div>
				<div class="posttitle"><?php echo htmlentities($post['title']); ?></div>
				<div class="postauthor">Posted by <?php echo htmlentities(getDisplayName($post['author'])); ?></div>
				<div class="postcontent">
					<?php echo $post['content']; ?>
				</div>
<?php						$commentresult = mysql_query("SELECT * FROM comments WHERE blogpost='" . $post['id'] . "' AND display='1'");
							if ($commentresult)
							{
								while ($comment = mysql_fetch_array($commentresult))
								{ ?>
				<div class="comment">
					<div class="commentdate"><?php echo date("D, M j, Y \\a\\t g:ia", strtotime($comment['date'])); ?></div>
					<span class="commentauthor"><?php echo htmlentities($comment['author']); ?></span> says:<br>
					<div class="commentcontent">
						<?php echo $comment['content']; ?>
					</div>
				</div>
<?php							}
							}
							
							if ($_POST['newcomment'] && $_POST['postid'] == $post['id'] && $commentmessage)
							{ ?>
				<div class="comment">
					<div class="commentnote"><?php echo $commentmessage; ?></div>
				</div>
<?php						} ?>
				<div id="newcommentbtn<?php echo $post['id']; ?>" class="newcommentbtn"><a href="javascript:void()" onclick="openNewPost('newcomment<?php echo $post['id']; ?>','newcommentbtn<?php echo $post['id']; ?>')">Add Comment</a></div>
				<div id="newcomment<?php echo $post['id']; ?>" class="newcomment">
					<form method="post" action="blog<?php if (isset($_GET['yr']) && isset($_GET['mo'])) echo "?yr=" . $_GET['yr'] . "&mo=" . $_GET['mo']; ?>">
						<input type="hidden" name="newcomment" value="true">
						<input type="hidden" name="postid" value="<?php echo $post['id']; ?>">
						Name <input type="text" name="author" value="<?php if ($_SESSION['priv'] != PRIV_NONE) echo getDisplayNameFromUsername($_SESSION['username']); ?>" onKeyPress="return disableEnterKey(event)"><br><br>
						<textarea name="content"></textarea><br>						
<?php 					if ($_SESSION['priv'] == PRIV_NONE)
							{ ?>
						<div class="commentnote">Note: Your comment will not become visible until approved by the site administration.</div><br>
<?php						} ?>
						<input type="submit" value="Submit">
					</form>
				</div>
			</div>
<?php					}
					}
				}
			}
			
			// Display Donation page
			else if ($sectiondata['type'] == SECTIONTYPE_DONATE)
			{ ?>
			<div class="text_left">
				<?php echo $pagedata['content']; ?>
			</div>
			<iframe id="donateframe" src="https://www.kintera.org/AutoGen/Simple/Donor.asp?ievent=420615">
				Please follow the following link to make your donation:<br>
				<a href="https://www.kintera.org/AutoGen/Simple/Donor.asp?ievent=420615">Donate</a>
			</iframe>
<?php		}
			
			// Any other type of section besides blog
			else
			{ ?>
			<div class="text_left">
				<?php echo $pagedata['content']; ?>
			</div>
<?php			if ($pagedata['gallery'] > 0)
				{
					$imageresult = mysql_query("SELECT * FROM images WHERE gallery='" . $pagedata['gallery'] . "' AND isthumb=0");
					if ($imageresult)
					{ ?>
			<div class="gallery_container">
<?php					while ($image = mysql_fetch_array($imageresult))
						{
							$thumbresult = mysql_query("SELECT * FROM images WHERE id='" . $image['image_thumb'] . "'");
							if (!$thumbresult || mysql_num_rows($thumbresult) === 0)
							{
								$display = $image['caption'];
								$showcaption = false;
							}
							else
							{
								$thumb = mysql_fetch_array($thumbresult);
								$display = '<img src="media/' . $thumb['path'] . '" alt="' . $image['caption'] . '" title="' . $image['caption'] . '">';
								$showcaption = $pagedata['showcaptions'];
							}
						?>
				<div class="gallery_item">
					<a class="gallery" rel="gallery" href="media/<?php echo $image['path']; ?>"><?php echo $display; ?></a><br>
<?php						if ($showcaption)
							{ ?>
					<div class="gallery_caption"><?php echo $image['caption']; ?></div>
<?php						} ?>
				</div>
<?php					}
					} ?>
			</div>
<?php			}
				
				if ($sectiondata['type'] == SECTIONTYPE_HOME)
				{ ?>
		</div>
<?php			}
			}
		}
	} ?>
	</div>
</div>

<?php include("include/footer.php"); ?>

</body>

</html>
