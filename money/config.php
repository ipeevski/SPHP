<?php
$cfg['db'] = 'money';
$cfg['userdb'] = $cfg['db'];
$cfg['user'] = 'web';
$cfg['pass'] = 'passwd';

$cfg['text_fields'] = array('person', 'category', 'notes', 'title', 'desc');

$cfg['states'] = array(0 => 'upcoming', 10 => 'invoiced', 90 => 'deposited', 100 => 'completed');

//$DEBUG=10;
?>
