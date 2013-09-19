<?php
	$url = $_SERVER['REQUEST_URI'];
?>

<table width="950" height="27" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="middle">
			<table width="950" height="27" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="30" align="center" valign="middle">&nbsp;</td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td width="70" align="center" valign="middle" class="top_nav"><a href="index.php" target="_self">HOME</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td width="105" align="center" valign="middle" <?php if (strstr($url, "projects") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"><a href="projects.php" target="_self">PROJECTS</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td align="center" valign="middle" <?php if (strstr($url, "transitional") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"><a href="transitional.php" target="_self">TRANSITIONAL SHELTERS</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td width="90" align="center" valign="middle" <?php if (strstr($url, "about") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"><a href="about.php" target="_self">ABOUT US</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td align="center" valign="middle" <?php if (strstr($url, "who_we_help") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"> <a href="who_we_help.php" target="_self">WHO WE HELP</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td align="center" valign="middle" <?php if (strstr($url, "shelters_in_use") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"><a href="shelters_in_use.php" target="_self">SHELTERS IN USE</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td align="center" valign="middle" <?php if (strstr($url, "contact") !== false) echo 'background="images/nav_bg.jpg" '; ?>class="top_nav"><a href="contact.php" target="_self">CONTACT US</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td align="center" valign="middle" class="top_nav"><a href="http://www.kintera.org/autogen/home/default.asp?ievent=420615" target="_self">DONATE</a></td>
					<td width="1" align="center" valign="middle"><img src="images/devider_small.jpg" width="1" height="24" /></td>
					<td width="30" align="center" valign="middle">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

