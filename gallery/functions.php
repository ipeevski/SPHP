<?php

function formatsize($size)
{
        if ($size > 1024*1024*1024)
                return round($size / 1024 / 1024 / 1024, 2) . ' Gb';
        if ($size > 1024*1024)
                return round($size / 1024 / 1024, 2) . ' Mb';
        if ($size > 1024)
                return round($size / 1024, 2) . ' Kb';
        return $size . ' B';
}

function resize($file)
{
  global $dir, $prefix, $conf_thumbs;

  $resizeAmount = $conf_thumbs['resizeAmount'];
  $maxW = $conf_thumbs['maxW'];
  $maxH = $conf_thumbs['maxH'];
  $quality = $conf_thumbs['quality'];

  $src = imagecreatefromjpeg($dir.'/'.$file);
  $sw =  imageSX($src);
  $sh =  imageSY($src);
  if ($sw * $resizeAmount > $maxW)
     $resizeAmount = ($maxW/($sw*$resizeAmount)) * $resizeAmount;
  if ($sh * $resizeAmount > $maxH)
     $resizeAmount = ($maxH/($sh*$resizeAmount)) * $resizeAmount;

  $rw = $sw * $resizeAmount;
  $rh = $sh * $resizeAmount;

  $img = imageCreateTrueColor($rw, $rh);
        imageAntialias($img, true);
        imageInterlace($img, 1);

  imageCopyResampled ( $img, $src, 0, 0, 0, 0,
                  $rw, $rh, $sw, $sh);
  if (substr($file, 0, 4) != $prefix)
    imagejpeg ( $img, $dir.'/'.$prefix.$file, $quality );

//  return $img;
}

function preload()
{
  global $dir, $prefix, $files;
  
  $xml_data = '';
  if ($dh = opendir($dir))
  {
    $_SESSION['images'][$dir] = array();
    $_SESSION['movies'][$dir] = array();
    $_SESSION['info'][$dir]['title'] = $dir;

    $size = 0;
    $count = 0;
    while (($file = readdir($dh)) !== false)
    {
//echo '.';
      if (substr($file,0,1) == '.')
        ;
      else if ($file == 'gallery.inc.php')
      {
        $inc = file($dir . '/' . $file);
        $title = stripslashes(substr($inc[1], strpos($inc[1], '<h1>')+4, strpos($inc[1], '</h1>') - strpos($inc[1], '<h1>')));
     	  $_SESSION['info'][$dir]['title'] = $title;
      }
      else if (is_dir($dir.'/'.$file))
      {
        $olddir = $dir;
        $dir = $dir .'/'. $file;
        preload();
        $dir = $olddir;
      }
      else
      {
        $fileinfo = array('name'=>$file,
                          'size'=>formatsize(filesize($dir.'/'.$file)),
                          'date'=>date("d/m/Y H:i", filemtime($dir.'/'.$file)));
        if (stristr(substr($file, -3), 'mpg'))
        {
          $_SESSION['movies'][$dir][] = $fileinfo;
        }
        // skip thumbnails, they are not to be browsed.
      if (!stristr(substr($file, 0, strlen($prefix)), $prefix))
      {
        $xml_data .= '<file name="'.$file.'" size="'.filesize($dir.'/'.$file)."\">\n";
        if (stristr(substr($file, -3), 'jpg'))
        {
            if (!file_exists($dir.'/'.$prefix.$file))
              resize($file);
            $info = getimagesize($dir.'/'.$file);
include_once('exif.php');
global $exif_data, $tags;
exif($file);

            $_SESSION['images'][$dir][] = array_merge($fileinfo, array(
                                          'w'=>$info[0],
                                          'h'=>$info[1],
                                          'bits'=>$info['bits'],
                                          'exif'=>$exif_data));
            $xml_data .= '<exif';
            foreach($exif_data as $tagname => $tag)
              $xml_data .= ' '.str_replace(' ', '_', $tagname) . '="'.$tag.'"'."\n";
            $xml_data .= " />\n";
          }
          $xml_data .= "</file>\n";
        }
      }
    }
    closedir($dh);

    $xml_data = '<gallery dir="'.$dir.'" size="'.$size.'" count="'.$count.'">'."\n" . $xml_data . '</gallery>'; 

    $xml_path = $dir . '/' . $files['xml'];
    if (is_writable($xml_path)) 
    {
      $xml_file = fopen($xml_path, 'w+');
      if (!$xml_file)
        echo 'cant open the file';
      if (fwrite($xml_file, $xml_data) === FALSE)
        echo 'error';
      fclose($xml_file);
    }
    else
      echo 'xml file not writable: ' . $xml_path . '<br />';
    if (is_writable($dir.'/'.$files['index']))
    {
      $index_file = fopen($dir.'/'.$files['index'], 'w+');
      $levels = strlen($dir) - strlen(str_replace('/', '', $dir));
      $backdir = '';
      while ($levels-- > 0)
        $backdir .= '../';
      fwrite($index_file, '<?php header(\'Location: '.$backdir.'index.php?gallery=' . $dir . '\'); ?>');
      fclose($index_file);
    }
  }
}

function exif_tab($i)
{
        global $images;

    echo '<div id="pic'.$i.'" style="max-width: 200px;background-color: lightblue; border: 1px solid; padding: 20px;position: absolute; top: 20px; left: 0px; display:none; text-align: left"><br />';
        echo '<h2>EXIF info</h2>';
        echo '<a style="color: black; background-color: lightblue; border: 1px solid black; position: absolute; top: -15px; left: 220px" href="javascript:void();" onClick="hide('.$i.')">x</a>';
    echo '<span class="highlight">Bits:</span> '.$images[$i]['bits'] . '<br />';
        foreach($images[$i]['exif'] as $key=>$value)
      echo "<span class=\"highlight\">$key:</span> $value<br />";
    echo '</div>';
}

function display_menu_galleries()
{
  global $dir;
  $type = $_SESSION['menu'];
  echo '<p style="text-align: center;">';
  foreach ($_SESSION['images'] as $key=>$val)
    {
//    if (count($val) > 0)
      $title = $_SESSION['info'][$key]['title'];
      if (empty($title))
        $title = substr($key, strrpos($key, '/')+1);
      if ($key == '.')
        echo '[<a href="?gallery='.$key.'">Main ('.count($val).' images)</a>]';
      else if ($type == 'list')
        echo '[<a href="?gallery='.$key.'">'.$title.' ('.count($val).' images)</a>]';
      else if ($type == 'tree')
      {
    if (stristr($dir, $key))
      echo '-->[<a href="?gallery='.$key.'">'.$title.' ('.count($val).' images)</a>]';
    else if (stristr($key, $dir) && strstr(substr($key, strlen($dir)+1), '/') == '')
      echo '<p align="right">[<a href="?gallery='.$key.'">'.$title.' ('.count($val).' images)</a>]</p>';
      }

    }
  echo '</p>';
}

function display_conf($name, $options)
{
$temp = isset($_SESSION[$name])?$_SESSION[$name]:'';
$temp = isset($_POST[$name])?$_POST[$name]:$temp;
$_SESSION[$name] = $temp;

echo '<select name="'.$name.'" onChange="this.form.submit();">';
foreach($options as $key=>$value)
	echo '<option value="'.$key.'"'.(($key == $_SESSION[$name])?' selected':'').'>'.$value.'</option>';
echo '</select>';
}

function display_conf_styles()
{
  $styles = array();
  if ($dh = opendir('.'))
    while (($file = readdir($dh)) !== false)
      if (substr($file, -4) == '.css')
        $styles[$file] = $file;
  display_conf('style', $styles);
  closedir($dh);
}

?>
