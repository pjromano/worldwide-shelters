<?php
	/*
		Image Database/Upload functions
	*/
	
	// Returns the value of the property of the given ID from the images table
	// Returns ERROR_DB if a database error occurs
	function getImageProperty($id, $property)
	{
		$result = mysql_query("SELECT $property FROM images WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		$image = mysql_fetch_array($result);
		
		if (is_string($image[$property]))
			return htmlentities($image[$property]);
		return $image[$property];
	}
	
	// Returns a string holding an HTML img tag with the temporary image preview
	// On error, returns an error code defined in start.php
	function getImagePreview($id)
	{
		$result = mysql_query("SELECT * FROM images WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		
		$imagerow = mysql_fetch_array($result);
		$filepath = SITE_MEDIADIRECTORY . $imagerow['path'];
		
		if (!is_file($filepath))
			return ERROR_NOFILE;
		
		try {
			$image = new Imagick($filepath);
		} catch (ImagickException $e) {
			return ERROR_OPENIMAGE;
		}
		
		if ($image->getImageWidth() > 450)
			$image->scaleImage(450, 0);
		
		if (!$image->writeImage(SITE_TOPDIRECTORY . "admin/img/preview.jpg"))
			return ERROR_WRITEFILE;
		$image->destroy();
		
		return '<a href="media/' . $imagerow['path'] . '"><img src="admin/img/preview.php" alt="' . htmlentities($imagerow['caption']) . '"></a>';
	}
	
	// Returns true if an image exists with the given file name ('path' field in table), or false otherwise
	function getImageExists($filename)
	{
		$result = mysql_query("SELECT * FROM images WHERE path='" . mysql_real_escape_string($filename) . "'");
		if (!$result)
			return ERROR_DB;
		
		if (mysql_num_rows($result) == 0)
			return false;
		return true;
	}
	
	// Returns true if a gallery exists with the given name, or false otherwise
	// On error, returns ERROR_DB
	function getGalleryExists($name)
	{
		$result = mysql_query("SELECT * FROM galleries WHERE name='" . mysql_real_escape_string($name) . "'");
		if (!$result)
			return ERROR_DB;
		
		if (mysql_num_rows($result) === 0)
			return false;
		return true;
	}
	
	// Returns the name of the gallery with given ID
	// Returns ERROR_DB if a database error occurs
	function getGalleryName($id)
	{
		$result = mysql_query("SELECT name FROM galleries WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		$gallery = mysql_fetch_array($result);
		return $gallery['name'];
	}
	
	// Add an image file to the database (use when uploading an image)
	// 	$file			- path to file (path must be relative to "media" folder to work properly!)
	//		$gallery		- ID of gallery of which this is part. 0 means not part of any gallery
	//								If image is part of a gallery, then it will always be resized and thumbnailed
	//		$resize		- if true, original image will be resized to a moderate size
	//								(max width and height of 600px)
	//		$makethumb	- if true, a thumbnail of the image will be created, saved, and added to the database (width and height of 100px)
	//		$caption		- image caption; blank means no caption
	//		$isthumb		- Not to be used externally - if 1 (true), image will be flagged as thumbnail in database
	//		$sidenav		- if true, original image will be sized and bordered for use as a SideNav background
	//								This image effect overrides the other two effects ($resize and $makethumb)
	//		$sidenaveffect - if true and making SideNav background, composite effects will be applied
	// If image already exists in database, then the old image will be overwritten
	// If any of the effects (resize, thumbnail, or sidenav) fail to be applied, then the original image will still try to be added
	// Returns the ID of the newly added image
	// On error, returns on of the ERROR_ADDIMG_* error codes defined in start.php, or ERROR_DB for a database error
	function addImage($file, $gallery, $resize, $makethumb, $caption, $isthumb = 0, $sidenav = false, $sidenaveffect = true)
	{
		// Images are always resized and thumbed as part of a gallery,
		if ($gallery > 0 && !$isthumb && !$sidenav)
		{
			$resize = true;
			$makethumb = true;
		}
		
		$filepath = SITE_MEDIADIRECTORY . $file;
		if (!is_file($filepath))
			return ERROR_ADDIMG_NOFILE;
		
		// Apply any effects necessary
		// sidenav option overrides the other options
		$thumbnail = 0;
		if ($sidenav)
		{
			$isthumb = 0;
			$result = makeSidenavBackground($filepath, $sidenaveffect);
			if ($result < 0)
				return $result;
		}
		else
		{
			if ($resize)
			{
				$result = resizeImage($filepath);
				if ($result < 0)
					return $result;
			}
			
			if ($makethumb)
			{
				$result = makeThumbnail($filepath);
				if ($result < 0)
					return $result;
				
				$thumbnail = addImage("thumb/" . $file, $gallery, false, false, $caption, 1);
				if ($thumbnail < 0)
					return $thumbnail;
			}
		}
		
		// First check if image is already in database
		$result = mysql_query("SELECT * FROM images WHERE path='$file'");
		if ($result && mysql_num_rows($result) == 0)
		{
			echo $gallery;
			$result = mysql_query("INSERT INTO images (path, gallery, image_thumb, caption, isthumb, sidenav) VALUES ('"
					. mysql_real_escape_string($file) . "', '$gallery', '$thumbnail', '"
					. mysql_real_escape_string($caption) . "', '$isthumb', '$sidenav')");
			if (!$result)
				return ERROR_DB;
			return mysql_insert_id();
		}
		else
		{
			$image = mysql_fetch_array($result);
			$result = mysql_query("UPDATE images SET gallery='$gallery', "
					. "caption='" . mysql_real_escape_string($caption) . "', "
					. "sidenav='$sidenav' WHERE id='" . $image['id'] . "'");
			if (!$result)
				return ERROR_DB;
			return $image['id'];
		}
	}
	
	// Resizes the image provided
	// The file is overwritten with the new image
	// 	$imagefile - full path name to image file (e.g. "/home/myusername/public_html/image.jpg")
	// Returns 0 on success, or one of the error codes
	function resizeImage($imagefile)
	{
		try {
			$image = new Imagick($imagefile);
		} catch (ImagickException $e) {
			return ERROR_ADDIMG_RESIZE;
		}
		
		// If image is wide, shrink width to 600
		if ($image->getImageWidth() > 600 && $image->getImageWidth() > $image->getImageHeight())
			$image->thumbnailImage(600, 0);
		// If image is tall, shrink height to 600
		else if ($image->getImageHeight() > 600)
			$image->thumbnailImage(0, 600);
		
		// Don't process or save anything if image
		// is smaller than 600 in both directions
		else
		{
			$image->destroy();
			return 0;
		}
		
		if (!$image->writeImage($imagefile))
			return ERROR_WRITEFILE;
		$image->destroy();
		
		return 0;
	}
	
	// Resizes the image provided for use on blog (smaller images)
	// The file is overwritten with the new image
	// 	$imagefile - full path name to image file (e.g. "/home/myusername/public_html/image.jpg")
	// Returns 0 on success, or one of the error codes
	function resizeImageBlog($imagefile)
	{
		try {
			$image = new Imagick($imagefile);
		} catch (ImagickException $e) {
			return ERROR_ADDIMG_RESIZE;
		}
		
		// If image is wide, shrink width to 600
		if ($image->getImageWidth() > 450 && $image->getImageWidth() > $image->getImageHeight())
			$image->thumbnailImage(450, 0);
		// If image is tall, shrink height to 600
		else if ($image->getImageHeight() > 450)
			$image->thumbnailImage(0, 450);
		
		// Don't process or save anything if image
		// is smaller than 450 in both directions
		else
		{
			$image->destroy();
			return 0;
		}
		
		if (!$image->writeImage($imagefile))
			return ERROR_WRITEFILE;
		$image->destroy();
		
		return 0;
	}
	
	// Opens the image provided and creates a thumbnail.
	// The thumbnail is saved to a file of the same filename as the original,
	// but under a subdirectory at the same level named "thumb"
	//		$imagefile - full path name to image file (e.g. "/home/myusername/public_html/image.jpg")
	// Returns 0 on success, or one of the error codes
	function makeThumbnail($imagefile)
	{
		try {
			$image = new Imagick($imagefile);
		} catch (ImagickException $e) {
			return ERROR_ADDIMG_MAKETHUMB;
		}
		
		// If image is square, simply scale down to 100
		if ($image->getImageWidth() == $image->getImageHeight() && $image->getImageWidth() > 100)
			$image->thumbnailImage(100, 100);
		
		// If image is tall, scale width to 100 and crop top and bottom
		else if ($image->getImageHeight() > $image->getImageWidth())
		{
			$image->thumbnailImage(100, 0);
			$image->cropImage(100, 100, 0, ($image->getImageHeight() - 100) / 2);
		}
		
		// If image is wide, scale height to 100 and crop sides
		else
		{
			$image->thumbnailImage(0, 100);
			$image->cropImage(100, 100, ($image->getImageWidth() - 100) / 2, 0);
		}
		
		$thumbpath = dirname($imagefile) . "/thumb/";
		if (!is_dir($thumbpath))
			if (!mkdir($thumbpath, 0755))
				return ERROR_ADDIMG_MAKETHUMB;
		
		if (!$image->writeImage($thumbpath . basename($imagefile)))
			return ERROR_WRITEFILE;
		$image->destroy();
		
		return 0;
	}
	
	// Resizes and applies border effects to the provided image so it can be used as a background for the SideNav
	// The processed image is saved over the original file
	//		$imagefile		- full path name to image file (e.g. "/home/myusername/public_html/image.jpg")
	//		$applyeffect	- if true, composite effects will be applied for border/washout
	// Returns 0 on success, or one of the error codes
	function makeSidenavBackground($imagefile, $applyeffect)
	{
		try {
			$image = new Imagick($imagefile);
		} catch (ImagickException $e) {
			return ERROR_ADDIMG_APPLYEFFECT;
		}
		
		if ($applyeffect)
		{
			// Open overlay image
			try {
				$overlay = new Imagick(SITE_TOPDIRECTORY . "admin/img/sidenav_overlay.png");
			} catch (ImagickException $e) {
				return ERROR_ADDIMG_APPLYEFFECT;
			}
		}
		
		if ($image->getImageWidth() >= 270 && $image->getImageHeight() >= 575)
		{
			if ($image->getImageWidth() > 275 || $image->getImageHeight() > 575)
			{
				// Shrink to height proportionally and crop sides
				$image->thumbnailImage(0, 575);
				$image->cropImage(275, 575, ($image->getImageWidth() - 100) / 2, 0);
			}
			
			// Overlay effects on image
			if ($applyeffect)
				$image->compositeImage($overlay, imagick::COMPOSITE_ATOP, 0, 0);
		}
		else
		{
			$image->destroy();
			if ($overlay)
				$overlay->destroy();
			return ERROR_ADDIMG_TOOSMALL;
		}
		
		if (!$image->writeImage($imagefile))
			return ERROR_WRITEFILE;
		$image->destroy();
		if ($overlay)
			$overlay->destroy();
	}
	
	// Add a new gallery with name
	// Returns new gallery ID on success, or an error code defined in start.php
	function addGallery($name)
	{
		if ($name == "")
			return ERROR_ADDGALLERY_NONAME;
		
		if (!getGalleryExists($name))
		{
			$result = mysql_query("INSERT INTO galleries (name) VALUES ('" . mysql_real_escape_string($name) . "')");
			if (!$result)
				return ERROR_DB;
			return mysql_insert_id();
		}
		else
			return ERROR_ADDGALLERY_INUSE;
	}
	
	// Update image information
	// See addImage() for parameter specifications
	// Returns 0 on success, or one of the error codes defined in start.php
	function updateImage($id, $gallery, $resize, $makethumb, $caption, $sidenav, $sidenaveffect)
	{
		if ($gallery > 0 && !$isthumb && !$sidenav)
		{
			$resize = true;
			$makethumb = true;
		}
		
		$result = mysql_query("SELECT * FROM images WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		$image = mysql_fetch_array($result);
		
		// Apply effects
		$thumbnail = $image['image_thumb'];
		
		// Delete thumb if we don't need it anymore but there used to be one
		if (($sidenav || !$makethumb) && $image['image_thumb'] > 0)
		{
			$result = deleteImage($image['image_thumb']);
			if ($result < 0)
				return $result;
			$thumbnail = 0;
		}
		
		if ($sidenav)
		{
			$result = makeSidenavBackground(SITE_MEDIADIRECTORY . $image['path'], $sidenaveffect);
			if ($result < 0)
				return $result;
		}
		else
		{
			if ($resize)
			{
				$result = resizeImage(SITE_MEDIADIRECTORY . $image['path']);
				if ($result < 0)
					return $result;
			}
			
			// Create thumbnail
			if ($makethumb && $thumbnail == 0 && !$image['isthumb'])
			{
				$result = makeThumbnail(SITE_MEDIADIRECTORY . $image['path']);
				if ($result < 0)
					return $result;
				
				$thumbnail = addImage("thumb/" . $image['path'], $gallery, false, false, $caption, 1);
				if ($thumbnail < 0)
					return $thumbnail;
			}
		}
		
		$result = mysql_query("UPDATE images SET gallery='$gallery', image_thumb='$thumbnail', isthumb='" . $image['isthumb'] . "', caption='"
				. mysql_real_escape_string($caption) . "', sidenav='$sidenav' WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		return 0;
	}
	
	// Update gallery information
	// Returns 0 on success, or ERROR_DB on error
	function updateGallery($id, $name)
	{
		$result = mysql_query("UPDATE galleries SET name='" . mysql_real_escape_string($name) . "' WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		return 0;
	}
	
	// Delete an image from the database
	// Also deletes the related file in the filesystem
	// Returns 0 on success, or one of the error codes defined in start.php
	function deleteImage($id)
	{
		$result = mysql_query("SELECT * FROM images WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		$image = mysql_fetch_array($result);
		
		// If this image has a thumbnail associated with it, delete that thumbnail first
		if ($image['image_thumb'] > 0)
		{
			$result = deleteImage($image['image_thumb']);
			if ($result < 0)
				return $result;
		}
		
		if (is_file(SITE_MEDIADIRECTORY . $image['path']) && !unlink(SITE_MEDIADIRECTORY . $image['path']))
			return ERROR_DELETEFILE;
		
		$result = mysql_query("DELETE FROM images WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		
		return 0;
	}
	
	// Delete a gallery and all associated images
	// Returns 0 on success, or one of the error codes defined in start.php
	function deleteGallery($id)
	{
		$result = mysql_query("SELECT id FROM images WHERE gallery='$id'");
		if (!$result)
			return ERROR_DB;
		
		$success = true;
		while ($image = mysql_fetch_array($result))
			$success = $success && (deleteImage($image['id']) == 0);
		
		if (!$success)
			return ERROR_DELETEIMAGES;
		
		$result = mysql_query("DELETE FROM galleries WHERE id='$id'");
		if (!$result)
			return ERROR_DB;
		return 0;
	}
?>
