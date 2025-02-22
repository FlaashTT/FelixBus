<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

validar_acesso(['Funcionario', 'Admin', 'Cliente']);

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo']; // Obtém o cargo do utilizador
    $userId = $utilizador['id'];
} else {
    // Se não estiver autenticado, assume como visitante
    $cargoUser = "Visitante";
}

// Mensagem de feedback
$msg = "";

// Verifica se foi enviado o formulário para editar o perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha_antiga = $_POST['senha_antiga'];
    $nova_senha = $_POST['nova_senha'];

    // Buscar a senha atual na base de dados
    $sql = "SELECT Password FROM utilizadores WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($senha_bd);
    $stmt->fetch();
    $stmt->close();

    // Se a senha antiga for introduzida, verifica se está correta antes de permitir a atualização
    if (!empty($senha_antiga)) {
        if (hash('sha256', $senha_antiga) !== $senha_bd) {
            $msg = "<p class='error-msg'>Erro: A senha antiga não está correta.</p>";
        } else {
            // Se a senha antiga estiver correta, verifica se há uma nova senha
            if (!empty($nova_senha)) {
                $nova_senha_hash = hash('sha256', $nova_senha);
                $sql = "UPDATE utilizadores SET Nome = ?, Email = ?, Password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $nome, $email, $nova_senha_hash, $userId);
            } else {
                // Se não houver nova senha, apenas atualiza nome e email
                $sql = "UPDATE utilizadores SET Nome = ?, Email = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $nome, $email, $userId);
            }

            // Executa a atualização dos dados
            if ($stmt->execute()) {
                $msg = "<p class='success-msg'>Perfil atualizado com sucesso!</p>";
            } else {
                $msg = "<p class='error-msg'>Erro ao atualizar o perfil.</p>";
            }

            $stmt->close(); 
        }
    }
}

// Buscar os dados atuais do utilizador para exibir no formulário
$sql = "SELECT Nome, Email FROM utilizadores WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - FelixBus</title>
    <link rel="stylesheet" href="../paginas/menu.css">
    <style>
        .form-container {
            background-color: white;
            padding: 20px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            max-width: 500px;
            margin: 30px auto;
        }

        label {
            font-size: 16px;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #218838;
        }

        .success-msg {
            color: green;
            font-size: 16px;
            margin-top: 20px;
        }

        .error-msg {
            color: red;
            font-size: 16px;
            margin-top: 20px;
        }

        .cancel-btn {
            background-color: #dc3545;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            text-align: center;
            border-radius: 5px;
            border: none;
            font-size: 18px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="logo">FelixBus</div>
        <div class="Hora" id="hora"></div>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="inicio.php">Início</a>
        <?php
        if ($cargoUser !== "Visitante") {
            echo '<a href="rotas.php">Rotas</a>';
            echo '<a href="consultar_bilhetes.php">Bilhetes</a>';
            echo' <a href="alertas.php">Alertas</a>';
            echo '<a href="perfil.php">Perfil</a>';
        }
        if ($cargoUser === 'Funcionario' || $cargoUser === 'Admin') {
            echo '<a href="gestao_veiculos.php">Gestão Veículos</a>';
            echo '<a href="gestao_rotas.php">Gestão de Rotas</a>';
            echo '<a href="gestao_utilizadores.php">Gestão de Utilizadores</a>';
            echo '<a href="gestao_bilhetes.php">Gestão de Bilhetes</a>';
        }
        if ($cargoUser === 'Admin') {
            echo '<a href="gestao_pedidos.php">Gestão de Pedidos</a>';
            echo '<a href="gestao_alertas.php">Gestão de Alertas</a>';
        }
        if ($cargoUser !== "Visitante") {
            echo '<a href="logout.php" class="logout">Sair</a>';
        } else {
            echo '<a href="login.php" class="login-btn">Iniciar Sessão</a>';
            echo' <a href="alertas.php">Alertas</a>';
        }
        ?>
    </div>

    <div class="content">

        <h1>Editar Perfil</h1>
        <div class="form-container">

            <?php
            // Exibe a mensagem de sucesso ou erro
            if (isset($msg)) {
                echo $msg;
            }
            ?>

            <form method="POST">
                <label for="nome">Nome:</label>
                <input class='texto-Adicionar' type="text" name="nome" value="<?php echo $user['Nome']; ?>" required>

                <label for="email">Email:</label>
                <input class='texto-Adicionar' type="email" name="email" value="<?php echo $user['Email']; ?>" required>

                <label for="senha_antiga">Senha Atual:</label>
                <input class='texto-Adicionar' type="password" name="senha_antiga" required>

                <label for="nova_senha">Nova Senha (opcional):</label>
                <input class='texto-Adicionar' type="password" name="nova_senha">

                <button type="submit">Atualizar Perfil</button>
                <a href="perfil.php">
                    <button class="cancel-btn" type="button">Cancelar</button>
                </a>
            </form>
        </div>

    </div>

</body>

</html>