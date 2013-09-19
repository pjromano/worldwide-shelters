$(document).ready(
function()
{
	$('[id^="treerow"]').not('[id$="_open"]').css('display', 'none');
	$('[id^="treebutton"]').filter('[id$="_open"]').attr('src', 'admin/img/tree_collapse.png');
	if ($('[name="stype"]:checked').val() == 0)
	{
		$('#linkurl').attr("disabled", "disabled");
		$('#linkurl').val("home");
	}
	$('[id^="year"]').not('[id$="_open"]').css('display', 'none');
	$('[id^="comment"]').not('[id$="_open"]').css('display', 'none');
	updateURLEnabled();
	updateGalleryOptions();
	updateSidenavOptions();
});

function toggleTreeSection(imageid)
{
	if ($('[id^="treebutton' + imageid + '_"]').attr('src') == 'admin/img/tree_expand.png')
	{
		$('[id^="treerow' + imageid + '_"]').css('display', 'table-row');
		$('[id^="treebutton' + imageid + '_"]').attr('src', 'admin/img/tree_collapse.png');
	}
	else
	{
		$('[id^="treerow' + imageid + '_"]').css('display', 'none');
		$('[id^="treebutton' + imageid + '_"]').attr('src', 'admin/img/tree_expand.png');
	}
}

function toggleHelp(divid)
{
	if ($('#' + divid).css('display') == 'none')
		$('#' + divid).slideDown(300);
	else
		$('#' + divid).slideUp(100);
}

function updateURLEnabled()
{
	if ($('[name="stype"]').size() > 0)
	{
		if ($('[name="stype"]:checked').val() == 0) // SECTIONTYPE_HOME
		{
			$('#linkurl').attr("disabled", "disabled");
			$('#linkurl').val("home");
		}
		else
		{
			$('#linkurl').attr("disabled", "");
			$('#linkurl').val($('#linkorig').val());
		}
	}
}

function addFile(filenumber)
{
	$('#file' + filenumber).before('<tr><td class="heading" colspan="2"><b>File ' + filenumber + '</b></td></tr>');
	$('#file' + filenumber).html('<td class="center" colspan="2"><input type="file" name="uploadfile[]"></td>');
	
	if (filenumber == 10)
		$('#file' + filenumber).after('<tr><td class="center" colspan="2">Maximum Files Reached (10 files)</td></tr>');
	else
		$('#file' + filenumber).after('<tr id="file' + (filenumber + 1) + '"><td class="center" colspan="2"><a href="javascript:void()" onclick="addFile(' + (filenumber + 1) + ')">Add File ' + (filenumber + 1) + '</a></td></tr>');
}

function updateGalleryOptions()
{
	if ($('#issidenav:checked').val() != "on")
	{
		if ($('[name="gallery"] option:selected').val() != 0)
		{
			$('#resize').attr("checked", "checked");
			$('#thumb').attr("checked", "checked");
			$('#resize').attr("disabled", "disabled");
			$('#thumb').attr("disabled", "disabled");
		}
		else
		{
			$('#resize').attr("disabled", "");
			$('#thumb').attr("disabled", "");
		}
	}
}

function updateSidenavOptions()
{
	if ($('#issidenav:checked').val() == "on")
	{
		$('#resize').attr("checked", "checked");
		$('#resize').attr("disabled", "disabled");
		$('#thumb').attr("disabled", "disabled");
		$('#sidenaveffectrow').css("display", "table-row");
	}
	else if ($('[name="gallery"] option:selected').val() == 0)
	{
		$('#resize').attr("disabled", "");
		$('#thumb').attr("disabled", "");
		$('#sidenaveffectrow').css("display", "none");
	}
	else
		$('#sidenaveffectrow').css("display", "none");
}

function toggleThumb()
{
	if ($('#thumbblock').css('display') == 'block')
		$('#thumbblock').slideUp(150);
	else
		$('#thumbblock').slideDown(150);
}

function openArchiveYear(year)
{
	if ($('[id^="year' + year + '"]').css('display') == 'none')
	{
		$('[id^="year"]').slideUp(200);
		$('[id^="year' + year + '"]').slideDown(200);
	}
	else
		$('[id^="year"]').slideUp(200);
}

function openComments(postid)
{
	if ($('[id^="comment' + postid + '"]').css('display') == 'none')
		$('[id^="comment' + postid + '"]').css('display', 'block');
	else
		$('[id^="comment' + postid + '"]').css('display', 'none');
}

function disableEnterKey(e)
{
	var key;     
	if (window.event)
		key = window.event.keyCode; // IE
	else
		key = e.which; // Firefox
	return (key != 13);
}
