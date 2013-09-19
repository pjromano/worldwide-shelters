<div id="topnav_container">
	<ul id="topnav_list">
<?php
	$result = mysql_query("SELECT * FROM sections ORDER BY ord, id");
	while ($row = mysql_fetch_array($result))
	{
	?><li class="topnav_item<?php if ($row['link'] == $sectiondata['link']) echo '_on'; ?>"><a href="<?php echo $row['link']; ?>" target="_self"><?php echo $row['displayname']; ?></a></li><?php
	} ?><li class="topnav_item"></li>
	</ul>
</div>

