<html>
  <head>
<!-- <meta http-equiv="refresh" content="2;URL=http://www.macs.unisa.edu.au/chat/check.php"> -->
  </head>
  <body>
<?php
	include "functions.php";
    
	if (!empty($_POST[red])) {
		echo "Yay, you found the treasure :). 
	}
The treasure is a list of all emoticons on here! pretty cool eh? <br>
 <a href=\"emoticons.php\">Click!</a> "
?>
		<form action="check.php" method="post">
			<input type="submit" name="red" value="Big Red Button">
		</form>
<?php
	$query = my_mysql_query("select lastread from readposts where user='" . $_SERVER['PHP_AUTH_USER'] . "'");
	//TODO: check also for the number of the last read post
	$read = mysql_num_rows($query);
	lastseen_user($_SERVER['PHP_AUTH_USER']); 
	$usettings = user_settings($_SERVER['PHP_AUTH_USER']);
	$refresh = $usettings['refresh'];
	if (!is_int($refresh) || $refresh < 4) {
		$refresh = 10;
	}
	
	if ($read <= 0) {
		refresh_main();    
	}
?>
    
    <script language="JavaScript">
    <!--
      setTimeout("location.reload()", <?php echo $refresh; ?>000);
    -->
    </script>
  </body>
</html>