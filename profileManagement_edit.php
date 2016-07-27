<?php
require_once "pdo.php";
require_once "util.php";

session_start();

// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

// Make sure the REQUEST parameter is present
if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    exit();
}

// Load profile
$stmt = $pdo->prepare('SELECT * FROM Profile
    WHERE profile_id = :prof AND user_id = :uid');
$stmt->execute(array( ':prof' => $_REQUEST['profile_id'],
    ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = "Could not load profile";
    header('Location: index.php');
    exit();
}



// If the user requested to edit data to the database
if ( isset($_POST['save']) && isset($_POST['profile_id']) ) {

    //Validate incoming data
    $pro_validation = validateProfile();
    if ($pro_validation !== true ) {
        $_SESSION['error'] = $pro_validation;
        header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
        exit();
      }

      // Validate position entries if present
      $pos_validation = validatePos();
      if ( is_string($pos_validation) ) {
          $_SESSION['error'] = $pos_validation;
          header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
          exit();
      }


      $sql = "UPDATE Profile SET
          first_name = :fn, last_name = :ln, email = :em, headline = :hl, summary = :sum WHERE profile_id = :pro AND user_id = :uid";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(
        ':pro' => $_REQUEST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':hl' => $_POST['headline'],
        ':sum' => $_POST['summary']));


      // Clear out the old position entries
  $stmt = $pdo->prepare('DELETE FROM Position
      WHERE profile_id=:pid');
  $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

  // Insert the position entries
  $rank = 1;
  for($i=1; $i<=9; $i++) {
      if ( ! isset($_POST['year'.$i]) ) continue;
      if ( ! isset($_POST['description'.$i]) ) continue;
      $year = $_POST['year'.$i];
      $description = $_POST['description'.$i];

      $stmt = $pdo->prepare('INSERT INTO Position
          (profile_id, rank, year, description)
      VALUES ( :pid, :rank, :year, :description)');
      $stmt->execute(array(
          ':pid' => $_REQUEST['profile_id'],
          ':rank' => $rank,
          ':year' => $year,
          ':description' => $description)
      );
      $rank++;
  }
  $_SESSION['success'] = 'Profile Edited';
  header("Location: edit.php?profile_id=" . $_REQUEST["profile_id"]);
  return;
}

// Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);

$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: edit.php' ) ;
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Editing Profile for Dalton</title>
</head>
<body style="font-family: sans-serif;">
<h1>Editing Profile for Dalton</h1>

<?php

//FLASH MESSAGES
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red;">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}

if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green;">'.htmlentities($_SESSION['success'])."</p>\n");
    unset($_SESSION['success']);
}
?>

<form method="post">
<p>First Name:
  <input type="text" name="first_name" size="60" value="<?= htmlentities($row['first_name']) ?>"></p>
<p>Last Name:
<input type="text" name="last_name"  size="60" value="<?= htmlentities($row['last_name']) ?>"></p>
<p>Email:
<input type="text" name="email" value="<?= htmlentities($row['email']) ?>"></p>
<p>Headline:
<input type="text" name="headline" value="<?= htmlentities($row['headline']) ?>"></p>
<p>Summary:
  <input type="text" name="summary"  value="<?= htmlentities($row['summary']) ?>"></p>

<?php

$pos = 0;
echo('<p>Position: <input type="submit" onclick="addPos(); return false;" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach( $positions as $position ) {
        $pos++;
    	echo('<div class="position">');
        echo
'<p>Year: <input type="text" name="year'.$pos.'" value="'.$position['year'].'" />
<input type="button" value="-" onclick="removePos(this)"></p>';
        echo '<textarea name="description'.$pos.'" rows="8" cols="80">'."\n";
        echo htmlentities($position['description'])."\n";
        echo "\n</textarea>\n</div>\n";
}
echo("</div></p>\n");
?>


<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<p><input type="submit" value="Save" name="save"/>
<a href="index.php">Cancel</a></p>
</form>

<script>
//Count is a global variable.
count = <?= $pos ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
function addPos() {
  // Registers in console that position is being added
    window.console && console.log("Adding position");

//  Can't add more than 9 elements per (page view)
    if ( count >= 9 ) {
        alert("Maximum of nine position entries exceeded");
        return;
    }
  //Increments count by 1 for every click of pressing add
    count++;

//creates a div element with year, a - button that removes the position and a text area for a description
    var div = document.createElement('div');
    div.className = 'position';
    div.innerHTML =
'<p>Year: <input type="text" name="year'+count+'" value="" /> \
<input type="button" value="-" onclick="removePos(this)"></p>\
<textarea name="description'+count+'" rows="8" cols="80"></textarea>';

     document.getElementById('position_fields').appendChild(div);

}

function removePos(input) {
    document.getElementById('position_fields').removeChild( input.parentNode.parentNode );
}

</script>
</html>
