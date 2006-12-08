<?php 
	$f = fopen('counter.txt', 'a');
	fwrite($f, '.');
	fclose($f);
	echo filesize('counter.txt') . ' visits';
?>
