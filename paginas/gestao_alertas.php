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

// Função para criar alerta de promoção
function criar_alerta_promocao($idRota, $desconto)
{
    $mensagem = "Promoção na rota ID $idRota: Desconto de $desconto% em bilhetes.";
    return criar_alerta($mensagem, "Promoção");
}

// Função para buscar os alertas com filtro e paginação
function gestaoAlertas($filtro = '', $inicio, $limite)
{
    global $conn;

    // Prepara a consulta SQL
    $sql = "SELECT alertas.*, utilizadores.Nome, utilizadores.Id AS Id_User, alertas.Texto_Alerta, alertas.Data_Emissao
            FROM alertas 
            INNER JOIN utilizadores ON alertas.Id_Remetente = utilizadores.Id";

    // Adiciona o filtro (se houver)
    if (!empty($filtro)) {
        // Protege contra SQL Injection
        $filtro = mysqli_real_escape_string($conn, $filtro);
        $sql .= " WHERE alertas.Texto_Alerta LIKE '%$filtro%' 
                  OR utilizadores.Nome LIKE '%$filtro%' 
                  OR alertas.Tipo_Alerta LIKE '%$filtro%'";
    }

    // Limita o número de alertas por página (12 alertas por página)
    $sql .= " LIMIT $inicio, $limite";

    // Executa a consulta
    $result = mysqli_query($conn, $sql);

    // Verifica se a consulta foi bem-sucedida
    if (!$result) {
        die('Erro na consulta: ' . mysqli_error($conn)); // Se houver erro na consulta
    }

    return $result; // Retorna o resultado da consulta
}

// Excluir alerta de promoção
if (isset($_POST['excluirPromocao'])) {
    $idAlerta = $_POST['excluirPromocao']; // Obtém o ID do alerta de promoção

    // Query para excluir o alerta de promoção
    $sqlExcluir = "DELETE FROM alertas WHERE id = ?";
    $stmt = $conn->prepare($sqlExcluir);
    $stmt->bind_param("i", $idAlerta);

    if ($stmt->execute()) {
        // Redireciona a página para evitar o loop de submissão do formulário
        echo "<script>window.location.href = 'gestao_alertas.php';</script>";
        exit();
    } else {
        echo "<script>alert('Erro ao excluir alerta de promoção.');</script>";
    }
}

// Verifica se o botão de reset foi clicado
if (isset($_POST['reset']) && $_POST['reset'] == 'true') {
    $filtro = ''; // Limpa o filtro
} else {
    // Se não foi clicado o botão de reset, pega o filtro do POST
    $filtro = isset($_POST['filtro']) ? $_POST['filtro'] : '';
}

// Paginacao
$limite = 12; // Número de alertas por página
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página atual (se não for passada, começa da 1)
$inicio = ($pagina_atual - 1) * $limite; // Calcular o início da consulta

// Consultar total de alertas para calcular número de páginas
$sqlTotal = "SELECT COUNT(*) AS total FROM alertas";
$resultTotal = mysqli_query($conn, $sqlTotal);
$totalAlertas = mysqli_fetch_assoc($resultTotal)['total'];
$totalPaginas = ceil($totalAlertas / $limite); // Calcular número total de páginas

// Consultar os alertas com filtro e paginação
$alertas = gestaoAlertas($filtro, $inicio, $limite);
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Alertas - Promoções</title>
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

    <!-- Conteúdo Principal -->
    <div class="content">
        <h1>Gestão de Alertas - Promoções</h1>
        <h2>Criar Alerta de Promoção</h2>

        <form method="POST">
            <label for="idRota">ID da Rota:</label>
            <input class='texto-Adicionar' type="number" name="idRota" required>

            <label for="desconto">Desconto (%):</label>
            <input class='texto-Adicionar' type="number" name="desconto" required min="1" max="100">

            <button type="submit" name="criarPromocao" class="aceitar-btn">Criar Promoção</button>
        </form>

        <?php
        // Verifica se o botão de criação de alerta de promoção foi clicado
        if (isset($_POST['criarPromocao'])) {
            $idRota = $_POST['idRota'];
            $desconto = $_POST['desconto'];

            if ($desconto > 0 && $desconto <= 100) {
                if (criar_alerta_promocao($idRota, $desconto)) {
                    echo "<p>Alerta de promoção criado com sucesso para a Rota ID $idRota com desconto de $desconto%!</p>";
                } else {
                    echo "<p>Erro ao criar alerta de promoção.</p>";
                }
            } else {
                echo "<p>Desconto inválido. O valor deve estar entre 0 e 100%.</p>";
            }
        }
        ?>

        <h2>Filtrar Alertas</h2>
        <!-- Formulário de Filtro -->
        <form method="POST" action="">
            <input class="filtro" type="text" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>" placeholder="Filtrar por Nome, Tipo ou Texto">
            <button type="submit" class="filtrar-btn">Filtrar</button>
            <button type="submit" name="reset" value="true" class="limpar-btn">Limpar Filtro</button>
        </form>

        <div class="grid-container">
            <?php
            if (mysqli_num_rows($alertas) > 0) {
                while ($alerta = mysqli_fetch_assoc($alertas)) {
                    echo '
            <div class="card-Inicio">
                <h3>Nome: ' . $alerta['Nome'] . '</h3>
                <p>ID: ' . $alerta['Id_User'] . '</p>
                <p><strong>Tipo: </strong>' . $alerta['Tipo_Alerta'] . '</p>
                <p><strong>Data: </strong>' . date('d-m-Y H:i', strtotime($alerta['Data_Emissao'])) . '</p>
                <p>Texto do alerta: ' . $alerta['Texto_Alerta'] . '</p>
        ';

                    // Adicionar um botão de exclusão se o tipo de alerta for "Promoção"
                    if ($alerta['Tipo_Alerta'] == 'Promoção') {
                        echo '
                <form method="POST" style="display:inline;">
                    <button type="submit" name="excluirPromocao" value="' . $alerta['id'] . '" class="excluir-btn">Excluir Promoção</button>
                </form>
            ';
                    }

                    echo '</div>';
                }
            } else {
                echo "Nenhum alerta encontrado.";
            }
            ?>
        </div>

        <!-- Paginação -->
        <div class="paginacao">
            <?php if ($pagina_atual > 1): ?>
                <a href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
            <?php endif; ?>
            <span>Página <?php echo $pagina_atual; ?> de <?php echo $totalPaginas; ?></span>
            <?php if ($pagina_atual < $totalPaginas): ?>
                <a href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
