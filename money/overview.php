<?php
echo '<table align="center">';
echo '<tr><td>Name</td><td>Balance</td></tr>';
$sql = '
SELECT chests.*, sum(money) as balance
FROM chests, records 
WHERE acc = chests.id
GROUP BY chests.id';
$accs = db_loadList($sql);
foreach ($accs as $acc)
	echo '
<tr>
	<td><a href="?p=list&acc=' . $acc['id']. '">'.$acc['name'].'</a></td>
	<td align="right"><pre>' . money_format('%#10.2n', $acc['balance']).'</pre></td>
</tr>';
echo '<table>';
?>
