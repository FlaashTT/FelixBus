<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FelixBus - Login</title>
  <link rel="stylesheet" href="../paginas/registro.css">
</head>

<body>
  <form action="registro.php" method="POST">
  <img src="../paginas/Felixbus.png" alt="Logo" id="LOGO">
    <input type="text" placeholder="Introduzir Username" name="username" required><br>
    <input type="email" placeholder="Introduzir Email" name="email" required><br>
    <input type="password" placeholder="Introduzir Password" name="password" required><br>
    <input type="submit" name="login" value="Criar Conta">
    <a href="login.php" id="ja-tenho-conta" ><b>Já Tenho Conta!</b></a>
    <a href="inicio.php" id="pagina-inicial"><b>Pagina Inicial</b></a>
  </form>

</body>

</html>

<?php
include("../basedados/basedados.h");
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["username"])) {
        
        // Sanitização dos inputs para prevenir XSS
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); 
        $username = htmlspecialchars(strip_tags($_POST['username']), ENT_QUOTES, 'UTF-8'); 
        $password = $_POST['password'];

        // Verifica se o e-mail é válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('O endereço de e-mail fornecido não é válido.'); window.location.href = 'registro.php';</script>";
            exit;
        }

        // Verifica se a senha tem pelo menos 5 caracteres
        if (strlen($password) < 5) {
            echo "<script>alert('A senha deve ter pelo menos 8 caracteres.'); window.location.href = 'registro.php';</script>";
            exit;
        }

        // Hash da password com SHA-256
        $hashedPassword = hash('sha256', $password);

        // Verifica se o e-mail já existe na base de dados
        $stmt = $conn->prepare("SELECT * FROM utilizadores WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('Já existe um utilizador com este e-mail.'); window.location.href = 'registro.php';</script>";
            exit;
        }

        // Inserção do novo utilizador com estado "Offline" e autenticação "Pendente"
        $cargo = "Cliente";
        $Autenticacao = "Pendente";
        $estado = "Offline";
        $stmt = $conn->prepare("INSERT INTO utilizadores (Nome, Email, Password, Cargo, Autenticacao, Estado) 
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $cargo, $Autenticacao, $estado);

        if ($stmt->execute()) {
            // Obtém o ID do utilizador recém-criado
            $userId = $stmt->insert_id;
            
            // Regista o alerta na tabela Alertas com os novos nomes de colunas
            $dataAtual = date('Y-m-d H:i:s');
            $alertStmt = $conn->prepare("INSERT INTO Alertas (Texto_Alerta, Data_Emissao, Id_Remetente, Tipo_Alerta) 
                                         VALUES ('Novo utilizador registado', ?, ?, 'Novo Registo')");
            $alertStmt->bind_param("si", $dataAtual, $userId);

            if ($alertStmt->execute()) {
                echo "<script>alert('Registo efetuado com sucesso! Aguarde a aprovação do administrador.'); window.location.href = 'login.php';</script>";
                exit;
            } else {
                echo "<script>alert('Erro ao criar o alerta.'); window.location.href = 'registro.php';</script>";
            }
        } else {
            echo "<script>alert('Erro ao criar o utilizador.'); window.location.href = 'registro.php';</script>";
        }

        $stmt->close();
        $alertStmt->close();
    } else {
        echo "<script>alert('Por favor, preencha todos os campos!'); window.location.href = 'registro.php';</script>";
    }
}

// Fecha a conexão
$conn->close();
?>


