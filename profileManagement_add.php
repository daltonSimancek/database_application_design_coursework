<?php
require_once "pdo.php";
require_once 'util.php';

session_start();

// If the user is not logged in redirect back to index.php
// with an error
if ( ! isset($_SESSION['name']) ) {
    die("ACCESS DENIED");
}

// If the user requested logout go back to index.php
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    exit();
}

// If the user requested to add data to the database
  if ( isset($_POST['add']) ) {
    //Profile Validation Check
    $pro_validation = validateProfile();
    if ($pro_validation !== true ) {
        $_SESSION['error'] = $pro_validation;
        header('Location: add.php');
        exit();
      } else {
          //Add Profile to database
          $stmt = $pdo->prepare('INSERT INTO Profile
            (user_id, first_name, last_name, email, headline, summary) VALUES ( :uid, :fn, :ln, :em, :hl, :sum)');
          $stmt->execute(array(
              ':uid' => $_SESSION['user_id'],
              ':fn' => $_POST['first_name'],
              ':ln' => $_POST['last_name'],
              ':em' => $_POST['email'],
              ':hl' => $_POST['headline'],
              ':sum' => $_POST['summary'])
          );
        }

// Load up the profile in question
$stmt = $pdo->prepare('SELECT profile_id FROM Profile
        WHERE first_name = :fn AND last_name = :ln');
        $stmt->execute(array( ':fn' => $_POST['first_name'], ':ln' => $_POST['last_name']));
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        if ( $profile === false ) {
           $_SESSION['error'] = "Could not load profile";
           header('Location: index.php');
           exit();
          }

          //Position Validation Check
          $pos_validation = validatePos();

          if ($pos_validation !== true ) {
              $_SESSION['error'] = $pos_validation;
                //Deletes existing profile data
                $sql = "DELETE FROM Profile WHERE profile_id = :pid";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(':pid' => $profile['profile_id']));
              header('Location: add.php');
              exit();
            } else {
          // Insert the position entries
          $rank = 1;
          for($i=1; $i<=9; $i++) {
              if ( ! isset($_POST['year'.$i]) ) continue;
              if ( ! isset($_POST['description'.$i]) ) continue;
              $year = $_POST['year'.$i];
              $desc = $_POST['description'.$i];

              $stmt = $pdo->prepare('INSERT INTO Position
                  (profile_id, rank, year, description)
              VALUES ( :pid, :rank, :year, :desc)');
              $stmt->execute(array(
                  ':pid' => $profile['profile_id'],
                  ':rank' => $rank,
                  ':year' => $year,
                  ':desc' => $desc)
              );
              $rank++;
          }
        }

          $_SESSION['success'] = 'Record Added';
          header( 'Location: index.php' ) ;
          return;
        }


?>



<!DOCTYPE html>
<html>
<head>
<title>Adding Profile for Dalton</title>
</head>
<body style="font-family: sans-serif;">
<h1>Adding Profile for Dalton</h1>

<?php
// Flash pattern
if ( isset($_SESSION['error']) ) {
    echo '<p style="color:red">'.$_SESSION['error']."</p>\n";
    unset($_SESSION['error']);
}
?>

<form method="POST" action="add.php">

<label for="make">First Name:</label>
<input type="text" name="first_name" id="first_name" size="40"><br/>
<br/>
<label for="mileage">Last Name:</label>
<input type="text" name="last_name" size="60" id="last_name"><br/>
<br/>
<label for="year">Email:</label>
<input type="text" name="email" id="email"><br/>
<br/>
<label for="mileage">Headline:</label>
<input type="text" name="headline" id="headline"><br/>
<br/>
<label for="mileage">Summary:</label>
<input type="text" name="summary" id="summary">
</textarea>
<br/>
<p>Position: <input type="submit" onclick="addPos(); return false;" value="+">
<div id="position_fields">
</div></p>

<input type="submit" name="add" value="Add">
<input type="submit" name="cancel" value="Cancel">
</form>

<script>
//Count is a global variable.
count = 0;
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

//creates a div element
    // , a - button that removes the position and a text area for a description
    var div = document.createElement('div');
    div.className = 'position';
    //div element contains a text field for year
    //div element contains a '-' button to remove position
    //div element contains a textarea
    div.innerHTML =
'<p>Year: <input type="text" name="year'+count+'" value="" /> \
<input type="button" value="-" onclick="removePos(this)"></p>\
<textarea name="description'+count+'" rows="8" cols="80"></textarea>';

    //div elemnt is appeneded position_fields section of the html form
     document.getElementById('position_fields').appendChild(div);

}

function removePos(input) {
    document.getElementById('position_fields').removeChild( input.parentNode.parentNode );
}

</script>
</html>
