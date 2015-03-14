<html>
  <head>
  </head>
  <body style="margin: 3px; font-family: System, sans-serif">
  <?php
    include("functions.php");

//    sleep(2);

    $query = my_mysql_query("select users.user as 'user', lastseen, lastread from users left join readposts on users.user = readposts.user group by users.user");
    $i = 0;

    $usettings= mysqli_fetch_array(my_mysql_query("select * from user_settings where user = '". $_SERVER['PHP_AUTH_USER'] . "'"));

    $tr = my_mysql_query("select count(user) as c from posts");
    $t = mysqli_fetch_array($tr);
    echo "Total Posts: " . $t["c"] . "<br>". pms($_SERVER["PHP_AUTH_USER"]) ."<br>";

    $online = 0;
    echo "=== Online ===<br>";

    while ($results = mysqli_fetch_array($query))
    {
      $u = $results["user"];

      $unixtime = $results["lastseen"];
      if ($unixtime + 15 < time())
      //if (!isset($results[lastread]))
      {
        $temp[$i++] = "<div style=\"border: 1px solid green\">" .
        "<img style=\"float:left\" src=\"images/offline.gif\">" .
        "$u <a href=\"pm.php?user=$u\" target=\"_blank\">pm</a><br>\n";
        if ($usettings['userlist'] > 0) 
          $temp[$i-1] .= "Last seen " . getmytimepast($unixtime) . " ago.";
        $temp[$i-1] .= "<br></div>";
      }
      else
      {
        ++$online;
        echo "<p><img style=\"float:left\" src=\"images/online.gif\">";
        echo "$u <a href=\"pm.php?user=$u\" target=\"_blank\">pm</a></p>\n";
      }
    }

    echo "Users online: $online<br>";
    echo "<br>=== Offline ===<br>";

    if (isset($temp))
      foreach ($temp as $u)
	echo $u;
  ?>
    <script language="JavaScript">
    <!--
      setTimeout("location.reload()", 5*60*1000);
    -->   
    </script>

  </body>
</html>
