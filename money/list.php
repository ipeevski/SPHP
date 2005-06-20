<?php
if ($_GET['acc'])
	$_SESSION['acc'] = $_GET['acc'];
$sql = '
SELECT records.*, sum(money) as balance
FROM chests, records 
WHERE chests.id='.$_SESSION['acc'] . '
AND acc = chests.id
GROUP BY chests.id';
list( $a ) = db_loadList($sql);
echo db_error();
echo '<h1>' . $a['name'] . '</h1>';
$balance = $a['balance'];
$records = db_loadList('
SELECT * 
FROM records 
WHERE acc='.$_SESSION['acc'] . ' 
ORDER BY date ASC');
echo '<table width="80%" align="center" cellpadding="5">';
echo '
<tr>
	<td>&nbsp;</td>
	<td>del</td>
	<td>edit</td>
	<td>Date</td>
	<td>Category</td>
	<td>To/From</td>
	<td width="100%">Desc</td>
	<td>Value</td>
	<td>Balance</td>
</tr>';
for($i = count($records) - 1; $i >= 0; $i--)
{
	if (empty($records[$i]['money']))
		$records[$i]['money'] = '0.00';
	$records[$i]['disp_money'] = '<pre' . (($records[$i]['money'] < 0)?' class="negative"':'') . '>' . money_format("%#6.2n", $records[$i]['money']) . '</pre>';
	$records[$i]['balance'] = '<pre' . (($balance < 0)?' class="negative"':'') . '>' . money_format("%#6.2n", $balance) . '</pre>';
	$balance -= $records[$i]['money'];
	if ($records[$i]['completed'] == 100)
		$actual_balance += $records[$i]['money'];
}

//array_reverse($records);
$rowstyle='odd';
foreach ($records as $r)
{
//	if ($r['balance'] < 0)
//		$r['balance'] = '<span class="negative">'.$r['balance'].'</span>';
//	if ($r['money'] < 0)
//		$r['money'] = '.$r['money'].'</span>';
  echo '
<tr class="' . (($r['completed'] > 0)?$rowstyle:'incomplete') . '">
	<td>' . (($r['completed'] > 0)?'&nbsp;':'<a href="index.php?complete='.$r['id'].'">c</a>') . '</td>
	<td><a href="index.php?p=enter&del_id=' . $r['id'] . '">del</a></td>
	<td><a href="index.php?p=enter&id=' . $r['id'] . '">edit</a></td>
	<td nowrap>'.$r['date'].'</td>
	<td nowrap>'.$r['category'].'</td>
	<td nowrap>'.$r['person'].'</td>
	<td>'.$r['desc'].'</td>
	<td align="right" nowrap>' . $r['disp_money'] . '</td>
	<td align="right" nowrap>' . $r['balance'] . '</td>
</tr>';
	$rowstyle = $rowstyle == 'odd'?'even':'odd';
}
echo '
<tr>
	<td colspan="3">&nbsp;</td>
	<td style="border-top: 2px solid black">' . date('Y-m-d') . '</td>
	<td colspan="4" style="border-top: 2px solid black">Total: </td>
	<td align="right" style="border-top: 2px solid black"><pre>'.money_format("%#6.2n", $actual_balance).'</pre></td>
</tr>';
echo '
<tr>
	<td colspan="3">&nbsp;</td>
	<td>' . date('Y-m-d') . '</td>
	<td colspan="4">Expected: </td>
	<td><pre>'.money_format("%#6.2n", $a['balance']).'</pre></td>
</tr>';

echo '</table>';
?>
