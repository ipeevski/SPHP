<?php

  if (isset($_POST["msg"]))
  {
    include("functions.php");
    my_mysql_query("insert into pms(uto, ufrom, date, msg) 
                    values('" . $_POST["user"] . "', '" .
                    $_SERVER["PHP_AUTH_USER"] . "', now(), '" . 
                    $_POST["msg"] . "')");
    echo "Message send!";
    echo "<script language=\"JavaScript\">
    <!--
      window.close();
    -->
    </script>";    
  }
  else
  {
    echo "Sending a private message to " . $_GET["user"];
?>
<form action="pm.php" method="post">
  <textarea rows="20" cols="80" name="msg"></textarea>
  <input type="hidden" name="user" value="<?php echo $_GET["user"]; ?>">
  <input type="submit" name="submit" value="Send">
</form>  

<?php
  }
?>
