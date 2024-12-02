<?php

//criptografar passes
//destruir sessoes
include("../basedados/basedados.h");  
session_start();

// Verifica se o formulário foi enviado com os campos 'email' e 'password'
if ($_POST["email"] != "" && $_POST["password"] != "") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Cria a consulta SQL para verificar se o email e a senha existem na tabela
    $sql = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";

    // Executa a consulta
    $result = mysqli_query($conn, $sql);

    // Verifica se a consulta retornou algum resultado
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if ($user['status'] === 'aprovado') {
            $update_sql = "UPDATE user SET estado = 'online' WHERE email = '$email'";
            $update_result = mysqli_query($conn, $update_sql); //Atualiza o estado
            header("Refresh: 1; url=PaginaPrincipal.php");

        } elseif ($user['status'] === 'pendente') {
            echo "Seu acesso ainda não foi aprovado pelo administrador.";
            header("Refresh: 3; url=login.html");

        } elseif ($user['status'] === 'rejeitado') {
            echo "Seu pedido foi rejeitado pelo administrador. Entre em contato.";
            header("Refresh: 3; url=login.html");
        }
    } else {
        // Caso insira algum dado incorreto, exibe uma mensagem de erro
        echo "Email ou senha incorretos!";
        header("Refresh: 2; url=login.html");
    }
} else {
    // Caso os campos de email ou senha estejam vazios
    echo "Preencha todos os campos!";
    header("Refresh: 2; url=login.html");
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>
