<html>
  <head>
    <title>Main</title>
  </head>
  <body onLoad="scrollTo(0,4000)" style="margin: 3px; font-family: System, sans-serif">
  <?php
    include('functions.php');
    $st = getmicrotime();

    my_mysql_query("insert into readposts values('" . $_SERVER['PHP_AUTH_USER'] . "', '0')");
    lastseen_user($_SERVER['PHP_AUTH_USER']);
  
    $us = user_settings($_SERVER['PHP_AUTH_USER']);

    $styles = getAllStyles();

    $count = mysql_fetch_array(mysql_query('select count(lastread) as count from readposts'));
    $query = my_mysql_query('select * from posts order by date desc limit 20');
    $page = '';
    while ($results = mysql_fetch_array($query))
    {
	$results['post'] = htmlspecialchars($results['post']);
//      $line = $results["id"] . ". [" . getmydate($results["date"]) . "]: ";
      $line = '[' . getmydate($results['date']) . ']: ';
      if (substr($results["post"], 0, 5) == "/seen")
      {
        $who = substr($results['post'], 6);
        $qu = mysql_query("select lastseen from users where user='$who'");
        if (mysql_num_rows($qu)>0)
        {
          $r = mysql_fetch_array($qu);
          $unixtime = $r['lastseen'];
          $line .= "<span style=\"font-weight: bold; color: green\">Chanserv</span>> $who last seen " . getmytimepast($unixtime) . " ago";
        }
        else
          $line .= '<span style="font-weight: bold; color: green">Chanserv</span>> unknown user ' . $who;
      }
      else
      {
        if (substr($results['post'], 0, 3) == "/me")
          $line .= '<span style="color:purple">* ' . $results['user'] . substr($results['post'],3) . '</span>';
        else 
				{
          $line .= '<span style="font-weight: bold">' . $results['user'] . '</span>&gt;';
					if (isset($styles[$results['user']]))
						$line .= '<span style="' . $styles[$results['user']] . '">';
					$line .= prepare($results['post']);
					if (isset($styles[$results['user']]))
						$line .= '</span>';
				}
      }

      $page = $line . "<br>\n" . $page; 
    }
    echo $page;
    if ($us['sound'] > 0 && $results['user'] != $_SERVER['PHP_AUTH_USER'])
      echo '<embed src="nutter.wav" hidden="true" autostart="true" ></embed>';
    echo '<a id="bottom">';
    echo '<br><hr><br>Page prepared in ' . number_format(microtime_since($st),3) . ' seconds.';
  ?>
  </body>
</html>
