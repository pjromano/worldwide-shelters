<?php
	include("../include/start.php");
	
	// The type of operation that was carried out
	// 0 means no operation
	$operation = 0;
	
	// Result of operation
	// If there was an error, $opresult contains a string with the error message
	$opresult = "";
	
	// Upload image
	if ($_GET['op'] == "upload")
	{
		$operation = "upload";
		
		$success = true;
		foreach ($_FILES['uploadfile']['error'] as $key => $error)
		{
			if ($error == UPLOAD_ERROR_OK)
			{
				if (!is_dir(SITE_MEDIADIRECTORY))
					mkdir(SITE_MEDIADIRECTORY, 0755);
				
				$filename = SITE_MEDIADIRECTORY . basename($_FILES['uploadfile']['name'][$key]);
				if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'][$key], $filename))
					$success = false;
				else
				{
					$result = addImage(basename($filename), $_POST['gallery'], ($_POST['resize'] == "on"), ($_POST['thumb'] == "on"), $_POST['caption'][$key], 0, ($_POST['issidenav'] == "on"), ($_POST['sidenaveffect'] == "on"));
					if ($result < 0)
						$opresult = getErrorString($result);
				}
			}
			else
				$success = false;
		}
		if (!$success)
			$opresult = "One or more of the files was not successfully uploaded.";
	}
	
	else if ($_GET['op'] == "newgallery")
	{
		$operation = "newgallery";
		
		$result = addGallery($_POST['name']);
		if ($result < 0)
			$opresult = getErrorString($result);
	}
	
	else if ($_GET['op'] == "edit")
	{
		$operation = "edit";
		
		if (isset($_GET['img']))
		{
			$result = updateImage($_GET['img'], $_POST['gallery'], ($_POST['resize'] == "on"), ($_POST['thumb'] == "on"), $_POST['caption'], ($_POST['issidenav'] == "on"), ($_POST['sidenaveffect'] == "on"));
			$opresult = getErrorString($result);
		}
		else if (isset($_GET['g']))
		{
			$result = updateGallery($_GET['g'], $_POST['name']);
			$opresult = getErrorString($result);
		}
		else
			$opresult = "An image or gallery to update was not specified.";
	}
	
	else if ($_GET['op'] == "delete")
	{
		$operation = "delete";
		
		if (isset($_GET['img']))
		{
			$result = deleteImage($_GET['img']);
			$opresult = getErrorString($result);
		}
		else if (isset($_GET['g']))
		{
			$result = deleteGallery($_GET['g']);
			$opresult = getErrorString($result);
		}
		else
			$opresult = "An image or gallery ID to delete was not specified.";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<base href="<?php echo SITE_BASE; ?>">
	<title><?php
		echo SITE_TITLE . " - Administration - Images";
	?></title>
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
			<li class="menu_current"><a href="admin/media.php">Media</a></li>
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
	<div class="head2">Media</div>
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
					<td colspan="2">Filename</td>
					<td>Delete</td>
				</tr>
<?php 
		// Figure out the open gallery
		$opengallery = false;
		if (isset($_GET['g']))
			$opengallery = $_GET['g'];
		else if (isset($_GET['img']))
		{
			$openimageresult = mysql_query("SELECT gallery FROM images WHERE id='" . $_GET['img'] . "'");
			if ($openimageresult)
			{
				$openimage = mysql_fetch_array($openimageresult);
				$opengallery = $openimage['gallery'];
			}
		}
		
		$galleryresult = mysql_query("SELECT * FROM galleries ORDER BY name");
		if (!$galleryresult)
		{ ?>
				<tr>
					<td colspan="3">Failed to read galleries from database.</td>
				</tr>
<?php	}
		else
		{
			// Iterate through each gallery
			while ($gallery = mysql_fetch_array($galleryresult))
			{ ?>
				<tr class="section">
					<td class="tree_button"><?php
						echo '<img id="treebutton' . $gallery['id'] . '_';
						if ($opengallery && $gallery['id'] == $opengallery)
							echo 'open';
						echo '" src="admin/img/tree_expand.png" alt="" onclick="toggleTreeSection(' . $gallery['id'] . ')">';
					?></td>
					<td class="title"><a href="admin/media.php?op=view&g=<?php echo $gallery['id']; ?>"><?php echo $gallery['name']; ?></a></td>
					<td class="opbutton"><a href="admin/media.php?action=deleteconfirm&g=<?php echo $gallery['id']; ?>"><img src="admin/img/delete.png" alt="Delete" title="Delete"></a></td>
				</tr>
<?php			
				// Iterate through images within gallery
				$imageresult = mysql_query("SELECT * FROM images WHERE gallery='" . $gallery['id'] . "' AND isthumb!='1' ORDER BY path");
				if (!$imageresult)
				{ ?>
				<tr>
					<td colspan="3">Failed to read images from database.</td>
				</tr>
<?php			}
				else
				{
					if (mysql_num_rows($imageresult) === 0)
					{ ?>
				<tr id="treerow<?php
							echo $gallery['id'] . '_' . $rowid . '_';
							if ($opengallery && $gallery['id'] == $opengallery)
								echo 'open';
				?>">
					<td class="treelist_last"></td>
					<td class="title_indent" colspan="2"><em>No images</em></td>
				</tr>
<?php				}
					else
					{
						$rowid = 0;
						while ($image = mysql_fetch_array($imageresult))
						{ ?>
				<tr id="treerow<?php
							echo $gallery['id'] . '_' . $rowid . '_';
							if ($opengallery && $gallery['id'] == $opengallery)
								echo 'open';
				?>">
					<td class="treelist<?php if ($rowid == mysql_num_rows($imageresult) - 1) echo "_last"; ?>"></td>
					<td class="title_indent"><a href="admin/media.php?op=view&img=<?php echo $image['id']; ?>"><?php echo $image['path']; ?></a></td>
					<td class="opbutton"><a href="admin/media.php?action=deleteconfirm&img=<?php echo $image['id']; ?>"><img src="admin/img/delete.png" alt="Delete" title="Delete"></a></td>
				</tr>
<?php					$rowid++;
						}
					}
				}
			}
		}
		
		$imageresult = mysql_query("SELECT * FROM images WHERE gallery='0' AND isthumb!='1' ORDER BY path");
		if (!$imageresult)
		{ ?>
				<tr>
					<td colspan="3">Failed to read images from database.</td>
				</tr>
<?php	}
		else if (mysql_num_rows($galleryresult) === 0 && mysql_num_rows($imageresult) === 0)
		{ ?>
				<tr>
					<td colspan="3">No images have been uploaded.</td>
				</tr>
<?php	}
		else
		{
			while ($image = mysql_fetch_array($imageresult))
			{ ?>
				<tr>
					<td class="tree_button"></td>
					<td class="title"><a href="admin/media.php?op=view&img=<?php echo $image['id']; ?>"><?php echo $image['path']; ?></a></td>
					<td class="opbutton"><a href="admin/media.php?action=deleteconfirm&img=<?php echo $image['id']; ?>"><img src="admin/img/delete.png" alt="Delete" title="Delete"></a></td>
				</tr>
<?php		}
		} ?>
			</table>
			<div class="text_center"><a href="admin/media.php">Upload</a></div>
		</div>
		<div id="editor_container">
<?php
		/*
			Display editor column
		*/
		
		// Output operation result information
		if ($operation !== 0)
		{
			// Create messages according to operation type
			if ($operation == "upload")
			{
				$opsuccess = "The files were successfully uploaded and added to the database.";
				$opfail = "An error occurred while uploading the file to the server:";
			}
			else if ($operation == "newgallery")
			{
				$opsuccess = "The gallery was successfully added to the database.";
				$opfail = "An error occurred while adding the gallery to the database:";
			}
			else if ($operation == "edit" && isset($_GET['img']))
			{
				$opsuccess = "The image information was successfully updated.";
				$opfail = "An error occurred while updating the image information:";
			}
			else if ($operation == "edit" && isset($_GET['g']))
			{
				$opsuccess = "The gallery name was successfully updated.";
				$opfail = "An error occurred while updating the gallery information:";
			}
			else if ($operation == "delete" && isset($_GET['img']))
			{
				$opsuccess = "The file was successfully deleted.";
				$opfail = "An error occurred while deleting the file from the server:";
			}
			else if ($operation == "delete" && isset($_GET['g']))
			{
				$opsuccess = "The gallery was successfully deleted.";
				$opfail = "An error occurred while deleting the gallery from the database:";
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
		}
		
		// Display confirmation message for deleting image
		if ($_GET['action'] == "deleteconfirm")
		{
			if (isset($_GET['img']))
			{
				$name = getImageProperty($_GET['img'], "path");
				if ($name === ERROR_DB)
				{ ?>
			<div class="message_error_editor">
				An internal database error occurred while accessing the image name.<br>
			</div>
<?php			}
				else
				{ ?>
			<div class="message_editor">
				Are you sure you want to delete image <em><?php echo $name; ?></em>?<br>
				<form method="post" action="admin/media.php?op=delete&img=<?php echo $_GET['img']; ?>">
					<input type="submit" value="Yes">
				</form>
				<form method="post" action="admin/media.php">
					<input type="submit" value="No">
				</form>
			</div>
<?php			}
			}
			else if (isset($_GET['g']))
			{
				$name = getGalleryName($_GET['g']);
				if ($name === ERROR_DB)
				{ ?>
			<div class="message_error_editor">
				An internal database error occurred while accessing the gallery name.<br>
			</div>
<?php			}
				else
				{ ?>
			<div class="message_editor">
				Are you sure you want to delete gallery <em><?php echo $name; ?></em>?<br>
				<form method="post" action="admin/media.php?op=delete&g=<?php echo $_GET['g']; ?>">
					<input type="submit" value="Yes">
				</form>
				<form method="post" action="admin/media.php">
					<input type="submit" value="No">
				</form>
			</div>
<?php			}
			}
			else
			{ ?>
			<div class="message_error_editor">
				An image or gallery ID to delete was not specified.<br>
			</div>
<?php		}
		}
		
		// Only display forms if there was no error in previous operation
		if ($opresult === "")
		{
			if ($_GET['op'] == "view" || $_GET['op'] == "edit")
			{
				// View/edit image information
				if (isset($_GET['img']))
				{
					$imageresult = mysql_query("SELECT * FROM images WHERE id='" . $_GET['img'] . "'");
					if (!$imageresult)
					{ ?>
			<div class="message_error_editor">
				Failed to get the image information from the database.<br>
				You can still update the information using the forms below.
			</div>
<?php				}
					else
					{
						$image = mysql_fetch_array($imageresult);
						$thumbresult = mysql_query("SELECT * FROM images WHERE id='" . $image['image_thumb'] . "'");
						if (!$thumbresult)
						{ ?>
			<div class="message_error_editor">
				Failed to get the thumbnail image from the database.<br>
				You can still update the information using the forms below.
			</div>
<?php					}
						else
						{
							if (mysql_num_rows($thumbresult) == 0)
								$thumb = 0;
							else
								$thumb = mysql_fetch_array($thumbresult);
							
							try {
								$imagedata = new Imagick(SITE_MEDIADIRECTORY . $image['path']);
								$resize = ($imagedata->getImageWidth() > 600 || $imagedata->getImageHeight() > 600);
								$imagedata->destroy();
							} catch (ImagickException $e) { ?>
			<div class="message_error_editor">
				An error occurred opening the file containing the image.
			</div>
<?php						}
						}
					}
					?>
			<div class="head2">Editing Image</div>
			<div class="head_subsection"><em><?php echo $image['path']; ?></em></div>
<?php				$imagetag = getImagePreview($_GET['img']);
					if ($imagetag < 0)
					{ ?>
			<div class="message_error_editor">
				The following error occurred while retrieving the image preview:<br>
				<?php echo getErrorString($imagetag); ?>
			</div>
<?php				}
					else
					{ ?>
			<div class="text_center">
				<?php echo $imagetag; ?><br>
				<?php echo getImageProperty($_GET['img'], "caption"); ?><br>
				<?php
						if ($thumb === 0)
							echo "<em>(No thumbnail)</em>";
						else
						{ ?>
				<a href="javascript:void()" onclick="toggleThumb()">View Thumbnail</a>
				<div class="thumb" id="thumbblock">
					<img src="media/<?php echo $thumb['path']; ?>" alt="<?php echo $thumb['caption']; ?>" title="<?php echo $thumb['caption']; ?>">
				</div>
			</div>
<?php					} ?>
			<div class="separator"></div>
<?php				} ?>
			<form method="post" action="admin/media.php?op=edit&img=<?php echo $_GET['img']; ?>">
				<table class="tableedit">
					<tr>
						<td class="left">Caption</td>
						<td class="right"><input type="text" name="caption" value="<?php echo htmlentities($image['caption']); ?>"></td>
					</tr>
					<tr>
						<td class="left">Resize</td>
						<td class="right"><input type="checkbox" name="resize" id="<?php if (!$resize) echo "no"; ?>resize"<?php if (!$resize) echo " disabled"; ?>></td>
					</tr>
					<tr>
						<td class="left">Thumbnail</td>
						<td class="right"><input type="checkbox" name="thumb" id="thumb"<?php if ($image['image_thumb'] > 0) echo " checked"; ?>></td>
					</tr>
					<tr>
						<td class="left">SideNav Background</td>
						<td class="right"><input type="checkbox" name="issidenav" id="issidenav" onclick="updateSidenavOptions()"<?php if ($image['sidenav'] == 1) echo " checked"; ?>></td>
					</tr>
					<tr id="sidenaveffectrow">
						<td class="left">Apply SideNav Effect?</td>
						<td class="right"><input type="checkbox" name="sidenaveffect"<?php if ($image['sidenav'] == 0) echo " checked"; ?>></td>
					</tr>
					<tr>
						<td class="left">Gallery</td>
						<td class="right">
<?php						$result = mysql_query("SELECT * FROM galleries");
							if (!$result)
							{ ?>
							Failed to retrieve gallery list from database.
<?php						}
							else
							{ ?>
							<select name="gallery" onclick="updateGalleryOptions()">
								<option value="0"<?php if ($image['gallery'] == 0) echo " selected"; ?>>No Gallery
<?php							while ($gallery = mysql_fetch_array($result))
								{ ?>
								<option value="<?php echo $gallery['id']; ?>"<?php if ($image['gallery'] == $gallery['id']) echo " selected"; ?>><?php echo $gallery['name']; ?>
<?php							} ?>
							</select>
<?php						} ?>
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Update"></td>
					</tr>
				</table>
			</form>
<?php			}
				
				// View/edit gallery information
				else if (isset($_GET['g']))
				{
					$galleryresult = mysql_query("SELECT * FROM galleries WHERE id='" . $_GET['g'] . "'");
					if (!$galleryresult)
					{ ?>
			<div class="message_error_editor">
				Failed to get the gallery information from the database.<br>
				You can still update the information using the forms below.
			</div>
<?php				}
					else
					{
						$gallery = mysql_fetch_array($galleryresult);
						?>
			<div class="head2">Gallery <em><?php echo htmlentities($gallery['name']); ?></em></div>
			<form method="post" action="admin/media.php?op=edit&g=<?php echo $_GET['g']; ?>">
				<table class="tableedit">
					<tr>
						<td class="left">Name</td>
						<td class="right"><input type="text" name="name" value="<?php echo htmlentities($gallery['name']); ?>"></td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Update"></td>
					</tr>
				</table>
			</form>
<?php				}
				}
			}
			else
			{ ?>
			<div class="head2">Upload Images</div>
			<form method="post" action="admin/media.php?op=upload" enctype="multipart/form-data">
				<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
				<table class="tableedit">
					<tr>
						<td class="heading" colspan="2">File 1</td>
					</tr>
					<tr id="file1">
						<td class="center" colspan="2"><input type="file" name="uploadfile[]"></td>
					</tr>
					<tr id="file2">
						<td class="center" colspan="2"><a href="javascript:void()" onclick="addFile(2)">Add File 2</a></td>
					</tr>
					<tr>
						<td class="left">Resize</td>
						<td class="right"><input type="checkbox" name="resize" id="resize" checked></td>
					</tr>
					<tr>
						<td class="left">Generate Thumbnail</td>
						<td class="right"><input type="checkbox" name="thumb" id="thumb" checked></td>
					</tr>
					<tr>
						<td class="left">Prepare SideNav Background</td>
						<td class="right"><input type="checkbox" name="issidenav" id="issidenav" onclick="updateSidenavOptions()"></td>
					</tr>
					<tr id="sidenaveffectrow">
						<td class="left">Apply SideNav Effect?</td>
						<td class="right"><input type="checkbox" name="sidenaveffect" checked></td>
					</tr>
					<tr>
						<td class="left">Add Images to Gallery</td>
						<td class="right">
<?php						$result = mysql_query("SELECT * FROM galleries");
							if (!$result)
							{ ?>
							Failed to retrieve gallery list from database.
<?php						}
							else
							{ ?>
							<select name="gallery" onclick="updateGalleryOptions()">
								<option value="0">No Gallery
<?php							while ($gallery = mysql_fetch_array($result))
								{ ?>
								<option value="<?php echo $gallery['id']; ?>"><?php echo $gallery['name']; ?>
<?php							} ?>
							</select>
<?php						} ?>
						</td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Upload"></td>
					</tr>
				</table>
			</form>
			<div class="separator"></div>
			<div class="head2">Create New Gallery</div>
			<form method="post" action="admin/media.php?op=newgallery">
				<table class="tableedit" align="center">
					<tr>
						<td class="left">Name</td>
						<td class="right"><input type="text" name="name"></td>
					</tr>
					<tr>
						<td class="center" colspan="2"><input type="submit" value="Create"></td>
					</tr>
				</table>
			</form>
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
