<?php
function connect()
{
	global $cfg;

	mysql_connect('localhost', $cfg['user'], $cfg['pass']);
	mysql_select_db($cfg['db']);
}

function db_exec($sql)
{
	global $DEBUG;

	$q = mysql_query($sql);
	if ($DEBUG > 5)
		echo '<br><pre>' . $sql . '</pre><br>';
	if ($msg = mysql_error())
	{
		$_SESSION['msg'] .= $sql . "[Caused: $msg]<br>";
		$_SESSION['msgtype'] = 'error';
		return 0;
	}

	return $q;
}

function db_error()
{
	return mysql_error();
}

function db_insert($sql)
{
	connect();
	$q = db_exec($sql);
	return mysql_insert_id();
}

function db_loadList($sql)
{
	if (!isset($sql))
		die("SQL statement not set!");
	connect();
	$q = db_exec($sql);
	$arr = array();
	if ($q && stristr($sql, 'select') != '')
		while ($row=mysql_fetch_array($q))
			$arr[] = $row;

	return $arr;
}

function db_loadHash($sql, $key, $value = '')
{
	$data = db_loadList($sql);
	foreach ($data as $row)
		if ($value == '')
			$temp[$row[$key]] = $row;
		else
			$temp[$row[$key]] = $row[$value];

	return $temp;
}

function db_loadColumn($sql)
{
	if (!isset($sql))
		die("SQL statement not set!");
	connect();
	$q = db_exec($sql);
	$arr = array();
	while ($row=mysql_fetch_array($q))
		$arr[] = $row[0];

	return $arr;
}

function db_loadResult($sql)
{
	$arr = db_loadList($sql);
	if ($arr) {
		return $arr[0][0];
	} else {
		return $arr;
	}
}

?>
