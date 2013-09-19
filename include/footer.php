<div id="bottomnav_container">
	<div id="bottomnav_separator"></div>
	<ul id="bottomnav_list">
<?php
	$result = mysql_query("SELECT * FROM sections ORDER BY ord, id");
	$numrows = mysql_num_rows($result);
	
	for ($i = 0; $i < $numrows - 1 && $row = mysql_fetch_array($result); $i++)
	{
	?><li class="bottomnav_item"><a href="<?php echo $row['link']; ?>"><?php echo $row['displayname']; ?></a></li><?php
	}
	
	$row = mysql_fetch_array($result);
	?><li class="bottomnav_item_last"><a href="<?php echo $row['link']; ?>"><?php echo $row['displayname']; ?></a></li>
	</ul>
</div>

<div class="copyright">All times on this website are in U.S. EST, unless otherwise noted.<br>Copyright &copy; <?php echo date("Y"); ?> Worldwide Shelters </div>
