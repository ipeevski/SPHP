<?php
  include("functions.php");
  $query = my_mysql_query("select * from pms where uto='" . $_SERVER["PHP_AUTH_USER"] . "'");

  while ($result = mysql_fetch_array($query))
    echo "<b>From " . $result["ufrom"] . "</b>:" . $result["msg"] . "<br>";

  $query = my_mysql_query("delete from pms where uto='" . $_SERVER["PHP_AUTH_USER"] . "'");
?>
