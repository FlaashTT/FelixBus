<?php
include("../basedados/basedados.h");
session_start();

// Verifica se o formulário foi enviado com os campos 'email' e 'password'
if ($_POST["email"] != "" && $_POST["password"] != "" && $_POST["username"] != "") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $username = $_POST["username"];

    //fazer o insert na db
    //verificar se registou

    $sql = "INSERT INTO user(nome,password,email)  VALUES ('$username', '$password', '$email')";

    if (mysqli_query($conn, $sql)) {
        // Redireciona para a página de registo após 2 segundos
        echo "Usuário criado com sucesso!";
        header("Refresh: 2; url=login.html");
       
    } else {
        echo "Erro ao criar usuário: " . mysqli_error($conn);
    }
} else {
    echo "Preencha todos os campos!";
    header("Refresh: 2; url=registo.html");
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
