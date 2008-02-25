<?php
$filename = stripslashes($_GET['file']);
if (substr($filename, 0, 2) == './' or substr($filename, 0, 3) == '../') {
	$filename = '../' . $filename;
}
$file = $filename;
$fcontent = rawurldecode($_GET['content']);
$fh = fopen($file, 'w');
fwrite($fh, $fcontent);
fclose($fh);

$fcontent = str_replace(array('\\', '"',"'","\n","\r"), array('\\\\', '\"',"\'","\\n",''), $fcontent);
$fcontent = htmlentities($fcontent);
echo $fcontent;
?>
