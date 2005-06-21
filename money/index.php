<?php
include('config.php');
include('db.php');
session_start();
setlocale(LC_MONETARY, 'en_AU');
connect();

include('header.php');


session_start();
if ($_GET['logout'] == 1)
{
	unset($_SESSION['user']);
}

if (!isset($_SESSION['user']))
{
	if (isset($_POST['username']))
	{
		$sql = "
SELECT id
FROM {$cfg['userdb']}.users
WHERE user = '{$_POST['username']}'
AND pass = '" . md5($_POST['password']) ."'"; 
		$_SESSION['user'] = db_loadResult($sql);
		if (!isset($_SESSION['user']))
			exit;
	}
	else
	{
		echo '
<form action="#" method="post">
<table align="center">
<tr>
	<td>Username</td>
	<td><input type="text" name="username" onLoad="this.requestFocus();"/></td>
</tr>
<tr>
	<td>Password</td>
	<td><input type="password" name="password" /></td>
</tr>
<tr>
	<td colspan="2" align="right">
		<input type="submit" name="submit" value="login" />
	</td>
</table>
</form>';
		exit;
	}
}


if ($_GET['p'])
{
	include($_GET['p'].'.php');
	if ($_GET['p'] == 'enter')
		include('list.php');
}
else
{
	if (isset($_GET['complete']))
	{
		$sql = 'UPDATE records SET completed = 100 WHERE id = ' . $_GET['complete'];
		db_exec($sql);
	}
	
	include('overview.php');
	//if ($_SESSION['acc'])
	//	include('list.php');
	include('enter.php');
	
	$sql = '
SELECT chests.*, sum(money) as balance
FROM chests, records 
WHERE acc = chests.id
GROUP BY chests.id';
	$accounts = db_loadList($sql);
	foreach ($accounts as $a)
		$graph_array[$a['name']] = $a['balance'];

	$_SESSION['a']['graph_title'] = 'Accounts';
	$_SESSION['a']['graph_array'] = $graph_array;
//	session_write_close();
	echo '<img src="graph.php?gid=a" /><br />';

	$sql = '
SELECT records.*
FROM records, chests
WHERE chests.id = acc
AND user = ' . $_SESSION['user'];
	$accounts = db_loadList($sql);

	foreach ($accounts as $a)
	{
		if ($a['money'] >= 0)
		{
			$graph_ci[$a['category']] += $a['money'];
			$graph_ui[$a['person']] += $a['money'];
		}
		else
		{
			$graph_ce[$a['category']] += $a['money'];
			$graph_ue[$a['person']] += $a['money'];
		}
		
		$month = substr($a['date'], 5, 2);
		$monthly[$month] += $a['money'];
		if ($a['money'] > 0)
			$mplus[$month] += $a['money'];
		else
			$mminus[$month] += $a['money'];
		
		if ($month == date('m'))
		{
			$day = substr($a['date'], 8);
			$daily[$day] += $a['money'];
			if ($a['money'] > 0)
				$dplus[$day] += $a['money'];
			else
				$dminus[$day] += $a['money'];
		}
	}
	//print_r($monthly);
	$_SESSION['monthly']['type'] = 'chart';
	$_SESSION['monthly']['graph_title'] = 'Monthly transactions';
	$_SESSION['monthly']['graph_array'] = $monthly;
	echo '<img src="graph.php?gid=monthly" /><br />';

	$_SESSION['mplus']['type'] = 'chart';
	$_SESSION['mplus']['graph_title'] = 'Monthly transactions';
	$_SESSION['mplus']['graph_array'] = $mplus;
	echo '<img src="graph.php?gid=mplus" /><br />';

	$_SESSION['mminus']['type'] = 'chart';
	$_SESSION['mminus']['graph_title'] = 'Monthly transactions';
	$_SESSION['mminus']['graph_array'] = $mminus;
	echo '<img src="graph.php?gid=mminus" /><br />';
	
	$_SESSION['daily']['type'] = 'chart';
	$_SESSION['daily']['graph_title'] = 'Daily transactions';
	$_SESSION['daily']['graph_array'] = $daily;
	echo '<img src="graph.php?gid=daily" /><br />';

	$_SESSION['dplus']['type'] = 'chart';
	$_SESSION['dplus']['graph_title'] = 'Daily transactions';
	$_SESSION['dplus']['graph_array'] = $dplus;
	echo '<img src="graph.php?gid=dplus" /><br />';

	$_SESSION['dminus']['type'] = 'chart';
	$_SESSION['dminus']['graph_title'] = 'Daily transactions';
	$_SESSION['dminus']['graph_array'] = $dminus;
	echo '<img src="graph.php?gid=dminus" /><br />';

	$_SESSION['ci']['graph_title'] = 'Income by Categories';
	$_SESSION['ci']['graph_array'] = $graph_ci;
	echo '<img src="graph.php?gid=ci" />';

	$_SESSION['ce']['graph_title'] = 'Expenses by Categories';
	$_SESSION['ce']['graph_array'] = $graph_ce;
	echo '<img src="graph.php?gid=ce" /><br />';

	$_SESSION['ui']['graph_title'] = 'Income by Source';
	$_SESSION['ui']['graph_array'] = $graph_ui;
	echo '<img src="graph.php?gid=ui" />';

	$_SESSION['ue']['graph_title'] = 'Expenses by Receiver';
	$_SESSION['ue']['graph_array'] = $graph_ue;
	echo '<img src="graph.php?gid=ue" />';

	//include('graph.php');
}
?>
