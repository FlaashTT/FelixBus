<?php
include("../basedados/basedados.h");  

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
        // Atualiza o estado do usuário para 'online'
        $update_sql = "UPDATE user SET estado = 'online' WHERE email = '$email'";
        $update_result = mysqli_query($conn, $update_sql);

        // Verifica se a atualização foi bem-sucedida
        if ($update_result) {
            // Redireciona para a página de início
            header("Location: inicio.php");
            exit(); 
        } else {
            echo "Erro ao atualizar o estado do usuário!";
        }
    } else {
        // Caso insira algum dado incorreto, exibe uma mensagem de erro
        echo "Email ou senha incorretos!";
        header("Refresh: 2; url=login.html");
    }
} else {
    // Caso os campos de email ou senha estejam vazios
    echo "Preencha todos os campos!";
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>
