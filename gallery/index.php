<?php
/* Distribution - free, but please let me know where it is used 
and credit me as the original author.

Ivan Peevski
10/1/2005
cyberhorse at gmail
(gmail = gmail.com of course)
*/

include('config.php');
//$dir = '/home/user/public_html'; // home directory where images are.
$dir = '.';

include('functions.php');
session_start();
// Setting defaults;
if (!$_SESSION['view'])
  $_SESSION['view'] = 't';
if (!$_SESSION['style'])
  $_SESSION['style'] = 'main.css';
if (!$_SESSION['menu'])
  $_SESSION['menu'] = 'list';

if (empty($_SESSION['images']) || isset($_GET['reload']))
{
session_destroy();
session_start();
ini_set('max_execution_time', 10000000);
   preload();
}
if (isset($_GET['page']))
	$page = $_GET['page'];
else
	$page = 0;
if (!empty($_REQUEST['gallery']))
  $_SESSION['gallery'] = $_REQUEST['gallery'];
if (!empty($_SESSION['gallery']))
  $dir = $_SESSION['gallery'];

$images = & $_SESSION['images'][$dir];
$imgdir =  $dir . '/';
if (isset($_REQUEST['image']))
{
	$image = $_REQUEST['image'];
	$page = floor(($image-1) / $pagesize);
}
else
	$image = '';

$start = $page*$pagesize;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Images</title>
<link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['style']; ?>" />
<script type="text/javascript" language="JavaScript" src="main.js"></script>
</head>
<body>
<?php
if (file_exists($dir.'/gallery.inc.php'))
  include($dir.'/gallery.inc.php');
else
  echo '<h1>' . $dir . '</h1>';
echo '<form action="#" method="post">';
echo '<p style="text-align: center">Style: '; display_conf_styles();
echo ' Menu type: '; display_conf('menu', array('list' => 'list', 'tree' => 'tree'));
echo ' Display type: '; display_conf('view', array('t' => 'tree', 'l' => 'list', 'd' => 'detailed'));
echo '</p></form>';
echo '<h2>galleries:</h2>';
display_menu_galleries();
echo '<h2>pictures:</h2>';
if (count($images) <= 0)
  echo '<p style="text-align: center">no pictures</p>';
else
{
$view = $_SESSION['view'];
echo '<table align="center" cellpadding="10">';
for ($i = $start; $i < $pagesize+$start && $i < count($images); $i++)
{
 	exif_tab($i);
  if ($view == 't')
  {
    if (($i-$start)%4 == 0)
      	echo '<tr valign="bottom">';
  	
    echo '
    <td align="center"';
  	if ($i + 1 == $image)
  		echo ' style="border: 3px solid; border-color: blue"';
  	echo '>
      <a id="'.$i.'" href="'.$imgdir.$images[$i]['name'].'"><img src="'.$imgdir.$prefix.$images[$i]['name'].'" border="0" alt="picture" /></a><br />
  		<hr />
      <span class="highlight">Name:</span> '.$images[$i]['name'].'<br />
      <span class="highlight">Date:</span> '.$images[$i]['date'].'<br />
      <span class="highlight">Resolution:</span> '.$images[$i]['w'].'x'.$images[$i]['h'].'<br />
      <span class="highlight">Size:</span> '.$images[$i]['size'].'<br />
  		<a href="javascript:void(0);" onClick="hide('.$i.')">EXIF info</a>
    </td>';
    if (($i-$start)%4 == 3)
      echo '</tr>';
	}
	else if ($view == 'l')
	{
	 	echo '
	<tr>
		<td';
		if ($i + 1 == $image)
                  echo ' style="border: 3px solid; border-color: blue"';
		echo '>
			<a id="'.$i.'" href="'.$imgdir.$images[$i]['name'].'">
			<img src="'.$imgdir.$prefix.$images[$i]['name'].'" alt="picture" width="100" /></a>
		</td>
		<td>'.$images[$i]['name'].'<br />
		'.$images[$i]['date'].'<br />
		'.$images[$i]['w'].'x'.$images[$i]['h'].'<br />
		'.$images[$i]['size'].'</td>
		<td><a href="javascript:void(0);" onClick="hide('.$i.')">EXIF info</a></td>
	</tr>';
	}
	else if ($view == 'd')
	{
	 	echo '
	<tr>
		<td';
		if ($i + 1 == $image)
                  echo ' style="border: 3px solid; border-color: blue"';
		echo '><a id="'.$i.'" href="'.$imgdir.$images[$i]['name'].'">'.$imgdir.$images[$i]['name'].'</a></td>
		<td>'.$images[$i]['date'].'</td>
		<td>'.$images[$i]['w'].'x'.$images[$i]['h'].'</td>
		<td>'.$images[$i]['size'].'</td>
		<td><a href="javascript:void(0);" onClick="hide('.$i.')">EXIF info</a></td>
	</tr>';
	}
}
  echo '</table>';
}
?>
<br />
<h2>movies:</h2>
<p style="text-align: center">
<?php
  $movies = $_SESSION['movies'][$dir];
  if (is_array($movies))
  {
    echo '<table align="center">';
    foreach($movies as $movie)
      echo '
<tr>
	<td><a href="'.$dir.'/'.$movie['name'].'">'.$movie['name'].'</a></td>
	<td>'.$movie['date'].'
	<td>'.$movie['size'].'
</tr>';
    echo '</table>';
  }
  else
    echo 'no movies';
?>
</p>
<div id="navigation" class="navigation">
<h2>pages:</h2>
<?php if ($page > 0) {?>
<a href="?page=<?=($page-1)?>">&lt;&lt; back</a>
<?php } if ($page > 0 && $page < floor(count($images)/$pagesize)) {?> 
 | 
<?php } if ($page < floor(count($images)/$pagesize)) {?> 
<a href="?page=<?=($page+1)?>">next &gt;&gt;</a>
<?php } ?>
<br />
<?php
$barsize = 10;
$maxpage = floor(count($images)/$pagesize)+1;
$st = ($page <= $barsize/2)?0:($page-$barsize/2);
for ($i = $st; $i < $st+$barsize && $i < $maxpage; $i++)
{
  if ($page == $i)
		echo ($i+1);
	else
	  echo '<a href="?page='.$i.'">'.($i+1).'</a>';
		
	if ($i < $st+$barsize-1 && $i < $maxpage-1)
		echo ' | ';
}
?>

<br /><br />
<form action="#" method="post">
	<input class="navigation" type="field" name="image" size="3" />
	<input class="navigation" type="submit" name="submit" value="Show Pic #" />
</form><br />
<div class="footer">Created by Ivan Peevski (cyberhorse at sourceforge)</div>
</body>
</html>
