<html>
  <head>
  </head>
  <body style="margin: 2px; font-family: System, sans-serif; background: lightgreen">

<?php 
  include('functions.php');  
  if (isset($_POST['line']) && strstr($_POST['line'], '/help'))
  {
    $help = file('help.txt');
    my_mysql_query("insert into pms (uto, ufrom, date, msg) 
values('". $_SERVER['PHP_AUTH_USER'] ."', 'ChanServ', now(), 
'". file_get_contents('help.txt') . "')"); 
  }
  else if (!empty($_POST['line']) && trim($_POST['line']) != '')
  {
    $_POST['line'] = str_replace("'", "\'", $_POST['line']);
    $query = my_mysql_query("insert into posts(post, user) values('" . $_POST['line'] . "', '" . $_SERVER['PHP_AUTH_USER'] . "')");
    mysql_query("update users set lastseen=unix_timestamp() where user='" . $_SERVER['PHP_AUTH_USER'] . "'");
    mysql_query('delete from readposts');
    refresh_main();
  }
?>

    <form name="chat" action="input.php" method="post">
      <input style="
           border: 1px solid darkblue; "
        type="field" name="line" size="80">
      <input type="submit" name="Submit" value="Say!" 
		style="background: white;
                       border: 1px solid darbblue;
                       color: darkblue;
                       font-weight: bold"><br>
    &nbsp;&nbsp;Chat v0.62
    [<a href="settings.php" target="_blank">Settings</a>] 
    [<a href="log.php" target="_blank">History</a>] 
    [<a href="bugs.php" target="_blank">Bugs/Requests</a>] 
    </form>
    <script type="text/JavaScript">
    <!-- 
     document.forms["chat"].elements["line"].focus();

      function a()
	{ alert("not implemented yet :p be patient"); } 
    -->
    </script>
  </body>
</html>
