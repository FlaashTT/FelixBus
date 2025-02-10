<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo'];
    $userId = $utilizador['id'];  // ID do utilizador na sessão
} else {
    $cargoUser = "Visitante";
}

// Função para buscar bilhetes com base nos parâmetros fornecidos
function pesquisarBilhetes($de = '', $para = '', $dataIda = '')
{
    global $conn;

    // SQL para buscar bilhetes, rotas e veículos, incluindo bilhetes Cancelados ou Expirados
    $sql = "SELECT b.id_bilhete, r.origem, r.destino, b.data, b.hora, b.preco, v.nome_veiculo, v.Capacidade, b.lugaresComprados
        FROM bilhetes b
        JOIN rota r ON b.id_rota = r.id_rota
        JOIN veiculos v ON b.id_veiculo = v.id_veiculo
        WHERE b.estado_bilhete = 'Ativo'"; 

    // Adicionar filtros conforme a pesquisa
    if (!empty($de)) {
        $sql .= " AND r.origem LIKE '%$de%'";  // Filtro de origem
    }
    if (!empty($para)) {
        $sql .= " AND r.destino LIKE '%$para%'";  // Filtro de destino
    }
    if (!empty($dataIda)) {
        $sql .= " AND b.data = '$dataIda'";  // Filtro de data
    }

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die('Erro na consulta: ' . mysqli_error($conn));
    }

    return $result;
}

// Função para comprar bilhete
function comprarBilhete($idBilhete, $numPassageiros)
{
    global $conn, $userId, $msgCompra;

    // Obter os detalhes do bilhete, como o preço, capacidade do veículo e lugares já comprados
    $sql = "SELECT b.preco, b.id_veiculo, v.Capacidade, b.lugaresComprados 
            FROM bilhetes b 
            JOIN veiculos v ON b.id_veiculo = v.id_veiculo
            WHERE b.id_bilhete = ? AND b.estado_bilhete = 'Ativo'";  // Garantir que o bilhete está ativo

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idBilhete);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $msgCompra = "Bilhete não encontrado ou não está ativo.";
        return;
    }

    $bilhete = $result->fetch_assoc();

    // Verificar se há lugares disponíveis (capacidade - lugares comprados)
    $lugaresRestantes = $bilhete['Capacidade'] - $bilhete['lugaresComprados'];

    if ($lugaresRestantes < $numPassageiros) {
        $msgCompra = "Desculpe, não há lugares suficientes disponíveis para o número de bilhetes que deseja.";
        return;
    }

    // Verificar o saldo do utilizador
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
    $totalPreco = $bilhete['preco'] * $numPassageiros;
    if ($saldoAtual < $totalPreco) {
        $msgCompra = "Saldo insuficiente para comprar os bilhetes.";
        return;
    }

    // Subtrair o valor do bilhete do saldo
    $novoSaldo = $saldoAtual - $totalPreco;
    $sqlUpdateSaldo = "UPDATE utilizadores SET Saldo = ? WHERE id = ?";
    $stmtUpdateSaldo = $conn->prepare($sqlUpdateSaldo);
    $stmtUpdateSaldo->bind_param("di", $novoSaldo, $userId);

    if (!$stmtUpdateSaldo->execute()) {
        $msgCompra = "Erro ao atualizar o saldo: " . mysqli_error($conn);
        return;
    }

    // Atualizar o número de lugares comprados
    $sqlUpdateBilhete = "UPDATE bilhetes 
                     SET lugaresComprados = IFNULL(lugaresComprados, 0) + ? 
                     WHERE id_bilhete = ?";

    $stmtUpdateBilhete = $conn->prepare($sqlUpdateBilhete);
    $stmtUpdateBilhete->bind_param("ii", $numPassageiros, $idBilhete);

    if (!$stmtUpdateBilhete->execute()) {
        $msgCompra = "Erro ao atualizar o bilhete: " . mysqli_error($conn);
        return;
    }

    // Registrar a compra na tabela compras_bilhetes
    $sqlInsertCompra = "INSERT INTO compras_bilhetes (id_bilhete, id_utilizador, num_passageiros) 
                        VALUES (?, ?, ?)";
    $stmtInsertCompra = $conn->prepare($sqlInsertCompra);
    $stmtInsertCompra->bind_param("iii", $idBilhete, $userId, $numPassageiros);

    if (!$stmtInsertCompra->execute()) {
        $msgCompra = "Erro ao registrar a compra: " . mysqli_error($conn);
        return;
    }

    $msgCompra = "Bilhete(s) comprado(s) com sucesso!";
}

// Verificar se a compra foi acionada
$msgCompra = ''; // Mensagem de feedback
if (isset($_POST['comprarBilhete']) && isset($_POST['idBilhete']) && isset($_POST['passageiros'])) {
    $idBilhete = $_POST['idBilhete'];
    $numPassageiros = $_POST['passageiros'];
    comprarBilhete($idBilhete, $numPassageiros);
}

$de = isset($_POST['de']) ? $_POST['de'] : '';
$para = isset($_POST['para']) ? $_POST['para'] : '';
$dataIda = isset($_POST['dataIda']) ? $_POST['dataIda'] : '';
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa de Bilhetes</title>
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
        <h1>Resultados da Pesquisa</h1>

        <div class="content-Rotas-Pesquisa">

            <?php
            if (empty($de) && empty($para) && empty($dataIda)) {
                echo "<p>Por favor, preencha ao menos um campo de pesquisa.</p>";
            } else {
                $result = pesquisarBilhetes($de, $para, $dataIda);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '
                            <div class="resultado-card">
                                <div class="locais">
                                    <span>De: ' . $row['origem'] . '</span>
                                    <span>Para: ' . $row['destino'] . '</span>
                                </div>
                                <div class="horarios">
                                    <span>Hora de Saída: ' . substr($row['hora'], 0, 5) . ' h</span>
                                    <span>Data: ' . $row['data'] . '</span>
                                </div>
                                <div class="preco-e-btn">
                                    <span class="preco">' . $row['preco'] . '€</span>
                                    <form method="POST">
                                        <input type="hidden" name="idBilhete" value="' . $row['id_bilhete'] . '">
                                        
                                        <!-- Campo para o número de lugares -->
                                        <label for="passageiros">Quantos lugares deseja comprar?</label>
                                        <input class="texto-Adicionar" type="number" name="passageiros" min="1" max="100' . ($row['Capacidade'] - $row['lugaresComprados']) . '" value="1" required>
                
                                        <button class="btn-continuar" type="submit" name="comprarBilhete">Comprar Bilhete</button>
                                    </form>
                                </div>
                            </div>
                        ';
                    }
                } else {
                    echo "<p>Não foram encontrados bilhetes para essa pesquisa.</p>";
                }
            }

            if (!empty($msgCompra)) {
                echo "<p>$msgCompra</p>";
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