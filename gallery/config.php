<?php 
$prefix = 'tmb_'; // can be a directory - not tested.
$page_w = 3;
$page_h = 4;
$pagesize = $page_w * $page_h;
$files['xml'] = 'list.xml';
$files['index'] = 'index.php';

$conf_thumbs['resizeAmount'] = 0.5;
$conf_thumbs['maxW'] = 200;
$conf_thumbs['maxH'] = 250;
$conf_maxH = 200;
$conf_quality = 100;

$do_exif = true;
$do_xml = true;

$types['images'] = array('jpg', 'jpeg', 'gif', 'png');
$types['movies'] = array('mpg', 'mpeg', 'mov', 'qt', 'rm', 'avi');
$types['ignore'] = array('.', '..', 'CVS', 'gallery.inc.php', 'list.xml', 'index.php', 'Thumbs.db');
?>
