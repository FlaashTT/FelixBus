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

// Verifica se foi enviado um valor para adicionar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valor'])) {
    $valor = $_POST['valor'];

    // Validação - o valor não pode ser negativo ou zero
    if ($valor > 0) {
        // Atualiza o saldo do utilizador
        $sql = "UPDATE utilizadores SET Saldo = Saldo + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $valor, $userId);

        if ($stmt->execute()) {
            $msg = "<p class='success-msg'>Saldo adicionado com sucesso!</p>";
        } else {
            $msg = "<p class='error-msg'>Erro ao adicionar saldo. Tente novamente.</p>";
        }
    } else {
        $msg = "<p class='error-msg'>O valor deve ser maior que 0.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Saldo - FelixBus</title>
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

        input[type="number"] {
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
            echo '<a href="alertas.php">Alertas</a>';
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
        }
        ?>
    </div>

    <div class="content">

        <h1>Adicionar Saldo</h1>
        <div class="form-container">

            <?php
            // Exibe a mensagem de sucesso ou erro
            if (isset($msg)) {
                echo $msg;
            }
            ?>

            <form method="POST">
                <label for="valor">Valor a Adicionar:</label>
                <input type="number" name="valor" required>
                <button type="submit">Adicionar Saldo</button>
                <a href="perfil.php">
                    <button class="cancel-btn" type="button">Cancelar</button>
                </a>
            </form>
        </div>

    </div>
    <script>
        function updateTime() {
            const date = new Date();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            document.getElementById('hora').textContent = hours + ":" + minutes + ":" + seconds;
        }
        setInterval(updateTime, 1000);
        updateTime(); 
    </script>

</body>

</html>
