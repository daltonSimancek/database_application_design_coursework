<?php
require_once "pdo.php";
session_start();

// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

// If the user requested to clear all data in the database
if ( isset($_POST['clear']) ) {
    $stmt = $pdo->prepare('DELETE FROM autos WHERE user_id = :uid');
    $stmt->execute(array( ':uid' => $_SESSION['user_id']) ) ;
    $_SESSION['autos'] = array();
    $_SESSION['success'] = "Database reset";
    header('Location: autos_db.php');
    exit();
}
// If the user requested logout go back to index.php
if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    exit();
}



// If the user requested to add data to the database
if ( isset($_POST['add']) ) {
  if ( !is_numeric($_POST['mileage']) || !is_numeric($_POST['year'])) {
    $_SESSION['error'] = "Mileage and year must be numeric";
    header("Location: autos_db.php");
    exit();
  } else if ( strlen($_POST['make']) < 1 ) {
      $_SESSION['error'] = "Make is required";
      header("Location: autos_db.php");
      exit();
    } else {
      $stmt = $pdo->prepare('INSERT INTO autos
          (user_id, make, year, mileage) VALUES ( :uid, :mk, :yr, :mi)');
      $stmt->execute(array(
          ':uid' => $_SESSION['user_id'],
          ':mk' => $_POST['make'],
          ':yr' => $_POST['year'],
          ':mi' => $_POST['mileage'])
      );
    }
  }
?>


<!DOCTYPE html>
<html>
<head>
<title>Dalton Simancek's Autos Database</title>
</head>
<body style="font-family: sans-serif;">
<h1>Tracking Autos for <?= htmlentities($_SESSION['name']); ?> </h1>
<?php
if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}

if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>
<form method="POST" action="autos_db.php">

<label for="make">Make:</label>
<input type="text" name="make" id="make" size="40"><br/>
<br/>
<label for="year">Year:</label>
<input type="text" name="year" id="year"><br/>
<br/>
<label for="mileage">Mileage:</label>
<input type="text" name="mileage" id="mileage"><br/>
<br/>

<input type="submit" name="add" value="Add">
<input type="submit" name="clear" value="Clear All">
<input type="submit" name="logout" value="Logout">
</form>

<h2>Automobiles</h2>


  <?php
  $stmt = $pdo->query("SELECT * FROM autos");
  echo '<table border="1">'."\n";

  while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
      echo "<tr><td>";
      echo($row['make']);
      echo("</td><td>");
      echo($row['year']);
      echo("</td><td>");
      echo($row['mileage']);
      echo("</td></tr>\n");
  }
  echo "</table>\n";?>

</body>
</html>
