<?php
function my_mysql_query($string)
{
	global $dbuser, $dbpass, $db;
  mysql_pconnect("localhost", $dbuser, $dbpass);
  mysql_select_db($db);
  return mysql_query($string);
}

function getmydate($date)
{
  return substr($date, 6,2) . "/" . substr($date,4,2) . "@" .
    substr($date,8,2) . ":" . substr($date,10,2);
}

function num_replies($id)
{
    $q = my_mysql_query("select * from replies where parent = '$id'");
    return mysql_num_rows($q);
}


function show_row($row, $type)
{

  if ($row['timestamp']+3000000 > date("Ymdhis"))
    $new = '<img src="images/new.gif" />';
  else
  {
    $q = my_mysql_query("select timestamp from replies where parent='$row[id]' order by timestamp desc");
    $r = mysql_fetch_array($q);
  if ($r['timestamp']+3000000 > date("Ymdhis"))
    $new = '<img src="images/new.gif" />';
  }
  $msg = $row['message'];
  $msg = prepare($msg);
  $final = "<div style=\"float: left; padding: 0px; margin-left: 0px; margin-right: 0px; margin-bottom: 0px\">" . ((!empty($row['flags']))?("<img src=\"./images/" . $row['flags'] .".gif\" />"):"") . "</div>" . 
"<div style=\"margin-left: 20px; border-style: solid; border-color: #006600; border-width: thin; border-bottom-style: none; align: center; width: 200px; background:#ccff99;\">" . ((!empty($row['flag']))?("<img src=\"./images/" . $row['flags'] .".gif\" />"):"") . "<span style=\"color: green;\">[".getmydate($row['timestamp']) . "]</span> by ".$row['user']."</div>"; 
	if (!isset($row['title']))
		$row['title'] = '';
	$final .= "<div style=\"margin-left: 0px; border-style: solid; border-color: #006600; border-width: thin; padding: 15px; align: center; width: 300px; background:#ccffcc;\"><b style=\"text-transform: capitalize\">" . $new.$row['title'] . "</b><br /> "; 
  if ($type == "short")
  {
    $final .= substr($row['message'], 0, 100+strpos(substr($row['message'], 100), ' '));
    if (strlen($msg) > 100 || num_replies($row['id']))
      $final .= " <a href=\"complete.php?id=" . $row['id'] . "\">more...</a>";
    else
      $final .= "<br><a href=\"complete.php?id=" . $row['id'] . "\">reply</a>";
    $final .= "<br>(".num_replies($row['id'])." replies)"; 
  }
  else if ($type == 'long')
    $final .= $msg;

	if (!empty($row['attachment']))
    $final .= ' <br />[<a href="upload/' . $row['attachment'] . '"> file </a>]';

  $final .= "</div><br>";
  return $final;
}

function iconify($text)
{
//  $text = " " . $text;

  $text = str_replace(":)", "<img src=\"images/smiley.gif\">", $text);
  $text = str_replace(":-)", "<img src=\"images/smiley.gif\">", $text);
  $text = str_replace(";)", "<img src=\"images/wink.gif\">", $text);
  $text = str_replace(";-)", "<img src=\"images/wink.gif\">", $text);
  $text = str_replace(":D", "<img src=\"images/mrgreen.gif\">", $text);
  $text = str_replace(":-D", "<img src=\"images/mrgreen.gif\">", $text);
  $text = str_replace(":p", "<img src=\"images/tongue.gif\">", $text);
  $text = str_replace(":-p", "<img src=\"images/tongue.gif\">", $text);
  $text = str_replace(":-P", "<img src=\"images/tongue.gif\">", $text);
  $text = str_replace(":P", "<img src=\"images/tongue.gif\">", $text);
  $text = str_replace(":(", "<img src=\"images/sad.gif\">", $text);
  $text = str_replace(":-(", "<img src=\"images/sad.gif\">", $text);

  return $text;
}

function fixLinks($text)
{
  if (!strstr($text, "<a href"))
  {
    if (strstr($text, "http://"))
    {
      $start = strpos($text, "http://");
      $end = substr($text, $start);
      if (strpos($end, " "))
        $link = substr($end, 0, strpos($end, " "));
      else
        $link = substr($end, 0, strlen($end));
      if (strpos($end, "<"))
        $link = substr($end, 0, strpos($end, "<"));
      
      $text = substr($text, 0, $start) .
                 "<a href=\"$link\" target=\"_blank\">$link</a>" .
                  substr($text, $start+strlen($link));
        
    }
    else if (strstr($text, " www."))
    {
      $start = strpos($text, "www.");
      $end = substr($text, $start);
      if (strpos($end, " "))
        $link = substr($end, 0, strpos($end, " "));
      else
        $link = substr($end, 0, strlen($end));
      if (strpos($end, "<"))
        $link = substr($end, 0, strpos($end, "<"));
      $text = substr($text, 0, $start) .
            "<a href=\"http://$link\" target=\"_blank\">$link</a>" .
            substr($text, $start+strlen($link));
    }
  }
  return $text;
}

function closetags($text)
{
  $tags = array("font", "b", "i", "span", "h1", "h2", "h3", "h4", "h5",
"h6", "center", "marquee");
  foreach ($tags as $tag)
    if (stristr($text, "<$tag"))
    {
      $temp = substr($text, strrpos(strtolower($text), "<$tag"));
      if (!strpos(strtolower($temp), "</$tag>"))
        $text .= " </$tag>";
    }

  if (stristr($text, "marquee"))
  {
    $t = strpos(strtolower($text), "<marquee");
    $temp = substr($text, 0, $t);
    $temp .= substr(substr($text, $t), strpos(substr($text, $t), ">"));
    $text = temp;
  }

  $text = str_replace("\n", "<br>", $text);
  $text = str_replace("\t", "&nbsp; &nbsp; &nbsp; ", $text);

  return $text;
}

function prepare($text)
{
  return fixLinks(iconify(closetags($text)));
}


?>
