<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

date_default_timezone_set('Europe/Lisbon');

// Apenas Funcionários e Administradores podem acessar esta página
validar_acesso(['Funcionario', 'Admin']);

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo'];
} else {
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
        return true;  // Retorna true se a inserção for bem-sucedida
    } else {
        return "Erro ao criar alerta: " . $stmt->error;  // Retorna a mensagem de erro, caso haja um erro na execução
    }
}


?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestão de Veículos</title>
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
        }
        ?>
    </div>

    <div class="content">
        <h1>Gestão de Veículos</h1>
        <h2>Visão geral da frota</h2>

        <form action="" method="POST">
            <button class='adicionarRota-btn' type="submit" name="adicionarVeiculo">Adicionar Veículo</button>
        </form>

        <?php
        // FORMULÁRIO PARA ADICIONAR VEÍCULO
        if (isset($_POST['adicionarVeiculo'])) {
            echo "
            <form method='POST' action=''>
                <div class='card'>
                    <h1>Adicionar Veículo</h1>
                    <input class='texto-Adicionar' type='text' name='nomeVeiculo' placeholder='Nome do veículo' required>
                    <input class='texto-Adicionar' type='number' name='capacidade' placeholder='Capacidade' required>
                    <input class='texto-Adicionar' type='text' name='matricula' placeholder='Matrícula' required>
                    <button class='aceitar-btn' type='submit' name='ConfirmarAddVeiculo'>Adicionar</button>
                    <button class='recusar-btn' type='button' onclick='window.location.href=\"../paginas/gestao_veiculos.php\"'>Cancelar</button>
                </div>
            </form>";
        }

        // INSERIR VEÍCULO NO BD
        if (isset($_POST['ConfirmarAddVeiculo'])) {
            $nomeVeiculo = $_POST['nomeVeiculo'];
            $capacidade = $_POST['capacidade'];
            $matricula = $_POST['matricula'];

            $stmt = $conn->prepare("INSERT INTO Veiculos (Nome_Veiculo, Capacidade, Matricula) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $nomeVeiculo, $capacidade, $matricula);
            if ($stmt->execute()) {
                criar_alerta("Criou um novo veículo com matrícula: $matricula", "Criar Veículo");
                header("Refresh: 2; url=gestao_veiculos.php");
            } else {
                echo "Erro ao adicionar veículo.";
            }
        }

        // LISTAR VEÍCULOS
        $result = $conn->query("SELECT * FROM Veiculos");
        if ($result->num_rows > 0) {
            echo "<h1>Lista de Veículos</h1><div class='grid-container'>";
            while ($veiculo = $result->fetch_assoc()) {
                echo "
                <div class='grid-container-lado'>
                    <h1>ID: " . $veiculo['Id_Veiculo'] . "</h1>
                    <p>Nome: " . $veiculo['Nome_Veiculo'] . "</p>
                    <p>Capacidade: " . $veiculo['Capacidade'] . "</p>
                    <p>Matrícula: " . $veiculo['Matricula'] . "</p>
                    <form method='POST'>
                        <button class='aceitar-btn' type='submit' name='editarVeiculo' value='" . $veiculo['Id_Veiculo'] . "'>Editar</button>";
                if ($cargoUser === 'Admin') {
                    echo "<button class='recusar-btn' type='submit' name='eliminarVeiculo' value='" . $veiculo['Id_Veiculo'] . "'>Eliminar</button>";
                }
                echo "</form></div>";
            }
            echo "</div>";
        } else {
            echo "<p>Não há veículos inseridos.</p>";
        }

        // FORMULÁRIO DE EDIÇÃO
        if (isset($_POST['editarVeiculo'])) {
            $idVeiculo = $_POST['editarVeiculo'];
            $veiculo = $conn->query("SELECT * FROM Veiculos WHERE Id_Veiculo = $idVeiculo")->fetch_assoc();
            echo "
            <form method='POST' action='' >
            <div class='card'>
                    <h1>Editar Veículo</h1>
                    <input class='texto-Adicionar' type='hidden' name='idVeiculo' value='" . $veiculo['Id_Veiculo'] . "'>
                    <input class='texto-Adicionar' type='text' name='novoNomeVeiculo' value='" . $veiculo['Nome_Veiculo'] . "'>
                    <input class='texto-Adicionar' type='number' name='novaCapacidade' value='" . $veiculo['Capacidade'] . "'>
                    <input class='texto-Adicionar' type='text' name='novaMatricula' value='" . $veiculo['Matricula'] . "'>
                    <button class='aceitar-btn' type='submit' name='ConfirmarEditarVeiculo'>Guardar</button>
                    <button class='recusar-btn' type='button' onclick='window.location.href=\"../paginas/gestao_veiculos.php\"'>Cancelar</button>
                </div>
            </form>";
        }

        // ATUALIZAR VEÍCULO
        if (isset($_POST['ConfirmarEditarVeiculo'])) {
            $idVeiculo = $_POST['idVeiculo'];
            $novoNome = $_POST['novoNomeVeiculo'];
            $novaCapacidade = $_POST['novaCapacidade'];
            $novaMatricula = $_POST['novaMatricula'];

            $conn->query("UPDATE Veiculos SET Nome_Veiculo='$novoNome', Capacidade='$novaCapacidade', Matricula='$novaMatricula' WHERE Id_Veiculo='$idVeiculo'");
            criar_alerta("Editou o veículo ID $idVeiculo", "Editar Veículo");
            header("Refresh: 2; url=gestao_veiculos.php");
        }

        // ELIMINAR VEÍCULO
        if (isset($_POST['eliminarVeiculo'])) {
            $idVeiculo = $_POST['eliminarVeiculo'];

            echo '
    <form method="POST">
        <div class="card">
            <h2>Confirmar Eliminação</h2>
            <p>Tem a certeza que deseja eliminar o veículo com ID: ' . $idVeiculo . '?</p>
            <input type="hidden" name="confirmarEliminarVeiculo" value="' . $idVeiculo . '">
            <button class="recusar-btn" type="submit">Confirmar</button>
            <button class="aceitar-btn" type="button" onclick="window.location.href=\'gestao_veiculos.php\'">Cancelar</button>
        </div>
    </form>';
        }

        // PROCESSAR ELIMINAÇÃO
        if (isset($_POST['confirmarEliminarVeiculo'])) {
            $idVeiculo = $_POST['confirmarEliminarVeiculo'];

            // Apagar veículo
            $stmt = $conn->prepare("DELETE FROM Veiculos WHERE Id_Veiculo = ?");
            $stmt->bind_param("i", $idVeiculo);

            if ($stmt->execute()) {
                criar_alerta("Eliminou o veículo com ID: $idVeiculo", "Eliminar Veículo");
                echo "<p>Veículo eliminado com sucesso!</p>";
                header("Refresh: 2; url=gestao_veiculos.php");
            } else {
                echo "<p>Erro ao eliminar o veículo.</p>";
            }
        }
        ?>
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