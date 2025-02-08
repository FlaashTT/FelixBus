<?php
include("../basedados/basedados.h");
session_start();

$update_sql = "UPDATE utilizadores SET Estado = 'Offline' WHERE Estado = 'Online'";
$update_result = mysqli_query($conn, $update_sql);

// Verifica se a atualização foi bem-sucedida
if ($update_result) {
    // Redireciona para a página de início apos realizar logout
    session_destroy();
    header("Location: inicio.php") ;
    exit();
} else {
    echo "Erro ao atualizar o estado do usuário!";
}