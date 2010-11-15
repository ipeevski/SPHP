<?php
if (isset($_POST["msg"])) {
	include("functions.php");
	my_mysql_query("insert into pms(uto, ufrom, date, msg) 
                    values('cyberhorse', '" .
                    $_SERVER["PHP_AUTH_USER"] . "', now(), '" . 
                    $_POST["priority"] . "! " . $_POST["heading"] . ": " . 
                    $_POST["msg"] . "')");
	echo "Message send!"; 
	echo "<script language=\"JavaScript\">
	<!--
		window.close();
	-->
	</script>";    
} else {
?>
<h1>Make a request/Report a bug</h1>
<form action="bugs.php" method="post">
  Priority: 
  <select name="priority">
    <option value="0">Low</option>
    <option value="1">Medium</option>
    <option value="2">High</option>
    <option value="3">Emergency</option>
  </select><br>
  Subject: <input type="field" name="heading"><br>
  <textarea rows="20" cols="80" name="msg"></textarea><br />
  <input type="submit" name="submit" value="Send">
</form>  

<?php
}
?>