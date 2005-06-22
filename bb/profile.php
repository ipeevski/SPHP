<?php
$uid = $_GET['profile'];
$sql = '
SELECT * 
FROM users
LEFT JOIN user_settings ON users.id = user_settings.id
WHERE users.id = ' . $uid;
list ($user) = db_loadList($sql);

$smarty->assign('user', $user);
display('user');
?>