	<script type="text/javascript" src="main.js"></script>
	<script language="JavaScript" type="text/javascript">
		var pass = new Array();
  <?php foreach ($list as $k => $acc) { ?>
  	pass[<?= $k ?>] = '<?= addslashes(decrypt($acc['password'])) ?>';
  <?php } ?>
		function reveal(field, i)
		{
			if (pass[i])
				field.innerHTML = pass[i];
			else
				field.innerHTML = '&nbsp;';
		}
		
		function hide(field)
		{
			field.innerHTML = '****';
		}
	</script>
	
	<form action="#" method="post">
	<input type="hidden" name="id" id="id" />
	<table align="center" >
	<tr>
		<th colspan="2">Info</th>
	</tr>
	<tr>
		<td width="50%">Account</td>
		<td width="50%"><input type="text" name="account" value="<?= $account ?>" /></td>
	</tr>
	<tr>
		<td width="50%">Site</td>
		<td width="50%"><input type="text" name="site" value="<?= $site ?>" /></td>
	</tr>
	<tr>
		<td width="50%">Username</td>
		<td width="50%"><input type="text" name="usr" value="<?= $usr ?>" /></td>
	</tr>
	<tr>
		<td width="50%">Password</td>
		<td width="50%"><input type="password" name="passwd" value="" /></td>
	</tr>
	<tr>
		<td align="center"><input type="submit" name="action" value="seach" /></td>
		<td align="center"><input type="submit" name="action" value="add" /></td>
	</tr>
	<tr>
		<td colspan="2" align="center">
  		<input type="submit" name="action" value="logout"/>
  	</td>
	</table>

	
	<table align="center">
<?php if (isset($sugar_account) and is_array($sugar_account)) 
foreach($sugar_account as $field => $value) 
	if (!empty($value)){ ?>
	<tr>
		<td valign="top"><?= $field ?></td>
		<td valign="top"><?= $value ?></td>
	</tr>
<?php } ?>
	</table>
	
	
	<table align="center" cellspacing="0">
	<tr>
		<th>del</th>
		<th>Site</th>
		<th>Username</th>
		<th width="250">Password</th>
	</tr>
<?php foreach ($list as $k => $acc) { ?>
	<tr class="<?php echo (($k % 2 == 0)?'odd':'even');?>">
		<td align="center"><input type="submit" name="action" value="X" onClick="document.getElementById('id').value = <?= $acc['id'] ?>"/></td>
		<td><a href="<?=$acc['site']?>"><?= $acc['site'] ?></a></td>
		<td align="center"><?= $acc['username'] ?></td>
		<td align="center"><div onMouseOver="reveal(this, <?= $k ?>);" onMouseOut="hide(this);">****</div></td>
	</tr>
<?php } ?>
	</table>
	</form>
</body>
</html>
