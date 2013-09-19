$(document).ready(function(){
	$('[id^="year"]').not('[id$="_open"]').css('display', 'none');
	
	$('#loginname').css('color', '#BBB');
	$('#loginpass').css('color', '#BBB');
	$('#loginname').val('Username');
	$('#loginpass').val('Password');
	
	$('#loginname').focus(function(){
		if ($(this).css('color').toUpperCase() == '#BBB' || $(this).css('color') == 'rgb(187, 187, 187)')
		{
			$(this).css('color', '#000');
			$(this).val('');
		}
	});
	
	$('#loginpass').focus(function(){
		if ($(this).css('color').toUpperCase() == '#BBB' || $(this).css('color') == 'rgb(187, 187, 187)')
		{
			$(this).css('color', '');
			$(this).val('');
		}
	});
	
	$('#loginname').blur(function(){
		if ($(this).val() == '')
		{
			$(this).css('color', '#BBB');
			$(this).val('Username');
		}
	});
	
	$('#loginpass').blur(function(){
		if ($(this).val() == '')
		{
			$(this).css('color', '#BBB');
			$(this).val('Password');
		}
	});
});

function openNewPost(divid, btnid)
{
	if ($('#' + divid).css('display') == 'none')
	{
		$('#' + divid).slideDown(250);
		$('#' + btnid).slideUp(250);
	}
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

function addFile(filenumber)
{
	$('#file' + filenumber).html('File ' + filenumber + ': <input type="file" name="uploadfile[]">');
	
	if (filenumber == 10)
		$('#file' + filenumber).after('Maximum Files Reached (10 files)<br>');
	else
		$('#file' + filenumber).after('<div id="file' + (filenumber + 1) + '"><a href="javascript:void()" onclick="addFile(' + (filenumber + 1) + ')">Add File ' + (filenumber + 1) + '</a></div>');
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
