<?php
/**
 * Created on Feb 23, 2006
 * 
 * @author cyberhorse
 * @version 0.1
 */
 
$page = isset($_GET['p'])?$_GET['p']:'passwd';
session_start();
include('config.php');


$action = isset($_POST['action'])?$_POST['action']:'';

if ($action == 'login')
	$_SESSION['password'] = $_POST['password'];
if ($action == 'logout')
	unset($_SESSION['password']);

if (!isset($_SESSION['password']))
{
	?>
<div style="padding: 20%; text-align: center;">
<form action="#" method="post">
	<input style="border: 1px solid lightgray; color: lightgray; font-size: 12pt; font-style: italic; font-family: Webdings, sans-serif" type="password" name="password" size="6" />
	<input style="border: 1px solid lightgray; background: white; color: lightgray" type="submit" name="action" value="login" width="20" />
</form>
</div>
</body>
</html>
	<?php
	exit;
}


if ($page == 'passwd')
{	
	$account = isset($_POST['account'])?$_POST['account']:'';
	$usr = isset($_POST['usr'])?$_POST['usr']:'';
	$site = isset($_POST['site'])?$_POST['site']:'';
	
	if ($action == 'X')
		db_exec('DELETE FROM passwords WHERE id = ' . $_POST['id']);
	
	if ($action == 'add')
	{
		$passwd = isset($_POST['passwd'])?$_POST['passwd']:'';
		$passwd = addslashes(encrypt($passwd));
		db_exec("INSERT INTO passwords(account, site, username, password) VALUES('$account', '$site', '$usr', '$passwd')");
	}
	
//	$sugar_account = sugar_account($account);
	
	$list = db_list("
	SELECT * " .
	"FROM passwords " .
	"WHERE account LIKE '$account' AND site LIKE '%$site%' AND username LIKE '%$usr%'" .
	"ORDER BY account, site, username");
	
			
	include('page.php');
}
?>
