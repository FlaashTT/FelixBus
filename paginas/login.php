<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FelixBus - Login</title>
  <link rel="stylesheet" href="../paginas/login.css">
</head>

<body>
  <form action="login.php" method="POST">
    <img src="../paginas/Felixbus.png" alt="Logo" id="LOGO">
    <input type="email" id="email" name="email" placeholder="Introduzir Email" required />
    <input type="password" id="password" name="password" placeholder="Introduzir Password" required />
    <a href="../paginas/" id="esqueci-senha-login">Esqueci da Password?</a>
    <input type="submit" name="login" value="Login">
    <a href="registro.php" id="esqueci-registrar-conta" ><b>Não tenho Conta!</b></a>
    <a href="inicio.php" id="pagina-inicial"><b>Pagina Inicial</b></a>
  </form>
</body>

</html>



<?php
include("../basedados/basedados.h");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
        $email = $_POST['email'];
        $password = hash('sha256', $_POST['password']); 

        $stmt = $conn->prepare("SELECT * FROM `utilizadores` WHERE `Email` = ? AND `Password` = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Login bem-sucedido
            $utilizador = $result->fetch_assoc(); 
            $_SESSION['utilizador'] = $utilizador;    

            // Verificar o papel do usuário
            if ($utilizador['Cargo'] === 'Admin') {
                $_SESSION['Admin'] = $utilizador; 

                $statusQuery = "UPDATE `utilizadores` SET `Estado` = 'Online' WHERE `Email` = ?";
                $statusStmt = $conn->prepare($statusQuery);
                $statusStmt->bind_param("s", $email);
                $statusStmt->execute();
                $statusStmt->close();

                header("Location: ../paginas/inicio.php"); 
            } else if ($utilizador['Cargo'] === 'Funcionario') {
                $_SESSION['Funcionario'] = $utilizador; 

                $statusQuery = "UPDATE `utilizadores` SET `Estado` = 'Online' WHERE `Email` = ?";
                $statusStmt = $conn->prepare($statusQuery);
                $statusStmt->bind_param("s", $email);
                $statusStmt->execute();
                $statusStmt->close();

                header("Location: ../paginas/inicio.php"); 
            } elseif ($utilizador['Cargo'] === 'Cliente') {
                if ($utilizador['Autenticacao'] === 'Pendente') {
                    echo "<script>alert('Erro ao tentar iniciar sessão. Seu perfil está pendente e requer aprovação.'); 
                        window.location.href = '../paginas/login.php';</script>";
                } elseif ($utilizador['Autenticacao'] === 'Rejeitado') {
                    echo "<script>alert('Erro ao tentar iniciar sessão. Sua conta foi rejeitada pelo administrador.'); 
                        window.location.href = '../paginas/login.php';</script>";
                } elseif ($utilizador['Autenticacao'] === 'Eliminado') {
                            echo "<script>alert('Erro ao tentar iniciar sessão. Sua conta foi eliminada.'); 
                                window.location.href = '../paginas/login.php';</script>";
                } elseif ($utilizador['Autenticacao'] === 'Aceite') {
                    $_SESSION['Client'] = $utilizador;
                    
                    $statusQuery = "UPDATE `utilizadores` SET `Estado` = 'Online' WHERE `Email` = ?";
                    $statusStmt = $conn->prepare($statusQuery);
                    $statusStmt->bind_param("s", $email);
                    $statusStmt->execute();
                    $statusStmt->close();

                    header("Location: ../paginas/inicio.php");
                }
            }
            exit();
        } else {
            // Falha no login
            echo "<script>
                alert('Credenciais inválidas!\\nTente novamente.');
                window.location.href = '../paginas/login.php';
            </script>";
        }

        $stmt->close();
    } else {
        echo "<p style='color:red;'>Por favor, preencha todos os campos.</p>";
    }
}
?>