<?php require_once('config.php');?>

<html>
  <head>
    <title><?php echo $title; ?></title>
    <style type="text/css">
      .border	{boder-style: solid; border-color: #006600; border-width: thin;}
    </style>
  </head>
  <body>

<?php
include('functions.php');

if (isset($_POST['passwd']) && $_POST['passwd'] == $pass)
{
  $q = my_mysql_query('select * from posts group by timestamp desc');
  $results = mysql_fetch_array($q);
  if ($_POST['message'] == $results['message'])
    echo 'Trying to double post! Go <a href="#">back to main</a> first.';
  else
  {
		foreach($emails as $email)
	    mail($email, "$title entry", $_POST['message'], "From: {$_POST['user']}");

    $flag = $_POST['flag'];
    if ($_FILES['userfile']['error'] == 2 || $_FILES['userfile']['error'] == 1)
      echo 'Attachment was too big. Maximum file size is 2M';
    else
      my_mysql_query("insert into posts(user, flags, title, message, attachment) values('{$_POST['user']}', '$flag', '{$_POST['title']}', '{$_POST['message']}', '{$_FILES['userfile']['name']}')")
			 or die('Error: '.mysql_error());
echo "insert into posts(user, flags, title, message, attachment) values('{$_POST['user']}', '$flag', '{$_POST['title']}', '{$_POST['message']}', '{$_FILES['userfile']['name']}')";
    $path = './upload/' . $_FILES['userfile']['name'];
    move_uploaded_file($_FILES['userfile']['tmp_name'], $path);
  }
}

$q = my_mysql_query('select * from posts order by timestamp desc');
echo '<div>';
$week = 0;
while($row = mysql_fetch_array($q))
{
//TODO: Fix this - make anchors for different weeks
  if ($row['timestamp'] < $week)
  $week += 0;
  echo '<div style="margin-left: 440px;">'.show_row($row, 'short') . '</div>';
}
echo '</div>';
?>

<div style="margin-left: 0px; position: absolute; top: 35px">
<form enctype="multipart/form-data" style="text-align: right" action="index.php" method="post">
  User: <input class="border" ype="field" name="user" /><br />
  Password: <input class="border" type="password" name="passwd" /><br />
  Flag: 
  <select class="border" name="flag">
    <option value="0">General</option>
    <option value="1">Important</option>
    <option value="2">Design</option>
    <option value="3">Programming</option>
    <option value="4">Documentation</option>
    <option value="5">Administrative</option>
  </select>
  Title: <input class="border" type="field" name="title"></input><br />
  <textarea class="border" rows="30" cols="40" name="message"></textarea><br />
  <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
  Attach file (2M max): <input name="userfile" type="file" /><br />
  <input type="submit" name="submit" value="Post" /><br />
</form>
</div>

  </body>
</html>
