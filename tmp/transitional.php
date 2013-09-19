<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<base href="http://www.worldwideshelters.org/" />
<title>Worldwide Shelters - Transitional Shelters</title>
<link href="com/css/global.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="com/js/jquery.js"></script>
<script type="text/javascript" src="com/js/jquery.mousewheel.js"></script>
<script type="text/javascript" src="com/js/jquery.fancybox.js"></script>
<link rel="stylesheet" type="text/css" href="com/css/fancybox.css" media="screen" />
<script type="text/javascript">
$(document).ready(function() {

	/* Using custom settings */
	
	$("a#inline").fancybox({
		'hideOnContentClick': true
	});

	/* Apply fancybox to multiple items */
	
	$("a.transitional").fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false,
		'titlePosition' 	: 'over',
		'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
			return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
		}
	});
	
});
</script>
</head>
<body onload="MM_preloadImages('images/tent1_over.jpg','images/tent2_over.jpg','images/tent3_over.jpg')">
<table width="950" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td align="center" valign="top"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="950" height="130" align="top" title="">
        <param name="movie" value="Flash/header_main.swf" />
        <param name="quality" value="high" />
        <param name="BGCOLOR" value="0" />
        <embed src="Flash/header_main.swf" width="950" height="130" align="top" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" bgcolor="0"></embed>
      </object></td>
  </tr>
  <tr>
	  <td height="27" align="left" valign="top">
	  	<?php include('include/menu.php'); ?>
	  </td>
  </tr>
  <tr>
    <td><table width="950" border="0" align="center" cellpadding="0" cellspacing="0">
        <tr>
          <td width="300%" colspan="3" align="left" valign="middle"><table width="950" height="281" border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="35%" align="left" valign="top">
                <div class="side_nav_container">
                <table width="300" height="600" border="0" cellpadding="0" cellspacing="0" background="images/side_nav_girl.jpg">
                    <tr>
                      <td height="50" align="left" valign="top">&nbsp;</td>
                      <td align="left" valign="top">&nbsp;</td>
                    </tr>
                  </table>
                  </div>
                  </td>
                <td width="70%" align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                      <td height="50">&nbsp;</td>
                    </tr>
                    <tr>
							<td align="left" valign="top" class="body_copy"><span class="header_small">Transitional Shelters</span>
                      	<br /><br />
                        Worldwide Shelters' transitional shelters can serve as basic accommodations in the immediate wake of a disaster (when transport capacity is at a premium) and later become the frame for a more permanent home. The phase to permanence can be accomplished using local resources and with eventual homeowner assistance.<br /><br />
                        <table width="100%" border="0" cellspacing="10px" cellpadding="0">
                        	<tr>
                        		<td width="33%" align="center" valign="top">
                        			<a title="Steel frame" rel="transitional_1" class="transitional" href="images/transitional_steelframe.jpg"><img src="images/transitional_steelframe_sm.jpg" /></a>
                        			<div class="caption">Steel frame</div>
                        		</td>
                        		<td width="33%"  align="center" valign="top">
                        			<a title="With tarp" rel="transitional_1" class="transitional" href="images/programs.jpg"><img src="images/programs_sm.jpg" /></a>
                        			<div class="caption">With tarp</div>
                        		</td>
                        		<td width="33%"  align="center" valign="top">
                        			<a title="Fully transitioned" rel="transitional_1" class="transitional" href="images/transitional_medair.jpg"><img src="images/transitional_medair_sm.jpg" /></a>
                        			<div class="caption">Fully transitioned using local resources</div>
                        		</td>
                        	</tr>
                        	<tr>
                        		<td width="33%"  align="center" valign="top">
                        			<a title="Curtain" rel="transitional_1" class="transitional" href="images/transitional_curtain.jpg"><img src="images/transitional_curtain_sm.jpg" /></a>
                        			<div class="caption">Curtain</div>
                        		</td>
                        		<td width="33%"  align="center" valign="top">
                        			<a title="Roof line" rel="transitional_1" class="transitional" href="images/transitional_roofline.jpg"><img src="images/transitional_roofline_sm.jpg" /></a>
                        			<div class="caption">Roof line</div>
                        		</td>
                        		<td width="33%"></td>
                        	</tr>
                        </table>
							</td>
                    </tr>
                  </table></td>
              </tr>
            </table></td>
        </tr>
        <tr>
          <td height="20" colspan="3" align="center" valign="top">&nbsp;</td>
        </tr>
        <tr>
          <td colspan="3"><div align="center"><img src="images/footer.jpg" width="890" height="29" align="top" /></div></td>
        </tr>
        <tr>
         <?php include('include/footer.php'); ?>
        </tr>
      </table></td>
  </tr>
</table>
</body>
</html>
