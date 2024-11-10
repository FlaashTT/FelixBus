<?php
include("../basedados/basedados.h");

// Verifica se o formulário foi enviado com os campos 'email' e 'password'
if ($_POST["email"] != "" && $_POST["password"] != "" && $_POST["username"] != "") {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $username = $_POST["username"];

    //para ver quantos utilizadores tem e gerar um id automaticamente
    $sql = "SELECT * FROM user";
    $result = mysqli_query($conn, $sql);
    $num_users = mysqli_num_rows($result);
    $num_users = $num_users++;


    //fazer o insert na db
    //verificar se registou

    $sql = "INSERT INTO user(nome,password,email)  VALUES ('$username', '$password', '$email')";
    $result = mysqli_query($conn, $sql);

    if (mysqli_query($conn, $sql)) {
        // Redireciona para a página de registo após 2 segundos
        header("Refresh: 2; url=login.html");
        echo "Usuário criado com sucesso!";
    } else {
        echo "Erro ao criar usuário: " . mysqli_error($conn);
    }
} else {
    echo "Preencha todos os campos!";
    header("Refresh: 2; url=registo.html");
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
