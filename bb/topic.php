<?php
include_once('config.php');
include_once('db.php');
if (!$parent) {
	$parent = $_GET['parent'];
}
$sql = '
SELECT *
FROM messages
WHERE parent = ' . $parent . '
ORDER BY posted DESC';
$messages = db_loadList($sql);
$pid = db_loadResult('SELECT parent FROM messages WHERE id = ' . $parent);
echo '<ul>';
echo '<li><a href="index.php?view=' . $pid . '" target="_top">^ up ^</a></li>';
foreach ($messages as $msg) {
	echo '<li><a href="index.php?view=' . $parent . '#msg' . $msg['id'] . '" target="_top">' . $msg['title'] . '</a></li>';
}
echo '</ul>';
?>
