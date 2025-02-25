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

// Obtém o filtro de tipo (se fornecido) e sanitiza
$origemFiltro = isset($_GET['tipo']) ? mysqli_real_escape_string($conn, $_GET['tipo']) : '';

// Se um filtro foi fornecido, aplica-o na query, mas mantendo a regra: 
    if (!empty($origemFiltro)) {
        $sql = "SELECT * FROM alertas 
                WHERE Tipo_Alerta LIKE '%$origemFiltro%' 
                AND ((Tipo_Alerta = 'Promoção') OR ((Tipo_Alerta = 'Compra' OR Tipo_Alerta = 'Reembolso') AND Id_Remetente = '$userId'))";
    } else {
        $sql = "SELECT * FROM alertas 
                WHERE (Tipo_Alerta = 'Promoção') OR ((Tipo_Alerta = 'Compra' OR Tipo_Alerta = 'Reembolso') AND Id_Remetente = '$userId')";
    }
    

// Paginação
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Conta o total de registros que satisfazem a condição
// Para contar corretamente, encapsulamos a query em uma subconsulta:
$sqlCount = "SELECT COUNT(*) as total FROM ($sql) AS subquery";
$resultCount = $conn->query($sqlCount);
$totalRegistosAlertas = $resultCount->fetch_assoc()['total'];
$totalPaginasAlertas = ceil($totalRegistosAlertas / $limite);

// Adiciona ordenação e limites para a paginação
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

    <script>
        // Exibir a hora dinâmica na navbar
        function updateTime() {
            const date = new Date();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            document.getElementById('hora').textContent = hours + ":" + minutes + ":" + seconds;
        }
        setInterval(updateTime, 1000);
        updateTime(); // Inicializa a hora ao carregar a página
    </script>
</body>

</html>