<?php
/**
 * Created on Feb 23, 2006
 *
 * @author cyberhorse
 * @version 0.1
 */

// $password = 'passwd'); // Enable password
session_start();

date_default_timezone_set('Australia/Adelaide');

$history = true;
$date_format = 'd/m/y';
$priorities = array(
	'0' => 'low',
	'1' => 'normal',
	'2' => 'high',
	'3' => 'critical',
	'-1' => 'waiting');
$recuring = array(
	'' => '',
	'1 days' => 'Daily',
	'1 weeks' => 'Weekly',
	'2 weeks' => 'Bi-Weekly',
	'1 months' => 'Monthly',
	'3 months' => 'Quarterly',
	'1 years' => 'Yearly');
$durations = array(
	'0' => '',
	'15' => '15 minutes',
	'30' => 'half hour',
	'45' => '45 minutes',
	'60' => 'an hour',
	'120' => '2 hours',
	'240' => 'half day',
	'480' => 'a day', // Working day (8 hours)
	'960' => '2 days',
	'2400' => 'a week', // Working week (5 days)
	'4800' => 'two weeks',
	'10000' => 'a month');

$db = mysqli_connect('localhost', 'web', 'passwd') or die('no db');
mysqli_select_db($db, 'todo') or die('no db');

$crypt_algorithm = MCRYPT_3DES;
$iv_size = mcrypt_get_iv_size($crypt_algorithm, MCRYPT_MODE_ECB);
$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
function db_exec($sql)
{
	global $db;
	$q = mysqli_query($db, $sql);
	if ($msg = db_error())
		echo $sql . ' (Caused: ' .$msg.')';

	return $q;
}

function db_error() 
{
	global $db;
	echo mysqli_error($db);
}

function db_list($sql)
{
	$q = db_exec($sql);
	echo db_error();
	$rows = array();
	while ($row = mysqli_fetch_assoc($q))
		$rows[] = $row;

	return $rows;
}

function db_row($sql)
{
	$q = db_exec($sql);
	if (mysqli_num_rows($q) == 0)
		return null;
	$row = mysqli_fetch_array($q);
	return $row;
}

function db_column($sql)
{
	$data = array();
	$q = db_exec($sql);
	if (mysqli_num_rows($q) == 0)
		return null;
	while (($row = mysqli_fetch_array($q))) {
		$data[] = $row[0];
	}

	return $data;
}

function db_result($sql)
{
	$row = db_row($sql);
	return array_shift($row);
}

function db_lastid()
{
	global $db;
	return mysqli_insert_id($db);
}


/**
 * Log a message about a task or work done on a task
 * 
 * @param $task_id The task this message applies to
 * @param $msg 	The message to be recorded
 * @param $time The time it took to do the work in this message (in seconds)
 */
function msg($task_id, $msg, $time = 0) {
	global $history;
	if ($history) {
		$msg = addslashes($msg);
		db_exec("INSERT INTO todo_history(id, msg, time) VALUES($task_id, '$msg', $time)");
	}
}

function format_time($time) {
	if ($time > 3600 * 24) {
		$time = floor($time / 3600 / 24) . ' day(s)';
	} elseif ($time > 3600) {
		$time = floor($time / 3600) . ' hour(s)';
	} elseif ($time > 3600) {
		$time = floor($time / 60) . ' minute(s)';
	} else {
		$time = floor($time) . ' second(s)';
	}
	
	return $time;
}
