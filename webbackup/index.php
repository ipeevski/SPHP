<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<?php
if (isset($_REQUEST['go'])) {
	$action = false;
	if (isset($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
	}
	
	$file = $_REQUEST['go'];
	include $file.'.php';
} else {
	include 'menu.php';
}