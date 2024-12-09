<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
    }

    /* Centralizar o formulário */
    form {
      border: 3px solid #f1f1f1;
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
      margin-top: 50px;
    }

    /* Estilo dos inputs */
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 8px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    button {
      background-color: #04aa6d;
      color: white;
      padding: 14px;
      border: none;
      cursor: pointer;
      width: 100%;
      border-radius: 4px;
    }

    button:hover {
      background-color: #037f53;
    }

    a {
      display: block;
      text-align: center;
      margin-top: 10px;
      color: #04aa6d;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <h2>Login</h2>
  <form action="login.php" method="POST">
    <label for="email">Email</label>
    <input type="text" id="email" name="email" placeholder="Enter Email" required />

    <label for="password">Senha</label>
    <input type="password" id="password" name="password" placeholder="Enter Password" required />

    <button type="submit" name="login">Login</button>
    <a href="registro.php"><b>Não tenho Conta!</b></a>
  </form>
</body>

</html>



<?php
include("../basedados/basedados.h");
session_start();

if (!empty($_POST["email"]) && !empty($_POST['password'])) {
  // Obter valores do formulário
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $password = md5($_POST['password']);

  // Preparar a consulta SQL
  $sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
  $result = mysqli_query($conn, $sql);

  // Verificar se o usuário foi encontrado
  if ($result && mysqli_num_rows($result) > 0) {
    // Obter os dados do usuário
    $user = mysqli_fetch_assoc($result);
    $_SESSION['user'] = $user;


    if ($user['Autenticacao'] === 'Aceite') {
      // Atualizar o estado do usuário para "Online"
      $updateStatus = "UPDATE users SET Estado='Online' WHERE email='$email'";
      mysqli_query($conn, $updateStatus);

      $_SESSION['user']['Estado'] = 'Online';

      header("Refresh: 1; url=menu.php");
      exit;
    } elseif ($user['Autenticacao'] === 'Pendente') {
      echo "Seu acesso ainda não foi aprovado pelo administrador.";
      header("Refresh: 3; url=login.php");
    }else if($user['Autenticacao'] === 'Rejeitado'){
      echo "A sua conta foi negada.";
      header("Refresh: 3; url=login.php");
    }
  } else {
    echo "Email ou senha inválidos.";
  }
}

?>