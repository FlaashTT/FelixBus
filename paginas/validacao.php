<?php

function validar_acesso($cargos_permitidos = []) {
    if (!isset($_SESSION['utilizador'])) {
        // Redireciona para o login se o utilizador não estiver autenticado
        header("Location: ../paginas/login.php");
        exit;
    }

    $cargoUser = $_SESSION['utilizador']['Cargo']; // Obtém o cargo do utilizador da sessão

    // Verifica se o cargo do utilizador está na lista de cargos permitidos
    if (!in_array($cargoUser, $cargos_permitidos)) {
        // Redireciona para a página inicial caso não tenha permissão
        header("Location: ../paginas/inicio.php");
        exit;
    }
}
?>
