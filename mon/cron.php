<?php
$dir = dirname(__FILE__);
set_include_path(get_include_path() . PATH_SEPARATOR . $dir);

global $config;
$config['dir'] = $dir;
include 'config.php';

date_default_timezone_set('America/New_York');

include 'classes/service.php';
include 'classes/mail.php';

$mail = new Mail();
$mail->cron();