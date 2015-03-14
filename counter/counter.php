<?php 
	$filename = 'counter.txt';

	$count = file_get_contents($filename);
	++$count;
	file_put_contents($filename, $count);
	echo $count . ' visits';

