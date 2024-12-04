<html>
  <style>
    /* Bordered form */
    form {
      border: 3px solid #f1f1f1;
      margin-left: 800px;
      margin-right: 800px;
    }

    h2{
        margin-left: 800px;
        margin-right: 800px;
        align-items: center;
        justify-content: center;
        display: flex;
        margin-top: 300px;
    }

    a{
        margin-left: 10px;
        margin-right: 10px;
        align-items: center;
        justify-content: center;
        display: flex;
    }

    /* Full-width inputs */
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px 20px;
      margin: 8px 0;
      display: inline-block;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    /* Set a style for all buttons */
    button {
      background-color: #04aa6d;
      color: white;
      padding: 14px 20px;
      margin: 8px 0;
      border: none;
      cursor: pointer;
      width: 100%;
    }

    /* Add a hover effect for buttons */
    button:hover {
      opacity: 0.8;
    }
  </style>
  <body>
    <h2>Login</h2>
    <form action="login.php" method="POST">
      <label for="email"><b>Email:</b></label
      ><br />
      <input type="text" placeholder="Enter Email" name="email"  />
      <br />
      <label for="password"><b>Senha:</b></label
      ><br />
      <input type="password" placeholder="Enter Password" name="password" />
      <br />
      <button type="submit" name="login">Efetuar login</button><br />
      <a href="registo.html"><b>Nao tenho conta!</b></a>
    </form>
  </body>
</html>

<?php

//criptografar passes
//destruir sessoes
include("../basedados/basedados.h");  
session_start();

// Verifica se o formulário foi enviado com os campos 'email' e 'password'
if ($_POST["email"] != "" && $_POST["password"] != "") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql1 = "SELECT * FROM user WHERE estado='online'";
    $result1 = mysqli_query($conn, $sql1);
    if (mysqli_num_rows($result1) > 0 ) {
        $update_sql = "UPDATE user SET estado = 'offline' WHERE estado = 'online'";
        $update_result = mysqli_query($conn, $update_sql); //Atualiza o estado
        session_destroy();
    } 

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
            header("Refresh: 3; url=login.php");

        } elseif ($user['status'] === 'rejeitado') {
            echo "Seu pedido foi rejeitado pelo administrador. Entre em contato.";
            header("Refresh: 3; url=login.php");
        }
    } else {
        // Caso insira algum dado incorreto, exibe uma mensagem de erro
        echo "Email ou senha incorretos!";
        header("Refresh: 2; url=login.php");
    }
} else {
    // Caso os campos de email ou senha estejam vazios
    echo "Preencha todos os campos!";
    header("Refresh: 2; url=login.php");
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>