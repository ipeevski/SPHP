<?php 
	$filename = 'counter.txt';
	$f = fopen($filename, 'a');
	fwrite($f, '.');
	fclose($f);
	echo filesize($filename) . ' visits';
