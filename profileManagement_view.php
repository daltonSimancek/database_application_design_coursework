<?php
require_once "pdo.php";
require_once "util.php";

session_start();
?>


<!DOCTYPE html>
<html>
<head>
<title>Dalton's Profile View</title>
</head>
<body style="font-family: sans-serif;">
<h1>Profile information</h1>
<?php
$stmt = $pdo->prepare("SELECT * FROM Profile WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));

$row = $stmt->fetch(PDO::FETCH_ASSOC);
  echo ('<p>First Name:  '.$row['first_name'].'</p>');
  echo ('<p>Last Name:  '.$row['last_name'].'</p>');
  echo ('<p>Email:  '.$row['email'].'</p>');
  echo ('<p>Headline:  '.$row['headline'].'</p>');
  echo ('<p>Summary:  '.$row['summary'].'</p>');
  echo('<br>');

$positions = loadPos($pdo, $_REQUEST['profile_id']);
foreach($positions as $pos) {
  echo('<p>Position '.$pos['rank'].'</p>');
  echo('<p>Year: '.$pos['year'].'</p>');
  echo('<p>Description: '.$pos['description'].'</p>');
  echo('<br>');
}
?>

<a href="index.php">Done</a>
</body>
</html>
