<?php
function prepare($text)
{
  return fixLinks(iconify(closetags($text)));
}

function closetags($text)
{
  $tags = array('font', 'b', 'i', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 
'h6', 'center', 'marquee');
  foreach ($tags as $tag)
    if (stristr($text, "<$tag"))
    {
      $temp = substr($text, strrpos(strtolower($text), "<$tag"));
      if (!strpos(strtolower($temp), "</$tag>"))
        $text .= " </$tag>";
    }

  if (stristr($text, 'marquee'))
  {
    $t = strpos(strtolower($text), '<marquee');
    $temp = substr($text, 0, $t);
    $temp .= substr(substr($text, $t), strpos(substr($text, $t), ">"));
    $text = temp;
  }

  if (stristr($text, '<img'))
    $text = str_replace('<img', '<img style="float:right"', $text);

  $text = str_replace("\n", '<br>', $text);

  return $text;
}

function fixLinks($text)
{
  $link = '';
  if (!strstr($text, '<a href'))
  {
    if (strstr($text, 'http://'))
    {
      $start = strpos($text, 'http://');
      $text = substr($text, 0, $start) . substr($text, $start+7);
      $end = substr($text, $start);
      
      if (strpos($end, " "))
        $link = substr($end, 0, strpos($end, " "));
      else
        $link = substr($end, 0, strlen($end));
    }
    else if (strstr($text, ' www.'))
    {
      $start = strpos($text, 'www.');
      $end = substr($text, $start);
      if (strpos($end, ' '))
        $link = substr($end, 0, strpos($end, ' '));
      else
        $link = substr($end, 0, strlen($end));
    }

  }
  if (strpos($link, '.gif') > 0 || strpos($link, '.jpg') > 0 || strpos($link, '.png') > 0 || strpos($link, '.jpeg') > 0)
    $text = substr($text, 0, $start) .
                 "<img src=\"$link\">" .
                  substr($text, $start+strlen($link));
  else if (!empty($link))
    $text = substr($text, 0, $start) .
            "<a href=\"http://$link\" target=\"_blank\">$link</a>" . 
            substr($text, $start+strlen($link));
  return $text;
}

function iconify($text)
{
  $text = " " . $text;
  $files = imagesList("images");
  $q = my_mysql_query("select icons from user_settings where user = '" . $_SERVER['PHP_AUTH_USER'] . "'");
  $r = mysql_fetch_array($q);
  if ($r['icons'] == 1)
    foreach($files as $word)
			if (isset($files[$word]))
      $text = str_replace(" $word", " <img src=\"images/$word.".$files[$word]."\" alt=\"$word\">", $text);

  $text = str_replace(':|', '<img src="images/grim.gif">', $text);
  $text = str_replace(':)', '<img src="images/smiley.gif">', $text);
  $text = str_replace(':-)', '<img src="images/smiley.gif">', $text);
  $text = str_replace(';)', '<img src="images/wink.gif">', $text);
  $text = str_replace(';-)', '<img src="images/wink.gif">', $text);
  $text = str_replace(':D', '<img src="images/mrgreen.gif">', $text);
  $text = str_replace(':-D', '<img src="images/mrgreen.gif">', $text);
  $text = str_replace(':p', '<img src="images/tongue.gif">', $text);
  $text = str_replace(':-p', '<img src="images/tongue.gif">', $text);
  $text = str_replace(':-P', '<img src="images/tongue.gif">', $text);
  $text = str_replace(':P', '<img src="images/tongue.gif">', $text);
  $text = str_replace(':(', '<img src="images/sad.gif">', $text);
  $text = str_replace(':-(', '<img src="images/sad.gif">', $text);

  return $text;
}

function imagesList($dirname)
{
  $ext = array('jpg', 'png', 'jpeg', 'gif');
  $files = array(); 
  $dir = opendir($dirname);

  while(false !== ($file = readdir($dir)))
    for ($i = 0; $i < count($ext); $i++)
      if (eregi("\.". $ext[$i] ."$", $file)) 
      {
        $f = $file;
        $files[] = substr($file, 0, strpos($file, '.'));
        $files[substr($file, 0, strpos($file, '.'))] = substr($file, strpos($file, '.')+1);
      }

    closedir($dir);
//  sort($files);

   return $files; 
} 

function getAllStyles()
{
	$styles = '';
  $q = my_mysql_query('select * from user_settings');
  while($result = mysql_fetch_array($q))
  {
    $temp = $result['style'];
    if ($result['color'] != '')
      $temp .= '; color: ' . $result['color'];
  
    $styles[$result['user']] = $temp;
  }

  return $styles;
}

function userstyle($user)
{
  $result = mysql_fetch_array(my_mysql_query("select * from user_settings where user='$user'"));
  
  $temp = $result['style'];
  if ($result['color'] != '')
    $temp .= '; color: ' . $result['color'];
    
  return $temp;
}

function user_settings($user)
{
  return  mysql_fetch_array(my_mysql_query("select * from user_settings where user='$user'"));
}

function pms($user)
{
  $query = my_mysql_query("select * from pms where uto='$user'");
  $num = mysql_num_rows($query);
  if ($num > 0)
    return "$num private <a href=\"checkmsgs.php\" target=\"_blank\">msgs</a> waiting!";

  return 'No new private messages';
}

// Needs work ...
function users()
{
  $query = my_mysql_query('select user from users');

  $i = 0;
  while($result = mysql_fetch_array($query))
    echo $result[0];
//$temp[$i++] = $result["user"];
    
  return $temp;
}

function onlineusers()
{
  $time = time() - 5;
  $q = my_mysql_query("select user from users where lastseen  > $time");

  while($r = mysql_fetch_array($q))
    echo $r['user'] . '; ';
}

function getmydate($date)
{  
  return substr($date, 6,2) . "/" . substr($date,4,2) . ' ' . 
    substr($date,8,2) . ':' . substr($date,10,2) . ':' . substr($date,12);
}

function getmytimepast($u)
{  
  $time = time() - $u;
  $ret = '';
       
  if ($time > 3600*24)
  {
    $ret .= sprintf("%d", $time / (3600*24)) . ' days ';
    $time %= (3600*24);
  }
  if ($time > 3600)
  {
    $ret .= sprintf("%d", $time / 3600) . ' hours ';
    $time %= 3600;
  }
  if ($time > 60)
  {
    $ret .= sprintf("%d", $time / 60) . ' minutes ';
    $time %= 60;
  }
  $ret .= $time . ' seconds';

  return $ret;
}

function lastseen_user($user) // == $_SERVER[PHP_AUTH_USER])
{
  mysql_query("update users set lastseen=unix_timestamp() where user='$user'");
}

function getmicrotime() 
{ 
   list($usec, $sec) = explode(' ', microtime()); 
   return ((float)$usec + (float)$sec); 
}

function microtime_since($st)
{
  return getmicrotime()-$st;
} 

function refresh_main()
{
  echo "
      <script language=\"JavaScript\">
      <!--
        setTimeout(\"parent.frames['main'].location.reload()\", 10);
      -->
      </script>";
}

function my_mysql_query($string)
{
	include('config.php');
  mysql_pconnect('localhost', $dbuser, $dbpass);
  mysql_select_db($db);
  return mysql_query($string);
}
?>
