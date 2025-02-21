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
        return true; 
    } else {
        return "Erro ao criar alerta: " . $stmt->error;  
    }
}

?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestão de Rotas</title>
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
        <h1>Gestão de Rotas</h1>
        <h2>Visão geral das rotas</h2>

        <form action="" method="POST">
            <button class='adicionarRota-btn' type="submit" name="adicionarRota">Adicionar Rota</button>
        </form>

        <?php
        // FORMULÁRIO PARA ADICIONAR ROTA
        if (isset($_POST['adicionarRota'])) {
            echo "
            <form method='POST' action=''>
                <div class='card'>
                    <h1>Adicionar Rota</h1>
                    <input class='texto-Adicionar' type='text' name='nomeRota' placeholder='Nome da Rota' required>
                    <input class='texto-Adicionar' type='text' name='origem' placeholder='Origem' required>
                    <input class='texto-Adicionar' type='text' name='destino' placeholder='Destino' required>
                    <input class='texto-Adicionar' type='number' step='0.01' name='distancia' placeholder='Distância (km)' required>
                    <button class='aceitar-btn' type='submit' name='ConfirmarAddRota'>Adicionar</button>
                    <button class='recusar-btn' type='button' onclick='window.location.href=\"gestao_rotas.php\"'>Cancelar</button>
                </div>
            </form>";
        }

        // INSERIR ROTA NO BD
        if (isset($_POST['ConfirmarAddRota'])) {
            $stmt = $conn->prepare("INSERT INTO Rota (Nome_Rota, Origem, Destino, Distancia) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssd", $_POST['nomeRota'], $_POST['origem'], $_POST['destino'], $_POST['distancia']);
            if ($stmt->execute()) {
                criar_alerta("Criou uma nova rota: " . $_POST['nomeRota'], "Criar Rota");
                header("Refresh: 2; url=gestao_rotas.php");
            }
        }

        // LISTAR ROTAS
        $result = $conn->query("SELECT * FROM Rota");
        if ($result->num_rows > 0) {
            echo "<h1>Lista de Rotas</h1><div class='grid-container'>";
            while ($rota = $result->fetch_assoc()) {
                echo "
                <div class='grid-container-lado'>
                    <h1>ID: " . $rota['Id_Rota'] . "</h1>
                    <p>Nome: " . $rota['Nome_Rota'] . "</p>
                    <p>Origem: " . $rota['Origem'] . "</p>
                    <p>Destino: " . $rota['Destino'] . "</p>
                    <p>Distância: " . $rota['Distancia'] . " km</p>
                    <form method='POST'>
                        <button class='aceitar-btn' type='submit' name='editarRota' value='" . $rota['Id_Rota'] . "'>Editar</button>
                        <button class='recusar-btn' type='submit' name='eliminarRota' value='" . $rota['Id_Rota'] . "'>Eliminar</button>
                    </form>
                </div>";
            }
            echo "</div>";
        } else {
            echo "<p>Não há rotas cadastradas.</p>";
        }

        // FORMULÁRIO DE EDIÇÃO
        if (isset($_POST['editarRota'])) {
            $idRota = $_POST['editarRota'];
            $rota = $conn->query("SELECT * FROM Rota WHERE Id_Rota = $idRota")->fetch_assoc();
            echo "
            <form method='POST'>
                <div class='card'>
                    <h1>Editar Rota</h1>
                    <input class='texto-Adicionar' type='hidden' name='idRota' value='" . $rota['Id_Rota'] . "'>
                    <input class='texto-Adicionar' type='text' name='novoNomeRota' value='" . $rota['Nome_Rota'] . "' required>
                    <input class='texto-Adicionar' type='text' name='novaOrigem' value='" . $rota['Origem'] . "' required>
                    <input class='texto-Adicionar' type='text' name='novoDestino' value='" . $rota['Destino'] . "' required>
                    <input class='texto-Adicionar' type='number' step='0.01' name='novaDistancia' value='" . $rota['Distancia'] . "' required>
                    <button class='aceitar-btn' type='submit' name='ConfirmarEditarRota'>Guardar</button>
                    <button class='recusar-btn' type='button' onclick='window.location.href=\"gestao_rotas.php\"'>Cancelar</button>
                </div>
            </form>";
        }

        // ATUALIZAR ROTA
        if (isset($_POST['ConfirmarEditarRota'])) {
            $stmt = $conn->prepare("UPDATE Rota SET Nome_Rota=?, Origem=?, Destino=?, Distancia=? WHERE Id_Rota=?");
            $stmt->bind_param("sssdi", $_POST['novoNomeRota'], $_POST['novaOrigem'], $_POST['novoDestino'], $_POST['novaDistancia'], $_POST['idRota']);
            $stmt->execute();
            criar_alerta("Editou a rota ID " . $_POST['idRota'], "Editar Rota");
            header("Refresh: 2; url=gestao_rotas.php");
        }
        // ELIMINAR ROTA
        if (isset($_POST['eliminarRota'])) {
            $idRota = $_POST['eliminarRota'];

            echo '
            <form method="POST">
                <div class="card">
                    <h2>Confirmar Eliminação</h2>
                    <p>Tem a certeza que deseja eliminar a rota com ID: ' . $idRota . '?</p>
                    <input type="hidden" name="confirmarEliminarRota" value="' . $idRota . '">
                    <button class="recusar-btn" type="submit">Confirmar</button>
                    <button class="aceitar-btn" type="button" onclick="window.location.href=\'gestao_rotas.php\'">Cancelar</button>
                </div>
            </form>';
        }

        // PROCESSAR ELIMINAÇÃO
        if (isset($_POST['confirmarEliminarRota'])) {
            $idRota = $_POST['confirmarEliminarRota'];

            // Apagar rota da base de dados
            $stmt = $conn->prepare("DELETE FROM Rota WHERE Id_Rota = ?");
            $stmt->bind_param("i", $idRota);

            if ($stmt->execute()) {
                criar_alerta("Eliminou a rota com ID: $idRota", "Eliminar Rota");
                echo "<p>Rota eliminada com sucesso!</p>";
                header("Refresh: 2; url=gestao_rotas.php");
            } else {
                echo "<p>Erro ao eliminar a rota.</p>";
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