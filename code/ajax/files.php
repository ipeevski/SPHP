<?php
session_start();

function filetree($dir) 
{
	$array = array();
	if (is_dir($dir)) {
		$dh = opendir($dir);
		
		while ($file = readdir($dh)) {
			if ($file[0] == '.') {
				//ignore
			} elseif (is_dir($dir . $file)) {
				$array[$dir . $file . DIRECTORY_SEPARATOR] = filetree($dir . $file . DIRECTORY_SEPARATOR);
			} elseif (is_file($dir . $file)) {
				$array[$dir.'.'][] = $file;
			} else {
				echo $dir.$file.' - error?<br />';
			}
		}
		closedir($dh);
	}
	
	return $array;
}

header("Content-Type: application/xhtml+xml;charset=iso-8859-1");
$dir = $_GET['id'] ? stripslashes($_GET['id']) : '\\www\\code\\';
chdir('../');
$files = filetree($dir);
echo '<?xml version="1.0" encoding="iso-8859-1"?>';
?>

<tree id="<?=($_GET['top'] ? 0 : $dir)?>">
<?php
	//print_r($files);

	echo rec($files);

function rec($dirs) {

	$dirs_code = '';
	$files_code = '';
	foreach ($dirs as $dir => $files) {
		if (substr($dir, -1) == '.') {
			foreach ($files as $file) {
				$files_code .= '<item text="'.$file.'" id="'.substr($dir, 0, -1).$file.'" child="0" tooltip="'. basename($file) .'" />';
			}
		} else {
			$dirs_code .= '<item text="'.basename($dir).'" id="'.$dir.'" child="1" tooltip="Directory" />';
			//rec($files);
		}
	}
	
	return $dirs_code . $files_code;
}
?>
</tree>
