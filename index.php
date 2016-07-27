<!DOCTYPE html>
<head><title>Dalton Simancek MD5 Cracker</title></head>
<body>
<h1>MD5 cracker</h1>
<p>This application takes an MD5 hash of a four 
digit pin and check all 10,000 possible four 
digit PINs to determine the PIN.</p>
<pre>
Debug Output:
<?php
$goodtext = "Not found";
// If there is no parameter, this code is all skipped
if ( isset($_GET['md5']) ) {
    $time_pre = microtime(true);
    $md5 = $_GET['md5'];

	// This is our numbers
	$num = "0123456789";
	$show = 15;
	
	//Outer loop goes through the numbers for the 
	//first position in our "possible pre-hash num
	for($i=0; $i<strlen($num); $i++ ) {
		$dig1 = $num[$i];   // The first of four digits
		
		// Our inner loop Not the use of new variables
        // $j and $dig2 
        for($j=0; $j<strlen($num); $j++ ) {
            $dig2 = $num[$j];  // Our second digit
            
        	// Our second inner loop Not the use of new variables
       		// $k and $dig3
        	for($k=0; $k<strlen($num); $k++ ) {
            	$dig3 = $num[$k];  // Our third digit
            	
        		// Our third inner loop Not the use of new variables
       			// $l and $dig4
        		for($l=0; $l<strlen($num); $l++ ) {
            		$dig4 = $num[$l];  // Our last digit
            		
            // Concatenate the two characters together to 
            // form the "possible" pre-hash text
            $try = $dig1.$dig2.$dig3.$dig4;
            
            // Run the hash and then check to see if we match
            $check = hash('md5', $try);
            if ( $check == $md5 ) {
                $goodtext = $try;
                break;   // Exit the inner loop
            }
            
            // Debug output until $show hits 0
            if ( $show > 0 ) {
                print "$check $try\n";
                $show = $show - 1;
                	}
                }
            }
        }
	}
                
    // Compute ellapsed time
    $time_post = microtime(true);
    print "Ellapsed time: ";
    print $time_post-$time_pre;
    print "\n";
}
?>
</pre>
<!-- Use the very short syntax and call htmlentities() -->
<p>Original Text: <?= htmlentities($goodtext); ?></p>
<form>
<input type="text" name="md5" size="60" />
<input type="submit" value="Crack MD5"/>
</form>
<ul>
<li><a href="index.php">Reset</a></li>
<li><a href="md5.php">MD5 Encoder</a></li>
<li><a href="makecode.php">MD5 Code Maker</a></li>
<li><a
href="https://github.com/csev/php-intro/tree/master/code/crack"
target="_blank">Source code for this application</a></li>
</ul>
</body>
</html>
