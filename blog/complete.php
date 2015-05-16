<?php require_once('config.php'); ?>

<html>
  <head>
    <title><?php echo $title; ?></title>
  </head>
  <body>

<?php
//  include("header.php");
include("functions.php");


if (!empty($_GET['id'])) 					// Show one thread
{
  $id = $_GET['id'];
  $q = my_mysql_query("select * from posts where id = '$id'");
}
else if (!empty($_GET['user'])) {	// Show all messages from a user
  $user = mysql_real_escape_string($_GET['user']);
  $q = my_mysql_query("select * from posts where user = '$user'");
}
else															// Show all messages
  $q = my_mysql_query("select * from posts order by timestamp desc");

if (isset($_POST['passwd']) && isset($id) && $_POST['passwd'] == $pass)
{
  $q1 = my_mysql_query("select * from replies group by timestamp desc");
  $results = mysql_fetch_array($q1);
  if ($_POST['message'] == $results['message'])
    echo "Trying to double post! Go to <a href=\"http://www.macs.unisa.edu.au/cyberhorse/log/\">main</a> first.";
  else
  {
    foreach ($emails as $email)
      mail($email, "$title entry", $_POST['message'], "From: {$_POST['user']}");
    my_mysql_query("insert into replies(parent, user, message) values('$id', '".$_POST['user']."', '".$_POST['message']."')") or die("Error: ".mysql_error());
  }
}

if (empty($_GET['user']))
  while ($row = mysql_fetch_array($q))
  {
    echo '<div style="margin-left: 440px;">'.show_row($row, "long").'</div>';

    $q1 = my_mysql_query("select * from replies where parent='$row[id]' order by timestamp asc");

    echo "<a style=\"margin-left: 440px\" href=\"index.php\">Go back to main</a>";
    echo "<div style=\"padding: 5px; margin-left: 420px; border: solid; border-width: 5px;\">";
    echo "<h1>Replies</h1>";
    while ($row1 = mysql_fetch_array($q1))
      echo show_row($row1, "long");
    echo "</div>";
  }
else
{
  while ($row = mysql_fetch_array($q))
    echo show_row($row, "long");
    $q1 = my_mysql_query("select * from replies where user='$_GET[user]' order by timestamp asc");
    echo "<h1>Replies</h1>";
    while ($row1 = mysql_fetch_array($q1))
      echo show_row($row1, "long");
} 

if (isset($id))
{
?>
<div style="margin-left: 0px; position: absolute; top: 35px">
<form style="text-align: right" action="complete.php?id=<?php echo $id; ?>" method="post">
  User: <input class="border" ype="field" name="user"></input><br />
  Password: <input class="border" type="password" name="passwd"></input><br />
  <textarea class="border" rows="30" cols="40" name="message"></textarea><br />
  <input type="submit" name="submit" value="Post"></input><br />
</form>
</div>
  </body>
</html>

<?php
}
?>
