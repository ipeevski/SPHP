<?php
chdir('../');
$fcontent = file_get_contents(stripslashes($_GET['file']));
//$fcontent = str_replace(array('"',"'","\n","\r"), array('\"',"\'","\\n",''), $fcontent);

if ($_GET['escape'] == 'html') {
	$fcontent = htmlentities($fcontent);
}
//if ($_GET['escape'] == 'javascript')
$fcontent = str_replace(array('\\', '"',"'","\n","\r"), array('\\\\', '\"',"\'","\\n",''), $fcontent);
echo $fcontent;
?>
