<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");


// Apenas Funcionários e Administradores podem acessar esta página
validar_acesso(['Admin']);

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo']; // Obtém o cargo do utilizador
} else {
    // Se não estiver autenticado, assume como visitante
    $cargoUser = "Visitante";
}

// Função para criar alerta no sistema
function criar_alerta($mensagem, $tipo)
{
    global $conn;
    if (!isset($_SESSION['utilizador']['id'])) return;  // Verifica se o ID do utilizador está na sessão

    $idRemetente = $_SESSION['utilizador']['id'];
    $dataAtual = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO Alertas (Texto_Alerta, Data_Emissao, Id_Remetente, Tipo_Alerta) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $mensagem, $dataAtual, $idRemetente, $tipo);

    // Execute a query uma vez e verifique se a inserção foi bem-sucedida
    if ($stmt->execute()) {
        return true;  
    } else {
        return "Erro ao criar alerta: " . $stmt->error;  
    }
}


// Aceitar Pedido
if (isset($_POST['AceitarPedido'])) {
    $user_email = $_POST['AceitarPedido'];
    $sql = "UPDATE utilizadores SET Autenticacao = 'Aceite' WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);

    if ($stmt->execute()) {
        criar_alerta("O utilizador com email $user_email foi aceito", "Aceitar Utilizador");
        $mensagemSucesso = "Utilizador aceito com sucesso!";
    } else {
        $mensagemErro = "Erro ao aceitar o utilizador.";
    }
}

// Rejeitar Pedido
if (isset($_POST['RejeitarPedido'])) {
    $user_email = $_POST['RejeitarPedido'];
    $sql = "UPDATE utilizadores SET Autenticacao = 'Rejeitado' WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_email);

    if ($stmt->execute()) {
        criar_alerta("O utilizador com email $user_email foi rejeitado", "Rejeitar Utilizador");
        $mensagemSucesso = "Utilizador rejeitado com sucesso!";
    } else {
        $mensagemErro = "Erro ao rejeitar o utilizador.";
    }
}

// Obter pedidos pendentes
$sql = "SELECT * FROM utilizadores WHERE Autenticacao = 'Pendente'";
$pedidosPendentes = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Pedidos</title>
    <link rel="stylesheet" href="../paginas/menu.css">
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
        <h1>Gestão de Pedidos</h1>
        <h2>Pedidos Pendentes</h2>

        <!-- Mensagens de sucesso/erro -->
        <?php
        if (isset($mensagemSucesso)) {
            echo "<p class='sucesso'>$mensagemSucesso</p>";
        }
        if (isset($mensagemErro)) {
            echo "<p class='erro'>$mensagemErro</p>";
        }
        ?>

        <!-- Exibir pedidos pendentes -->
        <div class="grid-container">
            <?php
            if (mysqli_num_rows($pedidosPendentes) > 0) {
                while ($user = mysqli_fetch_assoc($pedidosPendentes)) {
                    echo "
                    <div class='card-Inicio'>
                        <h3>" . $user['Nome'] . "</h3>
                        <p><b>Email:</b> " . $user['Email'] . "</p>
                        <p><b>Data de Registo:</b> " . date('d-m-Y H:i', strtotime($user['data_registro'])) . "</p>
                        <form method='POST'>
                            <button class='aceitar-btn' type='submit' name='AceitarPedido' value='" . $user['Email'] . "'>Aceitar</button>
                            <button class='recusar-btn' type='submit' name='RejeitarPedido' value='" . $user['Email'] . "'>Rejeitar</button>
                        </form>
                    </div>";
                }
            } else {
                echo "<p class='sem-registo'>Sem pedidos pendentes.</p>";
            }
            ?>
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
