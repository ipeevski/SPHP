<?php 
$prefix = 'tmb_'; // can be a directory - not tested.
$pagesize = 12;
$files['xml'] = 'list.xml';
$files['index'] = 'index.php';

$conf_thumbs['resizeAmount'] = 0.1;
$conf_thumbs['maxW'] = 200;
$conf_maxH = 200;
$conf_quality = 80;

$do_exif = true;
$do_xml = true;

$types['images'] = array('jpg', 'jpeg', 'gif', 'png');
$types['movies'] = array('mpg', 'mpeg', 'mov', 'qt', 'rm', 'avi');
$types['ignore'] = array('.', '..', 'CVS', 'gallery.inc.php', 'list.xml', 'index.php', 'Thumbs.db');
?>
