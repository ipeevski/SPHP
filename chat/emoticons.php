<html>
  <head>
    <title>Emoticons</title>
  </head>
  <body>
<?php
  $ext = array("jpg", "png", "jpeg", "gif");
  $files = array();
  $dirname = "images";
  $dir = opendir($dirname);

  $no = 20;
  if (!empty($_GET['page']))
    $page = $_GET['page'];
  else
    $page = 0;
  $start = $page*$no;

  while(false != ($file = readdir($dir)))
    for ($i = 0; $i < count($ext); $i++)
      if (eregi("\.". $ext[$i] ."$", $file))
      {
        $words[] = substr($file, 0, strpos($file, "."));
        $files[] = $file;
      }

    closedir($dir);
//    sort($files);

  for($i = $start; $i < count($words) && $i < $start+$no; $i++)
    echo $words[$i] . " - <img src=\"$dirname\\". $files[$i] ."\"><br>\n";
  if (count($words) > ($page+1)*$no)
    echo "<a href=\"emoticons.php?page=" . ($page+1) . "\">next >></a>\n";
?>
  </body>
</html>
