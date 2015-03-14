<html>
<head>
	<title>Statistics</title>
	<link rel="stylesheet" href="css/main.css" />
	<link rel="stylesheet" href="css/print.css" media="print" />
	<script type="text/javascript" src="js/jquery.js"></script>
</head>
<body>
	<div class="noprint">
	<form method="post">

Modules:
<?php
global $config;
include('config.php');
$sites = $config['sites'];

date_default_timezone_set('America/New_York');

include 'lib/text_statistics.php';
include 'lib/google.php';

// Load Services
include('classes/service.php');
$dh = opendir('plugins');
$mods = array();
while (($mod = readdir($dh))) {
	if ($mod[0] != '.') {
		include 'plugins/'.$mod;
		$modname = substr($mod, 0, -4);
		$module = ucwords(str_replace('_', ' ', $modname));
		$classname = str_replace(' ', '', $module);
		$obj = new $classname();
		$mods[$modname] = $obj;
		
		if (empty($config['disabled']) or !in_array($modname, $config['disabled'])) {
			echo ' <input type="checkbox" name="modules[]" value="'.$modname.'" /> '.$module;
		}
	}
}
closedir($dh);
?>
<input type="checkbox" onclick="$('input[@name^=modules]').attr('checked', $(this).attr('checked'));"> All
<?php
echo '<br />Sites: ';
foreach ($sites as $site) {
	echo ' <input type="checkbox" name="sites[]" value="'.$site.'" /> '.$site;
}
?>
<input type="checkbox" onclick="$('input[@name^=sites]').attr('checked', $(this).attr('checked'));"> All
<br />
		Date:
		<select name="month">
		<?php for ($i = 1; $i <= 12; $i++) { ?>
			<option value="<?php echo $i?>"<?php echo (date('n') == $i ? ' selected="selected"' : '')?>><?php echo date('F', mktime(0, 0, 0, $i))?></option>
		<?php } ?>
		</select>
		
		<select name="year">
		<?php for ($i = 2005; $i <= 2010; $i++) { ?>
			<option value="<?php echo $i?>"<?php echo (date('Y') == $i ? ' selected="selected"' : '')?>><?php echo $i?></option>
		<?php } ?>
		</select>
		<input type="submit" value="apply" />
	</form>

<?php
// Configure Services
if (!empty($_POST['month'])) {
	$mods['awstats']->setDate(mktime(0, 0, 0, $_POST['month'], 1, $_POST['year']));
}
$mods['alexa']->login($config['alexa']['login'], $config['alexa']['secret']);
$mods['mysql']->login($config['mysql']['user'], $config['mysql']['passwd']);

// Process Sites
if (isset($_POST['sites'])) {
	foreach ($_POST['sites'] as $site) {
		$site_id = str_replace('.', '_', $site);
		echo '<button onclick="$(\'.sites\').hide(); $(\'#'.$site_id.'\').show();">'.$site.'</button>';
	}
}
?>

<br />
<a onclick="$('.block_body').show()">Expand All</a> / <a onclick="$('.block_body').hide()">Collapse All</a>
<br /><br />

</div> <!-- header (don't print) -->
<?php
if (isset($_POST['sites'])) {
	foreach ($_POST['sites'] as $site) {
		echo '<div id="'.str_replace('.', '_', $site).'" class="sites">';
		echo '<h1>' . $site . '</h1>';
		
		$mods['awstats']->setBaseUrl($config['awstats']['url']);
		
		foreach ($mods as $modname => $mod) {
			if (isset($_POST['modules']) and in_array($modname, $_POST['modules'])) {
				$mod->display($site);
			}
		}
		
		echo '</div>';
	}
}
?>

<script type="text/javascript">
$(document).ready(function() { 
	$('.sites:first').show(); 
});
</script>

</body>
</html>