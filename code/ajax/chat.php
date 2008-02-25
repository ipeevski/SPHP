<?php
if (isset($_GET['person'])) { 
	$filename = '../chat/'.stripslashes($_GET['person']);
} else {
	$filename = '../chat/' . $_SESSION['user'];
}

if (isset($_GET['content'])) {
	$fcontent = date('Y-m-d H:i:s ') . rawurldecode($_GET['content']);
	$fh = fopen($filename, 'a');
	fwrite($fh, "\n" . $fcontent);
	fclose($fh);
}

$dh = opendir('../chat');
while ($file = readdir($dh)) {
	if ($file[0] != '.') {
		$lines = file('../chat/'.$file, FILE_IGNORE_NEW_LINES);
		foreach ($lines as $line) {
			$time = substr($line, 0, 19);
			$line = substr($line, 20);
			$chat[$time][$file] = $line;
		}
	}
}
closedir($dh);
ksort($chat);


$fcontent = '';


foreach ($chat as $time => $person_chat) {
	foreach ($person_chat as $person => $line) {
		$fcontent .= '['.$time.'] ' . $person . '> '.$line . "\n";
	}
}
//$fcontent = file_get_contents($filename);

if ($_GET['escape'] == 'html') {
	$fcontent = htmlentities($fcontent);
}
//if ($_GET['escape'] == 'javascript')
$fcontent = str_replace(array('\\', '"',"'","\n","\r"), array('\\\\', '\"',"\'","\\n",''), $fcontent);
echo $fcontent;
?>
