<?php
include("../basedados/basedados.h");

$sql = "SELECT * FROM user WHERE estado = 'online'";
$result = mysqli_query($conn, $sql);
$update_sql = "UPDATE user SET estado = 'offline' WHERE estado = 'online'";
$update_result = mysqli_query($conn, $update_sql);

// Verifica se a atualização foi bem-sucedida
if ($update_result) {
    // Redireciona para a página de início apos realizar logout
    header("Location: login.html");
    exit();
} else {
    echo "Erro ao atualizar o estado do usuário!";
}
