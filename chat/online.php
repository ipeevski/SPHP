<?php
echo "Online users @ the <a href=\"/chat/\">chat</a>:";

  mysql_connect("localhost", "chatview", "chat");
  mysql_selectdb("chat");
  $q = mysql_query("select user, lastseen from users");

  while($r = mysql_fetch_array($q))
    if ($r["lastseen"] + 15 > time())
      echo $r['user'] . "; ";
?>
