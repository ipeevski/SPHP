<html>
<head>
	<title>Tools<?= (isset($page)?' :: ' . $page:'') ?></title>
	<link rel="stylesheet" type="text/css" href="main.css">
</head>
<body>

<?php
/**
 * Created on Feb 23, 2006
 * 
 * @author cyberhorse
 * @version 0.1
 */

$db = mysqli_connect('localhost', 'web', 'passwd') or die('no db');
mysqli_select_db($db, 'passwd') or die('no db');

date_default_timezone_set('America/New_York');

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
	return mysqli_error($db);
}

function sugar_account($name)
{
	$sql = '' .
'SELECT ' .
'CONCAT(billing_address_street, \'<br>\', billing_address_city, \', \', billing_address_state, \' \', billing_address_postalcode) as address,' .
'phone_office as phone,' .
'phone_fax as fax,' .
'email1 as email,' .
'website' .
' FROM sugar.accounts' .
' WHERE name LIKE ' . "'$name%'";

	$matches = db_list($sql);
	if (!$matches || strlen($name) < 3)
		return NULL;
	return $matches[0];  
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

function decrypt($text)
{
	return encrypt($text);
	global $crypt_algorithm, $iv;
	return mcrypt_decrypt($crypt_algorithm, $_SESSION['password'], $pass, MCRYPT_MODE_ECB, $iv);
}

function encrypt($text)
{
	if (empty($_SESSION['password'])) {
		$pass = str_pad($_SESSION['password'], strlen($text), 'x');
	} else {
		$pass = str_pad($_SESSION['password'], strlen($text), $_SESSION['password']);
	}
//	while (strlen($pass) < strlen($text))
//		$pass .= $_SESSION['password'];
	return $text ^ $pass;
	global $crypt_algorithm, $iv;
	return mcrypt_encrypt($crypt_algorithm, $_SESSION['password'], $pass, MCRYPT_MODE_ECB, $iv);
}
