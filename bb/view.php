<?php
$pagesize = 10;
$start = isset($_GET['page'])?$_GET['page']:0;
if ($start < 0)
	$start = 0;

function format_id($id)
{
	$leadsp = 3;
	$ret = $id;
	$fill = '&nbsp;';
	while ($id < pow(10, $leadsp))
	{
		$ret = $fill . $ret;
		$id *= 10;
	}
	
	return '&lt;' . $ret . '&gt;&nbsp;';
}

$sql = '
SELECT *, messages.id as id
FROM messages
LEFT JOIN users ON users.id = poster
WHERE messages.id = '.$id;
// . 'GROUP BY posted';
list ($main_msg) = db_loadList($sql);

// Setting the current topic
//$_SESSION['topic'] = $msg['topic'];


$sql = '
SELECT messages.*, user, count(children.id) as children_number, messages.id as id
FROM messages
LEFT JOIN users ON users.id = messages.poster
LEFT JOIN messages as children ON messages.id = children.parent
WHERE messages.parent = '.$id . '
GROUP BY messages.id
ORDER BY messages.posted
LIMIT '.($start*$pagesize).',' . $pagesize;
$msgs = db_loadList($sql);
echo mysql_error();
?>

<script type="text/javascript">

var msgs = Array();
var quotes = '';

<?php
echo "
	msgs[{$main_msg['id']}] = Array();
  msgs[{$main_msg['id']}]['user'] = '{$main_msg['user']}';
  msgs[{$main_msg['id']}]['title'] = '" . addslashes($main_msg['title']) . "';
  msgs[{$main_msg['id']}]['retitle'] = '" . ((substr($main_msg['title'], 0, 3) == 'Re:')?'':'Re: ') . "{$main_msg['title']}';
  msgs[{$main_msg['id']}]['msg'] = '" . str_replace("\r\n", '\\n\\' . "\n", addslashes($main_msg['message'])) . "';
  msgs[{$main_msg['id']}]['quote'] = '" . str_replace("\r\n", '\\n\\' . "\n", addslashes($main_msg['user'] . ': <div class=\"quote\">' . $main_msg['message'])) . "</div>';
";

foreach($msgs as $msg)
{
//	if (strstr($msg['message'], ''))
//	echo 'alert("' . str_replace("\r\n", '\\n\\' . "\n", $msg['message']) . '");';
	echo "
	msgs[{$msg['id']}] = Array();
	msgs[{$msg['id']}]['user'] = '{$msg['user']}';
	msgs[{$msg['id']}]['title'] = '" . addslashes($msg['title']) . "';
	msgs[{$msg['id']}]['retitle'] = '" . ((substr($msg['title'], 0, 3) == 'Re:')?'':'Re: ') . "{$msg['title']}';
	msgs[{$msg['id']}]['msg'] = '" . str_replace("\r\n", '\\n\\' . "\n", addslashes($msg['message'])) . "';
	msgs[{$msg['id']}]['quote'] = '" . str_replace("\r\n", '\\n\\' . "\n", "{$msg['user']}: <div class=\"quote\">{$msg['message']}</div>") . "';
	";
}
?>

function show(id, action)
{
	for(m in msgs)
	{
	//	alert(m);
		if (document.getElementById(m) && m != id)
			document.getElementById(m).style.display = 'none';
	}
	
	var element = document.getElementById(id);
	element.style.display = 'block';
	
	var form = document.forms['form'+id];

// Clear parent topic - used only for spawning a new thread based on msg.
	if (id != <?php echo $id; ?>)
	{
		if (action == 'respawn')
			form.parent.value = id;
		else
			form.parent.value = '<?php echo $id; ?>';
	}
		
	if (action == 'edit')
	{
// Dealing with message quotes by manipulating strings (doesn't need an array). 
// drawback is that once a message is cleared (normal reply) it cannot be reloaded.
// hence now an array of messages is built in javascript. 
//		var msg = document.forms['form'+id].message.value;
//		var pos = msg.indexOf('<div class="quote">');
//		document.forms['form'+id].message.value = msg.substring(pos + 19, msg.length - 7);

		form.message.value = msgs[id]['msg'];
		form.title.value = msgs[id]['title'];
		form.msgid.value = id;
	}
	else
	{
		// Clear msgid to indicate that a new post should be created.
		form.msgid.value = '';
		form.title.value = msgs[id]['retitle'];

		if (action == 'quote')
		{
			mark(id);
			form.message.value = quotes;
			quotes = '';
//			form.message.value = msgs[id]['quote'];
		}
		else
			form.message.value = '';
	}
}

function mark(id)
{
	quotes += '<br />\n ' + msgs[id]['quote'];
}

function hide(id)
{
	document.getElementById(id).style.display = 'none';
}

</script>
<?php
//include('functions.php');
// OUTPUT starts
echo '<table cellspacing="0" align="center">';
$smarty->assign('style', '');

$main_msg['children_number'] = count($msgs);
$main_msg['formatted_id'] = format_id($main_msg['id']);
$main_msg['message'] = prepare($main_msg['message']);
$smarty->assign('message', $main_msg);
display('message');
?>


<tr>
	<td colspan="3"><hr>REPLIES:</td>
</tr>
	
<?php
foreach ($msgs as $msg)
{
	$msg['formatted_id'] = format_id($msg['id']);
	$msg['message'] = prepare($msg['message']);
	
	$sql = '
SELECT *
FROM messages
WHERE parent = ' . $msg['id'] . '
ORDER BY posted DESC';
	$messages = db_loadList($sql);

	$msg['children'] = $messages;
	//$parent = $msg['id']; $tmp = $msg['id'];
	//$msg['children'] = include('topic.php'); 
	//$msg['id'] = $parent; 
	//$parent = $id;
	//$quote = $msg; include('post.php');
	
	$all_messages[] = $msg;
//	$smarty->assign('message', $msg);
//	display('message');
}
$smarty->assign('messages', $all_messages);
display('messages');
?>
</table>
<a href="?view=<?php echo $id; ?>&page=<?php echo ($start - 1); ?>">&lt; &lt; prev</a> | 
<a href="?view=<?php echo $id; ?>&page=<?php echo ($start + 1); ?>">next &gt; &gt;</a>