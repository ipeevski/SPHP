<html>
  <head>
  </head>
  <body style="margin: 3px; font-family: System, sans-serif">
  <?php
    include("functions.php");

    if (!isset($_GET["no"]))
      $i = 0;
    else
      $i = $_GET["no"];
    

    $query = my_mysql_query("select * from posts order by date desc limit $i, 50");
    while ($results = mysqli_fetch_array($query))
    {
      $results['post'] = htmlspecialchars($results['post']);
      $line = $results["id"] . ". [" . getmydate($results["date"]) . "]: ";
      if (substr($results["post"], 0, 5) == "/seen")
      {
        $who = substr($results["post"], 6);
        $qu = my_mysql_query("select lastseen from users where user='$who'");
        if (mysqli_num_rows($qu)>0)
        {
          $r = mysqli_fetch_array($qu);
          $unixtime = $r["lastseen"];
          $line .= "<span style=\"font-weight: bold; color: green\">Chanserv</span>> $who last seen " . getmytimepast($unixtime) . " ago";
        }
        else
          $line .= "<span style=\"font-weight: bold; color: green\">Chanserv</span>> unknown user $who";
      }
      else
      {
        if (substr($results["post"], 0, 3) == "/me")
          $line .= "<span style=\"color:purple\">* " . $results["user"] . substr($results["post"],3) . "</span>";
        else 
          $line .= "<span style=\"font-weight: bold\">" . prepare($results["user"]) . "</span>> " . $results["post"];
      }

      echo prepare($line) . "<br>\n"; 
    }

  $i += 50;
  $prev = $i - 100;
  if ($prev < 0)
    $prev = 0;
  echo "[<a href=\"log.php?no=$prev\"><< prev</a>]";
  echo "[<a href=\"log.php?no=$i\">next >></a>]";

  ?>
  </body>
</html>
