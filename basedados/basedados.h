<?php

$database = 'felixbuslr24';
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';


$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $database);


if (!$conn) {
    die('Erro na conexão: ' . mysqli_connect_error());
}


?>