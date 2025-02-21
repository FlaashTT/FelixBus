<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo']; // Obtém o cargo do utilizador
    $userId = $utilizador['id'];  // ID do utilizador na sessão
} else {
    // Se não estiver autenticado, assume como visitante
    $cargoUser = "Visitante";
}

// Apenas utilizadores autenticados podem acessar
validar_acesso(['Cliente', 'Funcionario', 'Admin']);

function comprarBilhete($idBilhete, $userId, $numLugares)
{
    global $conn, $msgCompra;

    // Obter detalhes do bilhete
    $sql = "SELECT b.preco, b.id_veiculo, v.Capacidade, b.lugaresComprados 
            FROM bilhetes b 
            JOIN veiculos v ON b.id_veiculo = v.id_veiculo
            WHERE b.id_bilhete = ? AND b.estado_bilhete = 'Ativo'";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idBilhete);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $msgCompra = "Bilhete não encontrado ou não está ativo.";
        return;
    }

    $bilhete = $result->fetch_assoc();
    $lugaresRestantes = $bilhete['Capacidade'] - $bilhete['lugaresComprados'];

    if ($lugaresRestantes < $numLugares) {
        $msgCompra = "Desculpe, não há lugares suficientes disponíveis.";
        return;
    }

    $precoTotal = $bilhete['preco'] * $numLugares;

    // Verificar saldo do utilizador
    $sqlSaldo = "SELECT Saldo FROM utilizadores WHERE id = ?";
    $stmtSaldo = $conn->prepare($sqlSaldo);
    $stmtSaldo->bind_param("i", $userId);
    $stmtSaldo->execute();
    $resultSaldo = $stmtSaldo->get_result();

    if ($resultSaldo->num_rows == 0) {
        $msgCompra = "Erro ao obter saldo.";
        return;
    }

    $user = $resultSaldo->fetch_assoc();
    $saldoAtual = $user['Saldo'];

    // Verificar se o utilizador tem saldo suficiente
    if ($saldoAtual < $precoTotal) {
        $msgCompra = "Saldo insuficiente para comprar os bilhetes.";
        return;
    }

    // Atualizar saldo do utilizador
    $novoSaldo = $saldoAtual - $precoTotal;
    $sqlUpdateSaldo = "UPDATE utilizadores SET Saldo = ? WHERE id = ?";
    $stmtUpdateSaldo = $conn->prepare($sqlUpdateSaldo);
    $stmtUpdateSaldo->bind_param("di", $novoSaldo, $userId);

    if (!$stmtUpdateSaldo->execute()) {
        $msgCompra = "Erro ao atualizar o saldo.";
        return;
    }

    // Atualizar lugares comprados no bilhete
    $sqlUpdateBilhete = "UPDATE bilhetes SET lugaresComprados = lugaresComprados + ? WHERE id_bilhete = ?";
    $stmtUpdateBilhete = $conn->prepare($sqlUpdateBilhete);
    $stmtUpdateBilhete->bind_param("ii", $numLugares, $idBilhete);

    if (!$stmtUpdateBilhete->execute()) {
        $msgCompra = "Erro ao atualizar os lugares do bilhete.";
        return;
    }

    // Registrar a compra
    $sqlInsertCompra = "INSERT INTO compras_bilhetes (id_bilhete, id_utilizador, num_passageiros) VALUES (?, ?, ?)";
    $stmtInsertCompra = $conn->prepare($sqlInsertCompra);
    $stmtInsertCompra->bind_param("iii", $idBilhete, $userId, $numLugares);

    if (!$stmtInsertCompra->execute()) {
        $msgCompra = "Erro ao registrar a compra.";
        return;
    }

    $msgCompra = "Compra de $numLugares bilhete(s) efetuada com sucesso!";
}


?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Bilhetes</title>
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
        <div></div>
        <h1>Consultar Rotas</h1>

        <?php
        // Consulta para buscar todas as rotas disponíveis
        $sql = "SELECT b.id_bilhete, r.id_rota, r.Origem, r.Destino, b.data, b.hora, v.Capacidade, b.lugaresComprados, b.preco, b.data_criacao,
        (v.Capacidade - b.lugaresComprados) AS lugaresDisponiveis
        FROM bilhetes b
        INNER JOIN rota r ON b.id_rota = r.id_rota
        INNER JOIN veiculos v ON b.id_veiculo = v.id_veiculo
        WHERE b.estado_bilhete = 'Ativo'
        ORDER BY b.data ASC";

        $result = mysqli_query($conn, $sql);


        if (mysqli_num_rows($result) > 0) {
            echo "<table class='utilizadores-tabela'>";
            echo "<tr>
                <th>ID</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Preço</th>
                <th>Lugares Disponíveis</th>
                <th>Ação</th>
            </tr>";
        
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id_rota'] . "</td>";
                echo "<td>" . $row['Origem'] . "</td>";
                echo "<td>" . $row['Destino'] . "</td>";
                echo "<td>" . date('d-m-Y', strtotime($row['data'])) . "</td>";
                echo "<td>" . $row['hora'] . "</td>";
                echo "<td>" . number_format($row['preco'], 2, ',', '.') . "€</td>";
                echo "<td>" . max(0, $row['lugaresDisponiveis']) . "</td>"; // Garante que não exibe valores negativos
        
                echo "<td>
                        <form action='rotas.php' method='POST'>
                            <input type='hidden' name='idBilhete' value='" . $row['id_bilhete'] . "'>
                            <button class='aceitar-btn' type='submit' name='verBilhete' " . ($row['lugaresDisponiveis'] <= 0 ? 'disabled' : '') . ">
                                Comprar
                            </button>
                        </form>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Não existem rotas disponíveis no momento.</p>";
        }
        

        if (isset($_POST['verBilhete'])) {
            $idBilhete = $_POST['idBilhete'];
        
            // Consulta para obter os detalhes do bilhete selecionado
            $sqlDetalhes = "SELECT b.id_bilhete, r.Origem, r.Destino, b.data, b.hora, b.preco, v.Capacidade, b.lugaresComprados
                            FROM bilhetes b
                            INNER JOIN rota r ON b.id_rota = r.id_rota
                            INNER JOIN veiculos v ON b.id_veiculo = v.id_veiculo
                            WHERE b.id_bilhete = ?";
        
            $stmtDetalhes = $conn->prepare($sqlDetalhes);
            $stmtDetalhes->bind_param("i", $idBilhete);
            $stmtDetalhes->execute();
            $resultDetalhes = $stmtDetalhes->get_result();
        
            if ($resultDetalhes->num_rows > 0) {
                $bilhete = $resultDetalhes->fetch_assoc();
                
                // Atualiza os lugares disponíveis após a compra
                $lugaresDisponiveis = max(0, $bilhete['Capacidade'] - $bilhete['lugaresComprados']); 
        
                echo "
                <div class='card'>
                    <h2>Detalhes do Bilhete</h2>
                    <p><strong>Origem:</strong> {$bilhete['Origem']}</p>
                    <p><strong>Destino:</strong> {$bilhete['Destino']}</p>
                    <p><strong>Data:</strong> " . date('d-m-Y', strtotime($bilhete['data'])) . "</p>
                    <p><strong>Hora:</strong> {$bilhete['hora']}</p>
                    <p><strong>Preço por Lugar:</strong> " . number_format($bilhete['preco'], 2, ',', '.') . "€</p>
                    <p><strong>Lugares Disponíveis:</strong> $lugaresDisponiveis</p>
        
                    <form action='' method='POST'>
                        <input type='hidden' name='idBilhete' value='{$bilhete['id_bilhete']}'>
                        <label for='numLugares'><strong>Quantidade de Lugares:</strong></label>
                        <input type='number' name='numLugares' id='numLugares' min='1' max='$lugaresDisponiveis' value='1' required>
                        
                        <div class='botoes-acoes'>
                            <button class='confirmar-btn' type='submit' name='comprarbilhete'>Confirmar Compra</button>
                            <button class='cancelar-btn' type='submit' name='cancelar'>Cancelar</button>
                        </div>
                    </form>
                </div>";
            } else {
                echo "<p>Erro ao carregar os detalhes do bilhete.</p>";
            }
        }
        
        

        if (isset($_POST['comprarbilhete'])) {
            $idBilhete = $_POST['idBilhete'];
            $numLugares = intval($_POST['numLugares']);
        
            comprarBilhete($idBilhete, $userId, $numLugares);
        
            if (!empty($msgCompra)) {
                echo "<p>$msgCompra</p>";
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