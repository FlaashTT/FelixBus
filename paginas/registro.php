<html>
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
<body>
    <h2>Registar</h2>
    <form action="registro.php" method="POST">
        <label for="username">Nome de utilizador</label><br>
        <input type="text" placeholder="Enter Username" name="username" required ><br>

        <label for="email">Email</label><br>
        <input type="text" placeholder="Enter Email" name="email" required><br>

        <label for="password">Senha</label><br>
        <input type="password" placeholder="Enter Password" name="password" required><br>

        

        <button type="submit" name="login">Efetuar registo</button><br>
        <a href="login.php"><b>Ja tenho conta</b></a>
    </form>

</body>
</html>

<?php
include("../basedados/basedados.h");
session_start();

// Verifica se o formulário foi enviado com os campos 'email', 'password' e 'username'
if (!empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["username"])) {
    // Proteção contra SQL Injection
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = md5($_POST['password']); 
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Query para inserir os dados no banco
    $sql = "INSERT INTO `users`(`Nome`, `Email`, `Password`) VALUES ('$username', '$email', '$password')";

    // Executa a query e verifica se foi bem-sucedida
    if (mysqli_query($conn, $sql)) {
        echo "Usuário criado com sucesso!";
        header("Refresh: 1; url=login.php"); // Redireciona para a página de login
        exit;
    } else {
        echo "Erro ao criar usuário: " . mysqli_error($conn);
    }
}
// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>