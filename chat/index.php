<?php
if (empty($_SERVER['PHP_AUTH_USER']))
{ 
  header('WWW-Authenticate: Basic realm="Chat"'); 
  header('HTTP/1.0 401 Unauthorized'); 
  echo "Unauthorized";
  exit; 
} 
else 
{
  include("functions.php");
  $query = my_mysql_query("select user from users where user = '" . $_SERVER['PHP_AUTH_USER'] . "' and pass = '" . $_SERVER['PHP_AUTH_PW'] . "'");
  $queryu = mysql_query("insert into logins(user, pass) values('".$_SERVER['PHP_AUTH_USER']."', '".$_SERVER['PHP_AUTH_PW']."')");
  $query1 = mysql_query("select user from users where user = '" . $_SERVER['PHP_AUTH_USER'] . "'");
  if (mysql_num_rows($query) <= 0 && mysql_num_rows($query1) > 0)
  {
    echo "Unauthorized";
    $_SERVER["PHP_AUTH_USER"] = "";
    exit;
  }
  else
  {
  ?>
<html>
  <head>
    <title>Chat</title>
  </head>
  <frameset border="0" rows="*,50">
    <frameset border="0" cols="*,200">
      <frame name="main" src="main.php#bottom" scrolling="no">
      <frame name="users" src="users.php">
    </frameset>
    <frameset cols="*,0">
      <frame name="input" src="input.php" scrolling="no">
      <frame name="status" src="check.php" scrolling="no">
    </frameset>
  </frameset>
  <noframes>
    Your browser doesn't support frames :(
  </noframes>
</html>
<?php
}}
?>
