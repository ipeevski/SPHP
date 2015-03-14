<?php

$db = 'dotproject';
$user = 'root';
$pass = 'k0H4e65';

$dbConn = mysqli_connect('localhost', $user, $pass);


if ($action == 'backup') {
	$sql = mysql_backup($db, $user, $pass);
	
	$filename = date('Ymd') .'.sql';
	file_put_contents($filename, $sql);
} elseif ($action == 'delete') {
	unlink($_REQUEST['file']);
} elseif ($action == 'revert') {
	$file = $_REQUEST['file'];

	mysql_select_db($dbConn, $db);
	mysqli_query($dbConn, file_get_contents($file));
} elseif ($action == 'upload') {
	$file = $_FILES['file']['tmp_name'];
	
	mysqli_select_db($dbConn, $_REQUEST['db']);
	mysqli_query($dbConn, file_get_contents($file));
}

$backups = glob('*.sql');

$dbs = mysql_dbs();
?>

<a href="?go=dbbackup&action=backup">Backup</a>

<form action="?go=dbbackup" method="post" enctype="multipart/form-data">
	<input type="file" name="file" />
	<select name="db">
		<?php foreach ($dbs as $db): ?>
		<option name="<?php echo $db ?>"><?php echo $db ?></option>
		<?php endforeach ?>
	</select>
	<input type="hidden" name="action" value="upload" />
	<input type="submit" value="Upload" />
</form>

<table>
<tr>
	<th>Date</th>
	<th>Size</th>
	<th>Actions</th>
</tr>

<?php foreach ($backups as $backup): ?>
<tr>
	<td><?php echo $backup ?></td>
	<td><?php echo filesize($backup) ?></td>
	<td>
		<a href="<?php echo $backup ?>">Download</a>
		<a href="?go=dbbackup&action=delete&file=<?php echo $backup ?>">Delete</a>
		<a href="?go=dbbackup&action=revert&file=<?php echo $backup ?>">Revert</a>
</tr>
<?php endforeach ?>
</table>

<?php
function mysql_dbs() {
	global $dbConn; 
	$sql = "SHOW DATABASES";
	$result = mysqli_query($dbConn, $sql);
	$dbs = array();
	while ($row = mysqli_fetch_row($result)) {
		$dbs[] = $row[0];
	}
	mysqli_free_result($result);
	
	return $dbs;
}

function mysql_backup($db) {
	global $dbConn;
	mysqli_select_db($dbConn, $db);
	
	$tables = array();
	$sql = "SHOW TABLES FROM $db";
	$result = mysqli_query($dbConn, $sql);
	while ($row = mysqli_fetch_row($result)) {
		$tables[] = $row[0];
	}
	mysqli_free_result($result);
	
	$ret = '';
	foreach ($tables as $table) {
		$ret .= create_header($db, $table);
		$ret .= get_data($table);
	}
	
	return $ret;
}

function create_header($db, $table) 
{ 
	global $dbConn; 
		$fields = mysqli_list_fields($dbConn, $db, $table); 
		$h = "DROP TABLE IF EXISTS `$table` \n";
		$h .= "CREATE TABLE `$table` ("; 
		 
		for ($i = 0; $i < mysqli_num_fields($fields); $i++) 
		{ 
				$name = mysqli_field_name($fields, $i); 
				$flags = mysqli_field_flags($fields, $i); 
				$len = mysqli_field_len($fields, $i); 
				$type = mysqli_field_type($fields, $i); 

				$h .= "`$name` $type($len) $flags,"; 

				$pkey = '';
				if(strpos($flags, "primary_key")) { 
					$pkey = " PRIMARY KEY (`$name`)"; 
				} 
		} 
		 
		$h = substr($h, 0, -1); 
		$h .= "$pkey) TYPE=MyISAM;\n\n"; 
		return($h); 
} 

function get_data($table) 
{ 
	global $dbConn;
		$d = null; 
		$data = mysqli_query($dbConn, "SELECT * FROM " . $table); 
		 
		while ($cr = mysqli_fetch_array($data, MYSQL_NUM)) 
		{ 
				$d .= "INSERT INTO `" . $table . "` VALUES ("; 

				for($i=0; $i<sizeof($cr); $i++) 
				{ 
						if($cr[$i] == '') { 
								$d .= 'NULL,'; 
						} else { 
								$d .= "'$cr[$i]',"; 
						} 
				} 

				$d = substr($d, 0, strlen($d) - 1); 
				$d .= ");\n"; 
		} 

		return($d); 
} 
