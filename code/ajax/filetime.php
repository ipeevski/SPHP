<?php
$filename = stripslashes($_GET['file']);
if (substr($filename, 0, 2) == './' or substr($filename, 0, 3) == '../') {
  $filename = '../' . $filename;
}

$fcontent = filemtime($filename);
echo $fcontent;
?>
