<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo']; // Obtém o cargo do utilizador
    $userId = $utilizador['id'];
} else {
    // Se não estiver autenticado, assume como visitante
    $cargoUser = "Visitante";
}

$acessosPermitidos = ['Cliente', 'Funcionario', 'Admin'];
if (!in_array($cargoUser, $acessosPermitidos)) {
    echo "<script>alert('Acesso negado! Tem de iniciar sessão para continuar.'); 
    window.location.href = 'login.php';</script>";
    exit();
}

// Inicializa variáveis de filtro para evitar warnings
$origemFiltro = $_GET['tipo'] ?? '';

// Consulta para buscar alertas com filtro
$sql = "SELECT * FROM alertas WHERE Tipo_Alerta LIKE '%Promoção%'";

// Aplicar filtros
if (!empty($origemFiltro)) {
    $sql .= " AND Tipo_Alerta LIKE '%" . mysqli_real_escape_string($conn, $origemFiltro) . "%'";
}


$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Contagem total de alertas
$sqlCountAlertas = "SELECT COUNT(*) as total FROM alertas";
$resultCountAlertas = $conn->query($sqlCountAlertas);
$totalRegistosAlertas = $resultCountAlertas->fetch_assoc()['total'];
$totalPaginasAlertas = ceil($totalRegistosAlertas / $limite);

// Aplicar limite e offset para a paginação
$sql .= " ORDER BY Data_Emissao DESC LIMIT $limite OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Saldo - FelixBus</title>
    <link rel="stylesheet" href="../paginas/menu.css">
</head>

<body>
    <div class="content">
        <h1>Consultar Alertas</h1>

        <!-- Filtros -->
        <form class="filtros-form" method="GET">
            <input type="text" class="filtro" name="tipo" placeholder="Filtro" value="<?= htmlspecialchars($origemFiltro) ?>">
            <button class="filtrar-btn" type="submit">Filtrar</button>
            <button class="limpar-btn" type="button" onclick="window.location.href='alertas.php'">Limpar Filtros</button>
        </form>
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table class='utilizadores-tabela'>";
            echo "<tr>
                <th>ID</th>
                <th>Texto alerta</th>
                <th>Data</th>
                <th>Tipo alerta</th>
            </tr>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['Texto_Alerta'] . "</td>";
                echo "<td>" . $row['Data_Emissao'] . "</td>";
                echo "<td>" . $row['Tipo_Alerta'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Não existem alertas disponíveis no momento.</p>";
        }
        ?>
    </div>
    <div class="navbar">
        <div class="logo">FelixBus</div>
        <div class="Hora" id="hora"></div>
    </div>

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

    <div class="paginacao">
        <?php if ($pagina > 1) : ?>
            <a href="?pagina=<?= $pagina - 1 ?>&origem=<?= urlencode($origemFiltro) ?>&destino=<?= urlencode($destinoFiltro) ?>&data=<?= urlencode($dataFiltro) ?>">Anterior</a>
        <?php endif; ?>

        <span>Página <?= $pagina ?> de <?= $totalPaginasAlertas ?></span>

        <?php if ($pagina < $totalPaginasAlertas) : ?>
            <a href="?pagina=<?= $pagina + 1 ?>&origem=<?= urlencode($origemFiltro) ?>&destino=<?= urlencode($destinoFiltro) ?>&data=<?= urlencode($dataFiltro) ?>">Próxima</a>
        <?php endif; ?>
    </div>
</body>

</html>