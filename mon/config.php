<?php
$config['sites'] = array('ipeevski.com');

$config['disabled'] = array('awstats', 'alexa', 'wayback_machine', 'mysql'); // 'http', 'html', 'alexa', 'awstats', 'wayback_machine', 'geoip', 'mysql'

$config['awstats']['url'] = 'http://myrejournal.com/awstats/';

$config['google']['search']['key'] = 'AIzaSyCVfqcZofcy7OCViocK7jnIQoJxqJ8w4Ig';
$config['google']['search']['engine'] = '017576662512468239146';

$config['alexa']['login'] = '';
$config['alexa']['secret'] = '';

$config['mysql']['user'] = 'root';
$config['mysql']['passwd'] = '';

$config['email']['smtp'] = '';
$config['email']['from'] = 'root@localhost';
$config['email']['to'] = array(
	'user@example.com' => array(
				'sites' => array('google.com'),
				'mods' => array('http', 'mysql'),
				'type' => 'daily'),
	);
?>
