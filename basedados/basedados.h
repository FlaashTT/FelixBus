<?php

$database ='felixbuspwbdlr';
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if(! $conn ){
die('Could not connect: ' . mysqli_error($conn));
}

mysqli_select_db($conn , $database);

$retval = mysqli_query($conn , $sql);//linha para correr a querry do sql


?>