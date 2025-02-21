<?php

$database = 'felixbusRecurso';
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';


// Estabelecer conexão com a base de dados
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $database);


if (!$conn) {
    die("Erro na conexão com a base de dados: " . mysqli_connect_error());
}


?>