
$dbhost = "";
$dbuser = "";
$dbpass = "";
$dbdatabase = "";
// Connecting to mysql database
$mysqli = new mysqli($dbhost, $dbuser, $dbpass) or die(mysql_error());

// Selecting database 
$mysqli->select_db($dbdatabase) or die(mysql_error());
?>
<?php
    // Connecting to mysql database with Role 8
    $mysqli = new mysqli('localhost', 'Fablabian', 'sbVaBEd3eW9dxmdb', 'fabapp') or die(mysql_error());
?>