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


// Contar total de utilizadores para paginação
function totalUtilizadores()
{
    global $conn;
    $sql = "SELECT COUNT(*) AS total FROM utilizadores";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// Obter utilizadores para exibição (apenas paginação, sem filtro)
function obterUtilizadoresPaginados($pagina = 1, $limite = 10)
{
    global $conn;
    $offset = ($pagina - 1) * $limite;
    $sql = "SELECT * FROM utilizadores ORDER BY Data_Registro DESC LIMIT $limite OFFSET $offset";
    return mysqli_query($conn, $sql);
}

// Aplicar filtro apenas quando há pesquisa
function obterUtilizadoresComFiltro($filtro)
{
    global $conn;
    $sql = "SELECT * FROM utilizadores WHERE Nome LIKE '%$filtro%' OR Email LIKE '%$filtro%' OR Cargo LIKE '%$filtro%' ORDER BY Data_Registro DESC";
    return mysqli_query($conn, $sql);
}

// PROCESSAR ELIMINAÇÃO
if (isset($_POST['confirmarEliminarUtilizador'])) {
    $idUtilizador = $_POST['confirmarEliminarUtilizador'];

    // Atualizar o cargo para "Eliminado" 
    $stmt = $conn->prepare("UPDATE utilizadores SET autenticacao = 'Eliminado' WHERE Id = ?");
    $stmt->bind_param("i", $idUtilizador);

    if ($stmt->execute()) {
        criar_alerta("O utilizador com ID: $idUtilizador foi marcado como 'Eliminado'", "Eliminar Utilizador");
        
        header("Location: gestao_utilizadores.php"); 
        exit();  
    } else {
        echo "<p>Erro ao marcar o utilizador como 'Eliminado'.</p>";
    }
    
}

// Verifica se o botão "Limpar Filtro" foi pressionado
if (isset($_POST['reset_filtro'])) {
    $filtro = ''; // Limpa o filtro
    header("Location: gestao_utilizadores.php"); 
    exit();
}

// Recuperar filtro se existir
$filtro = isset($_POST['filtro']) ? $_POST['filtro'] : '';

// Paginação (independente do filtro)
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$limite = 10;
$totalUtilizadores = totalUtilizadores();
$totalPaginas = ceil($totalUtilizadores / $limite);

// Definir se estamos a mostrar filtrados ou paginados
if (!empty($filtro)) {
    $utilizadores = obterUtilizadoresComFiltro($filtro);
} else {
    $utilizadores = obterUtilizadoresPaginados($pagina, $limite);
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestão de Utilizadores</title>
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
        <h1>Gestão de Utilizadores</h1>
        <h2>Visão geral dos utilizadores</h2>

        <!-- Formulário de Filtro -->
        <form method="POST" action="">
            <input class="filtro" type="text" name="filtro" value="<?php echo htmlspecialchars($filtro); ?>" placeholder="Filtrar por Nome, Email ou Cargo">
            <button type="submit" class="filtrar-btn">Filtrar</button>
            <button type="submit" name="reset_filtro" class="limpar-btn">Limpar Filtro</button>
        </form>

        <table class="utilizadores-tabela">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Autenticação</th>
                    <th>Estado</th>
                    <th>Cargo</th>
                    <th>Saldo</th>
                    <th>Data de Registo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($utilizadores) > 0) {
                    while ($user = mysqli_fetch_assoc($utilizadores)) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . $user['Nome'] . "</td>";
                        echo "<td>" . $user['Email'] . "</td>";
                        echo "<td>" . $user['Autenticacao'] . "</td>";
                        echo "<td>" . $user['Estado'] . "</td>";
                        echo "<td>" . $user['Cargo'] . "</td>";
                        echo "<td>" . $user['Saldo'] . "€</td>";
                        echo "<td>" . date('d-m-Y H:i', strtotime($user['data_registro'])) . "</td>";
                        echo "<td>";

            // Se o utilizador está eliminado, não pode ser editado nem removido
            if ($user['Autenticacao'] === 'Eliminado') {
                echo "<span class='status-inativo'>Conta Eliminada</span>";
            } 
            // Se for administrador, não pode ser eliminado
            elseif ($user['Cargo'] === 'Admin') {
                echo "<span class='status-admin'>Administrador</span>";
            } 
            // Caso contrário, permite editar/remover
            else {
                echo "<form method='POST' style='display:inline;'>
                    <button class='aceitar-btn' type='submit' name='editarUtilizador' value='" . $user['id'] . "'>Editar</button>
                    <button class='recusar-btn' type='submit' name='eliminarUtilizador' value='" . $user['id'] . "'>Remover</button>
                  </form>";
            }

                        echo "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>Nenhum utilizador encontrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Paginação -->
        <div class="paginacao">
            <?php if ($pagina > 1) : ?>
                <a href="?pagina=1" class="pagina-btn">Primeira</a>
                <a href="?pagina=<?php echo $pagina - 1; ?>" class="pagina-btn">Anterior</a>
            <?php endif; ?>

            <span>Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>

            <?php if ($pagina < $totalPaginas) : ?>
                <a href="?pagina=<?php echo $pagina + 1; ?>" class="pagina-btn">Próxima</a>
                <a href="?pagina=<?php echo $totalPaginas; ?>" class="pagina-btn">Última</a>
            <?php endif; ?>
        </div>
    

    <?php
    // FORMULÁRIO DE EDIÇÃO DE UTILIZADOR
    if (isset($_POST['editarUtilizador'])) {
        $idUtilizador = $_POST['editarUtilizador'];
        $utilizador = $conn->query("SELECT * FROM utilizadores WHERE Id = $idUtilizador")->fetch_assoc();

        echo "
    <form method='POST'>
        <div class='card'>
            <h2>Editar Utilizador</h2>
            <input class='texto-Adicionar' type='hidden' name='idUtilizador' value='" . $utilizador['id'] . "'>
            
            <label>Nome:</label>
            <input class='texto-Adicionar' type='text' name='novoNome' value='" . $utilizador['Nome'] . "' required>
            
            <label>Email:</label>
            <input class='texto-Adicionar' type='email' name='novoEmail' value='" . $utilizador['Email'] . "' required>
            
            <label>Saldo (€):</label>
            <input class='texto-Adicionar' type='number' step='0.01' name='novoSaldo' value='" . $utilizador['Saldo'] . "' required>

            <button class='aceitar-btn' type='submit' name='ConfirmarEditarUtilizador'>Guardar</button>
            <button class='recusar-btn' type='button' onclick='window.location.href=\"gestao_utilizadores.php\"'>Cancelar</button>
        </div>
    </form>";
    }

    // ATUALIZAR UTILIZADOR
    if (isset($_POST['ConfirmarEditarUtilizador'])) {
        $idUtilizador = $_POST['idUtilizador'];
        $novoNome = $_POST['novoNome'];
        $novoEmail = $_POST['novoEmail'];
        $novoSaldo = $_POST['novoSaldo'];

        $conn->query("UPDATE utilizadores SET Nome='$novoNome', Email='$novoEmail', Saldo='$novoSaldo' WHERE Id='$idUtilizador'");
        criar_alerta("Editou o utilizador ID $idUtilizador", "Editar Utilizador");
        header("Refresh: 2; url=gestao_utilizadores.php");
    }

    // ELIMINAR UTILIZADOR
    if (isset($_POST['eliminarUtilizador'])) {
        $idUtilizador = $_POST['eliminarUtilizador'];
    
        echo '
        <form method="POST">
            <div class="card">
                <h2>Confirmar Eliminação</h2>
                <p>Tem a certeza que deseja eliminar o utilizador com ID: ' . $idUtilizador . '?</p>
                <input type="hidden" name="confirmarEliminarUtilizador" value="' . $idUtilizador . '">
                <button class="recusar-btn" type="submit">Confirmar</button>
                <button class="aceitar-btn" type="button" onclick="window.location.href=\'gestao_utilizadores.php\'">Cancelar</button>
            </div>
        </form>';
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