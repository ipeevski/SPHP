function hide(pic)
{
 	var elements = document.getElementsByTagName('div');
	var flag = (document.getElementById('pic_'+pic).style.display == 'none');
		 
	for (var i = 0; i < elements.length; i++)
		if (elements[i].id != 'navigation')
			elements[i].style.display = 'none';
	
	if (flag)
		document.getElementById('pic_'+pic).style.display='inline';
}
