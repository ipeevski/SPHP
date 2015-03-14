<?php
  if (isset($_POST["color"]) || isset($_POST["style"]))
  {
    include("functions.php");
    $q = my_mysql_query("select user from user_settings where user='".$_SERVER["PHP_AUTH_USER"]."'");
    if (mysql_numrows($q) < 1)
      my_mysql_query("insert into user_settings(user) values('".$_SERVER['PHP_AUTH_USER']."')");
    my_mysql_query("update user_settings 
			set color='" . $_POST["color"] . "', 
			style='" . $_POST["style"] . "' ,
                        icons='" . $_POST["icons"] . "' ,
                        userlist='" . $_POST['userlist']  . "',
                        refresh='". $_POST['refresh'] ."',
			sound='". $_POST['sound'] ."' 
			where user='" . $_SERVER["PHP_AUTH_USER"] . "'");

    echo "<script language=\"JavaScript\">
    <!--
      window.close();
    -->   
    </script>";

  }
  else
  {
    require("functions.php");
    $results = mysqli_fetch_array(my_mysql_query("select * from user_settings where user='" . $_SERVER["PHP_AUTH_USER"] . "'"));
?>
<form action="settings.php" method="post">
  New colour: <input type="field" name="color" value="<?php echo $results["color"]; ?>"><br>
  Style: <input type="field" name="style" size="80" value="<?php echo $results["style"]; ?>"> as in anything that will fit after style="<br>
  Icons: 
  <select name="icons">
    <option value="0">no</option>
    <option value="1">yes</option>
  </select><br>
  User list display:
  <select name="userlist">
    <option value="0">only users</option>
    <option value="2">last seen data</option>
  </select><br>
  Time for refresh: <input type="field" name="refresh" value="4"> seconds<br>
  Sound notification: 
  <select name="sound">
    <option value="1">yes</option>
    <option value="0">no</option>
  </select><br>
  <input type="submit" name="submit" value="Set">
</form>

<?php
  }
?>
