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

function file_type($filename)
{
	global $types;
	$ext = substr($filename, strpos($filename, '.') + 1);
	foreach($types as $type=>$extensions)
	if (in_array(strtolower($ext), $extensions))
		return $type;

	return 'other';
}

function resize($file)
{
  global $dir, $prefix, $conf_thumbs;

  $resizeAmount = $conf_thumbs['resizeAmount'];
  $maxW = $conf_thumbs['maxW'];
  $maxH = $conf_thumbs['maxH'];
  $quality = $conf_thumbs['quality'];

	$image_type = image_type($dir.'/'.$file);
	echo $image_type;

	$func = 'imagecreatefrom' . $image_type; 
	$src = $func($dir.'/'.$file);
  	
  $sw =  imageSX($src);
  $sh =  imageSY($src);

  if ($maxW > 0 && $sw * $resizeAmount > $maxW)
     $resizeAmount = ($maxW/($sw*$resizeAmount)) * $resizeAmount;
  if ($maxH > 0 && $sh * $resizeAmount > $maxH)
     $resizeAmount = ($maxH/($sh*$resizeAmount)) * $resizeAmount;

  $rw = $sw * $resizeAmount;
  $rh = $sh * $resizeAmount;

  if ($image_type == 'jpeg')
  	$img = imageCreateTrueColor($rw, $rh);
  else
  	$img = imageCreate($rw, $rh);

	imageAntialias($img, true);
	imageInterlace($img, 1);

  imageCopyResampled ( $img, $src, 0, 0, 0, 0,
                  $rw, $rh, $sw, $sh);
  if (substr($file, 0, 4) != $prefix)
  {
		$func = 'image' . $image_type; 
	  $func( $img, $dir.'/'.$prefix.$file, $quality );
  }
//  return $img;
}

function image_type($filename)
{
	$ext = substr($filename, strrpos($filename, '.') + 1);
	$ext = strtolower($ext);
	if ($ext == 'jpg' or $ext == 'jpeg')
		return 'jpeg';
	elseif ($ext == 'gif')
		return 'gif';
	elseif ($ext == 'png')
		return 'png';
	else
		return false;
}

function preload()
{
  global $dir, $prefix, $files, $types, $do_exif, $do_xml;
	global $totalcount;
  
  $xml_data = '';
  if ($dh = opendir($dir))
  {
    $_SESSION['images'][$dir] = array();
    $_SESSION['movies'][$dir] = array();
    $_SESSION['info'][$dir]['title'] = $dir;
		$new_files = true;

		if ($do_xml)
		{
			include_once('xml.php');
			$xml_info = xml_parser($dir . '/' . $files['xml']);
			$xml_info = $xml_info[0];

			if (!empty($xml_info))
			{
				if (isset($xml_info['attrs']))
  				$_SESSION['info'][$dir]['title'] = $xml_info['attrs']['NAME'];
  
				if (isset($xml_info['children']))
  			foreach($xml_info['children'] as $xmlfile)
  				$_SESSION[file_type($xmlfile['attrs']['NAME'])][$dir][$xmlfile['attrs']['NAME']] = xml_fileinfo($xmlfile);

				$new_files = false;
			}
		}
		
		if (file_exists($dir . '/gallery.inc.php'))
		{
			$inc = file($dir . '/gallery.inc.php');
			$_SESSION['info'][$dir]['title'] = stripslashes(substr($inc[1], strpos($inc[1], '<h1>')+4, strpos($inc[1], '</h1>') - strpos($inc[1], '<h1>')));
		}

    $size = 0;
    $count = 0;
    while (($file = readdir($dh)) !== false)
    {
			$exif_data = array();
echo '.';
if (++$totalcount % 100 == 1)
	echo '<br />';
//echo $_SESSION['images'][$dir][$file];
flush();
      // skip program files and thumbnails, they are not to be browsed.
      if (in_array($file,$types['ignore']) || (stristr(substr($file, 0, strlen($prefix)), $prefix)))
        ;
      else if (is_dir($dir.'/'.$file))
      {
        $olddir = $dir;
        $dir = $dir .'/'. $file;
        preload();
        $dir = $olddir;
      }
			else if (isset($_SESSION['images'][$dir][$file]) || !empty($_SESSION['movies'][$dir][$file]))
				; // file found in xml
      else
      {
        $fileinfo = array('name'=>$file,
                          'size'=>formatsize(filesize($dir.'/'.$file)),
                          'date'=>filemtime($dir.'/'.$file),
													'type'=>file_type($file)); 

				if ($do_xml && image_type($file))
	        $xml_data .= '<file 
	name="'.$file.'" 
	size="'.filesize($dir.'/'.$file).'"
  date="'.filemtime($dir.'/'.$file).'"
	type="'.file_type($file)."\">\n";

        if (file_type($file) == 'movies')
        {
					if (!isset($_SESSION['movies'][$dir][$file]))
					{
						$new_files = true;
	          $_SESSION['movies'][$dir][$file] = $fileinfo;
					}
        }
        if (file_type($file) == 'images')
        {
					if (isset($_SESSION['images'][$dir][$file]))
						continue;
					else
						$new_files = true;
  
          if (!file_exists($dir.'/'.$prefix.$file))
            resize($file);
          $info = getimagesize($dir.'/'.$file);

          $fileinfo = array_merge($fileinfo, array(
                                          'w'=>$info[0],
                                          'h'=>$info[1],
                                          'bits'=>$info['bits']));

					if ($do_xml && image_type($file))
						$xml_data .= '<dimenstions 
	w="'.$info[0].'"
	h="'.$info[1].'"
	bits="'.$info['bits'].'" />
';
  
  				if ($do_exif)
  				{
            include_once('exif.php');
            global $exif_data, $exif_tags;
            exif($file);
  
  					$fileinfo = array_merge($fileinfo, array('exif'=>$exif_data));
  				
  					if ($do_xml && $exif_data)
  					{
                $xml_data .= '<exif';
                foreach($exif_data as $tagname => $tag)
                  $xml_data .= ' '.str_replace(' ', '_', $tagname) . '="'.$tag.'"'."\n";
                $xml_data .= " />\n";
  					}
  				}
  
  				$_SESSION['images'][$dir][$file] = $fileinfo;
        }
				if ($do_xml && image_type($file))
		      $xml_data .= "</file>\n";
      }
    }
    closedir($dh);

		foreach($types as $type=>$content)
		{
			if ($type != 'ignore' && isset($_SESSION[$type]))
			{
				$count += count($_SESSION[$type]);
				foreach($_SESSION[$type][$dir] as $tfile)
					$size += $tfile['size'];
			}
		}

		if ($do_xml && $new_files)
		{
       $xml_data = '<gallery 
	dir="'.$dir.'"
	name="'.htmlentities(strip_tags($_SESSION['info'][$dir]['title'])).'" 
	size="'.$size.'" 
	count="'.$count.'">
' . $xml_data . '</gallery>'; 
  
      $xml_path = $dir . '/' . $files['xml'];
      $xml_file = fopen($xml_path, 'w');
      if (!$xml_file)
        echo 'cant open the file';
      if (fwrite($xml_file, $xml_data) === FALSE)
        echo 'error';
      fclose($xml_file);
		}

    if ($dir != '.')
		{
			$index_file = fopen($dir.'/'.$files['index'], 'w+');
			if ($index_file)
			{
    	  $levels = strlen($dir) - strlen(str_replace('/', '', $dir));
      	$backdir = '';
	      while ($levels-- > 0)
  	      $backdir .= '../';
	      fwrite($index_file, '<?php header(\'Location: '.$backdir.'index.php?gallery=' . $dir . '\'); ?>');
  	    fclose($index_file);
    	}
			else
				echo 'protective index file not writable: ' . $dir . '/' . $files['index'] . '<br />';
		}
  }
}

function exif_tab($i)
{
		global $images;

	  echo '
<div id="pic_'.$i.'" style="max-width: 200px;background-color: lightblue; border: 1px solid; padding: 20px;position: absolute; top: 20px; left: 0px; display:none; text-align: left" onClick="hide_pic(\''.$i.'\')"><br />
';
        echo '<h2>EXIF info</h2>
';
        echo '<a style="color: black; background-color: lightblue; border: 1px solid black; position: absolute; top: -15px; left: 220px" href="javascript:void(0);" onClick="hide_pic(\''.$i.'\')">x</a>';
    echo '<span class="highlight">Bits:</span> '.$images[$i]['bits'] . '<br />';
        if ($images[$i]['exif'])
        {
        	foreach($images[$i]['exif'] as $key=>$value)
      			echo "<span class=\"highlight\">$key:</span> $value<br />";
        }
        else
        	echo 'more exif info not available';
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
			if (!empty($_SESSION['info'][$key]))
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
