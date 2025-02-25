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
$acessosPermitidos = ['Cliente', 'Funcionario', 'Admin'];
if (!in_array($cargoUser, $acessosPermitidos)) {
    echo "<script>alert('Acesso negado! Tem de iniciar sessão para continuar.'); 
    window.location.href = 'login.php';</script>";
    exit();
}

function criar_alerta($mensagem, $tipo, $idRemetente)
{
    global $conn;
    $dataAtual = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO alertas (Texto_Alerta, Data_Emissao, Id_Remetente, Tipo_Alerta) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $mensagem, $dataAtual, $idRemetente, $tipo);
    $stmt->execute();
}

// Definir variáveis de filtro
$origemFiltro = isset($_GET['origem']) ? $_GET['origem'] : '';
$destinoFiltro = isset($_GET['destino']) ? $_GET['destino'] : '';
$dataFiltro = isset($_GET['data']) ? $_GET['data'] : '';

// Paginação
$registosPorPagina = 5;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $registosPorPagina;

// Consulta para contar total de registos
$sqlTotal = "SELECT COUNT(*) as total FROM compras_bilhetes cb
             INNER JOIN bilhetes b ON cb.id_bilhete = b.id_bilhete
             INNER JOIN rota r ON b.id_rota = r.id_rota
             WHERE cb.id_utilizador = '$userId'";

if (!empty($origemFiltro)) {
    $sqlTotal .= " AND r.Origem LIKE '%$origemFiltro%'";
}
if (!empty($destinoFiltro)) {
    $sqlTotal .= " AND r.Destino LIKE '%$destinoFiltro%'";
}
if (!empty($dataFiltro)) {
    $sqlTotal .= " AND b.data = '$dataFiltro'";
}

$resultTotal = $conn->query($sqlTotal);
$totalRegistos = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistos / $registosPorPagina);
//funçao para arredondar valores

// Consulta para buscar as compras individualmente, utilizando um alias para os lugares comprados na compra
$sql = "SELECT b.*, r.*, cb.*, cb.id as id_compra, cb.lugaresComprados as compra_lugares
        FROM compras_bilhetes cb
        INNER JOIN bilhetes b ON cb.id_bilhete = b.id_bilhete
        INNER JOIN rota r ON b.id_rota = r.id_rota
        WHERE cb.id_utilizador = '$userId'";

if (!empty($origemFiltro)) {
    $sql .= " AND r.Origem LIKE '%$origemFiltro%'";
}
if (!empty($destinoFiltro)) {
    $sql .= " AND r.Destino LIKE '%$destinoFiltro%'";
}
if (!empty($dataFiltro)) {
    $sql .= " AND b.data = '$dataFiltro'";
}

$sql .= " ORDER BY cb.data_compra DESC LIMIT $offset, $registosPorPagina";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Bilhetes</title>
    <link rel="stylesheet" href="../paginas/menu.css">
</head>

<style>
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
            echo ' <a href="alertas.php">Alertas</a>';
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
        <h1>Consulta de Bilhetes</h1>

        <!-- Filtros -->
        <form class="filtros-form" method="GET">
            <input type="text" class="filtro" name="origem" placeholder="Origem" value="<?= htmlspecialchars($origemFiltro) ?>">
            <input type="text" class="filtro" name="destino" placeholder="Destino" value="<?= htmlspecialchars($destinoFiltro) ?>">
            <input type="date" class="filtro" name="data" value="<?= htmlspecialchars($dataFiltro) ?>">
            <button class="filtrar-btn" type="submit">Filtrar</button>
            <button class="limpar-btn" type="button" onclick="window.location.href='consultar_bilhetes.php'">Limpar Filtros</button>
        </form>

        <?php
        if ($result->num_rows > 0) {
            echo "<div class='grid-container'>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card-Inicio'>";
                echo "<p><strong>Bilhete ID:</strong> " . $row['id_bilhete'] . "</p>";
                echo "<p><strong>Data da Viagem:</strong> " . date('d-m-Y', strtotime($row['data'])) . "</p>";
                echo "<p><strong>Origem:</strong> " . $row['Origem'] . "</p>";
                echo "<p><strong>Destino:</strong> " . $row['Destino'] . "</p>";
                echo "<p><strong>Preço Unitário:</strong> " . number_format($row['preco'], 2, ',', '.') . "€</p>";
                echo "<p><strong>Lugares Comprados:</strong> " . $row['lugaresComprados'] . "</p>";
                echo "<p><strong>Total Pago:</strong> " . number_format($row['lugaresComprados'] * $row['preco'], 2, ',', '.') . "€</p>";

                if ($row['estado_bilhete'] === "Expirado" || $row['estado_bilhete'] === "Cancelado") {
                    echo "<form method='POST'>
                        <button class='eliminar-btn' type='submit' name='eliminarBilhete' value='" . $row['id_bilhete'] . "'>Eliminar Bilhete</button>
                    </form>";
                } else {
                    echo "<form method='POST'>
                        <input type='hidden' name='valorAReceber' value='" . $row['preco'] . "'>
                        <button class='aceitar-btn' type='submit' name='reembolsarBilhete' value='" . $row['id_bilhete'] . "'>Reembolsar Bilhete</button>
                    </form>";
                }

                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>Não existem bilhetes comprados no momento.</p>";
        }

        // Eliminar bilhete
        if (isset($_POST['eliminarBilhete'])) {
            $idCompra = $_POST['eliminarBilhete'];
            // Buscar os dados da compra (id_bilhete e quantidade adquirida)
            $sqlBuscaCompra = "SELECT id_bilhete, lugaresComprados FROM compras_bilhetes WHERE id = ?";
            $stmtBusca = $conn->prepare($sqlBuscaCompra);
            $stmtBusca->bind_param("i", $idCompra);
            $stmtBusca->execute();
            $resultBusca = $stmtBusca->get_result();

            if ($resultBusca->num_rows > 0) {
                $compra = $resultBusca->fetch_assoc();
                $idBilhete = $compra['id_bilhete'];
                $lugaresCompra = $compra['lugaresComprados'];

                // Atualizar os lugares disponíveis no bilhete
                $sqlUpdateBilhete = "UPDATE bilhetes SET lugaresComprados = lugaresComprados - ? WHERE id_bilhete = ? AND lugaresComprados >= ?";
                $stmtUpdate = $conn->prepare($sqlUpdateBilhete);
                $stmtUpdate->bind_param("iii", $lugaresCompra, $idBilhete, $lugaresCompra);
                if ($stmtUpdate->execute()) {
                    // Eliminar a compra na tabela compras_bilhetes
                    $sqlDeleteCompra = "DELETE FROM compras_bilhetes WHERE id = ?";
                    $stmtDelete = $conn->prepare($sqlDeleteCompra);
                    $stmtDelete->bind_param("i", $idCompra);
                    if ($stmtDelete->execute()) {
                        echo "<p class='success-msg'>Bilhete removido do perfil com sucesso!</p>";
                        header("Refresh: 2; url=perfil.php");
                    } else {
                        echo "<p class='error-msg'>Erro ao remover a compra do bilhete.</p>";
                    }
                } else {
                    echo "<p class='error-msg'>Erro ao atualizar os lugares do bilhete.</p>";
                }
            } else {
                echo "<p class='error-msg'>Compra não encontrada.</p>";
            }
        }

        // Reembolsar bilhete
        if (isset($_POST['reembolsarBilhete']) && isset($_POST['valorAReceber'])) {
            $idCompra = $_POST['reembolsarBilhete'];
            $valor = $_POST['valorAReceber'];  // Preço unitário do bilhete

            // Buscar a compra com seus detalhes
            $sqlVerificaCompra = "SELECT cb.lugaresComprados, b.preco, r.Origem, r.Destino, b.id_bilhete 
                                  FROM compras_bilhetes cb
                                  INNER JOIN bilhetes b ON cb.id_bilhete = b.id_bilhete
                                  INNER JOIN rota r ON b.id_rota = r.id_rota
                                  WHERE cb.id = ?";
            $stmtVerifica = $conn->prepare($sqlVerificaCompra);
            $stmtVerifica->bind_param("i", $idCompra);
            $stmtVerifica->execute();
            $resultVerifica = $stmtVerifica->get_result();

            if ($resultVerifica->num_rows > 0) {
                $compra = $resultVerifica->fetch_assoc();
                $lugaresCompra = $compra['lugaresComprados'];
                $precoUnitario = $compra['preco'];
                $origem = $compra['Origem'];
                $destino = $compra['Destino'];
                $idBilhete = $compra['id_bilhete'];

                // Calcular total a reembolsar
                $totalReembolso = $lugaresCompra * $precoUnitario;

                // Creditar o saldo do utilizador
                $sqlSaldo = "UPDATE utilizadores SET Saldo = Saldo + ? WHERE id = ?";
                $stmtSaldo = $conn->prepare($sqlSaldo);
                $stmtSaldo->bind_param("di", $totalReembolso, $userId);

                if ($stmtSaldo->execute()) {
                    // Atualizar os lugares disponíveis no bilhete
                    $sqlUpdateBilhete = "UPDATE bilhetes SET lugaresComprados = lugaresComprados - ? WHERE id_bilhete = ? AND lugaresComprados >= ?";
                    $stmtUpdateBilhete = $conn->prepare($sqlUpdateBilhete);
                    $stmtUpdateBilhete->bind_param("iii", $lugaresCompra, $idBilhete, $lugaresCompra);

                    if ($stmtUpdateBilhete->execute()) {
                        // Eliminar a compra da tabela de compras
                        $sqlDeleteCompra = "DELETE FROM compras_bilhetes WHERE id = ?";
                        $stmtDeleteCompra = $conn->prepare($sqlDeleteCompra);
                        $stmtDeleteCompra->bind_param("i", $idCompra);

                        if ($stmtDeleteCompra->execute()) {
                            echo "<p class='success-msg'>Fez Reembolso de Origem: $origem, Destino: $destino.</p>";
                            criar_alerta("Reembolsou $lugaresCompra bilhete(s) de $origem para $destino.", "Reembolso", $userId);
                            header("Refresh: 2; url=consultar_bilhetes.php");
                        } else {
                            echo "<p class='error-msg'>Erro ao remover a compra do bilhete.</p>";
                        }
                    } else {
                        echo "<p class='error-msg'>Erro ao atualizar os lugares do bilhete.</p>";
                    }
                } else {
                    echo "<p class='error-msg'>Erro ao creditar o saldo do utilizador.</p>";
                }
            } else {
                echo "<p class='error-msg'>Compra não encontrada.</p>";
            }
        }


        ?>
    </div>

    <!-- Paginação -->
    <div class="paginacao">
        <?php if ($pagina > 1) : ?>
            <a href="?pagina=<?= $pagina - 1 ?>&origem=<?= $origemFiltro ?>&destino=<?= $destinoFiltro ?>&data=<?= $dataFiltro ?>">Anterior</a>
        <?php endif; ?>

        <span>Página <?= $pagina ?> de <?= $totalPaginas ?></span>

        <?php if ($pagina < $totalPaginas) : ?>
            <a href="?pagina=<?= $pagina + 1 ?>&origem=<?= $origemFiltro ?>&destino=<?= $destinoFiltro ?>&data=<?= $dataFiltro ?>">Próxima</a>
        <?php endif; ?>
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