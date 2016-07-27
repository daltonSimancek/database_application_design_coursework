<?php
require_once "pdo.php";
 // Do not put any HTML above this line
// session_start() and header() fail if any (even one
// character) of output has been sent.
session_start();
unset($_SESSION['name']);
unset($_SESSION['user_id']);
$_SESSION['autos'] = array();
$salt = 'XyZzy12*_';

if ( isset($_POST['email']) && isset($_POST['pass']) ) {
  if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
    $_SESSION['error'] = "Email and password are required";
    header("Location: index.php");
    exit();
} else {
    $check = hash('md5', $salt.$_POST['pass']);
    $stmt = $pdo->prepare('SELECT user_id, name FROM users
      WHERE email = :em AND password = :pw');
    $stmt->execute(array( ':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ( $row !== false ) {
        $_SESSION['name'] = $row['name'];
        $_SESSION['user_id'] = $row['user_id'];
        // Redirect the browser to autos.php
        header("Location: autos_db.php");
        exit();
  } else {
    $_SESSION['error'] = "Inocrrect Password";
    header("Location: index.php");
    exit();
  }
}
}


// Fall through into the View
?>
<!DOCTYPE html>
<html>
<head>
<title>Dalton Simancek's Login Page</title>
</head>
<body style="font-family: sans-serif;">
<h1>Please Log In</h1>
<?php

if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>
<form method="POST" action="index.php">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="text" name="pass" id="id_1723"><br/>
<input type="submit" value="Log In">
</form>

<ul>
<li> Try to <a href="autos_db.php">enter database</a> without
logging in.</li>

<ul>
</body>
