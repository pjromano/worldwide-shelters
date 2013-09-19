<?php
	include("../include/start.php");
	
	if ($_GET['action'] == "approve")
		$approveresult = approveComment($_GET['comment']);
	
	else if ($_GET['action'] == "delete")
	{
		$deleteresult = false;
		if (isset($_GET['comment']))
		{
			if (!deleteComment($_GET['comment']))
				$deleteresult = "Failed to delete comment.";
		}
		else if (isset($_GET['post']))
		{
			if (!deleteBlogPost($_GET['post']))
				$deleteresult = "Failed to delete post.";
		}
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<base href="<?php echo SITE_BASE; ?>">
	<title><?php echo SITE_TITLE; ?> - Administration - Manage Blog</title>
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
			<li class="menu_current"><a href="admin/blog.php">Blog</a></li>
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
	<div class="head2">Blog</div>
	<div id="border_blog">
<?php
	if (($_SESSION['priv'] & PRIV_MOD) == PRIV_MOD || ($_SESSION['priv'] & PRIV_BLOG) == PRIV_BLOG)
	{ ?>
		<div id="postlist_container">
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
				<a href="admin/blog.php?yr=<?php echo $yr; ?>&mo=<?php echo $mo; ?>"><?php echo date("F", mktime(0, 0, 0, $mo)); ?></a><br>
<?php					} ?>
			</div>
<?php				}
				}
				else
				{ ?>
			<em>No Archives</em>
<?php			} ?>
		</div>
		<div id="postedit_container">
<?php			if ($_GET['action'] == "approve" && !$approveresult)
				{ ?>
			<div class="message_error">
				A database error occurred when approving the comment. Please try again later.
			</div>
<?php			}
				else if ($_GET['action'] == "delete" && $deleteresult)
				{ ?>
			<div class="message_error">
				<?php echo $deleteresult; ?>
			</div>
<?php			} ?>
			<ul class="postlist">
<?php			if (isset($_GET['yr']) && isset($_GET['mo']))
				{
					$result = mysql_query("SELECT * FROM blog WHERE date BETWEEN "
							. $_GET['yr'] . $_GET['mo'] . "01000000 AND " . $_GET['yr'] . $_GET['mo'] . "31235959"
							. " ORDER BY date DESC");
					if (!$result || mysql_num_rows($result) == 0)
					{ ?>
				<li>No entries found</li>
<?php				}
					else
					{
						while ($post = mysql_fetch_array($result))
						{
							$postdate = date("D, M j, Y \\a\\t g:ia", strtotime($post['date']))
							?>
				<li>
					<a href="javascript:void()" onclick="openComments(<?php echo $post['id']; ?>)"><?php echo $post['title'] . "</a> <em>by " . getDisplayName($post['author']) . "</em>"; ?>
					<div class="post_date"><?php echo $postdate; ?> <a href="admin/blog.php?yr=<?php echo $_GET['yr'] . "&mo=" . $_GET['mo'] . "&post=" . $post['id']; ?>&action=delete"><img src="admin/img/delete.png" alt="Delete" title="Delete" align="absmiddle"></a></div>
					<div id="comment<?php echo $post['id']; if ($_GET['post'] == $post['id']) echo "_open"; ?>">
<?php						$commentresult = mysql_query("SELECT * FROM comments WHERE blogpost='" . $post['id'] . "'");
							if ($commentresult)
							{
								if (mysql_num_rows($commentresult) == 0)
								{ ?>
						<div class="comment">No comments</div>
<?php							}
								else
								{
									while ($comment = mysql_fetch_array($commentresult))
									{ ?>
						<div class="commentbtn">
<?php									if ($comment['display'] == 0)
										{ ?>
							<a href="admin/blog.php?yr=<?php echo $_GET['yr'] . "&mo=" . $_GET['mo'] . "&post=" . $post['id'] . "&comment=" . $comment['id']; ?>&action=approve">
<?php									} ?>
								<img src="admin/img/<?php if ($comment['display'] == 0) echo "waiting.png"; else echo "approved.png"; ?>" alt="<?php if ($comment['display'] == 0) echo "Approve"; else echo "Already approved"; ?>" title="<?php if ($comment['display'] == 0) echo "Approve"; else echo "Already approved"; ?>">
<?php									if ($comment['display'] == 0)
										{ ?>
							</a>
<?php									} ?><br>
							<a href="admin/blog.php?yr=<?php echo $_GET['yr'] . "&mo=" . $_GET['mo'] . "&post=" . $post['id'] . "&comment=" . $comment['id']; ?>&action=delete"><img src="admin/img/delete.png" alt="Delete" title="Delete"></a>
						</div>
						<div class="comment<?php if ($comment['display'] == 1) echo "_approved"; ?>">
							<div class="comment_date"><?php echo date("D, M j, Y \\a\\t g:ia", strtotime($comment['date'])); ?></div>
							<div class="comment_author"><?php echo htmlentities($comment['author']); ?></div>
							<div class="comment_content"><?php echo $comment['content']; ?></div>
						</div>
<?php								}
								}
							} ?>
					</div>
				</li>
<?php					}
					}
				}
				else
				{ ?>
				<li>Please select a month to browse from the left.</li>
<?php			} ?>
			</ul>
		</div>
		<div class="clear"></div>
<?php }
	
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
<?php
	} ?>
	</div> <!-- <div class="border"> -->
<?php
	if ($_SESSION['priv'] != PRIV_NONE)
	{ ?>
	<div class="text_center">Welcome, <em><?php echo $_SESSION['username']; ?>.</em></div>
<?php	} ?>
</body>

</html>
