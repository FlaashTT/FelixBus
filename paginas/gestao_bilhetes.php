<?php
session_start();
include("../basedados/basedados.h");
include("../paginas/validacao.php");

date_default_timezone_set('Europe/Lisbon');

// Apenas Funcionários e Administradores podem acessar esta página
validar_acesso(['Funcionario', 'Admin']);

$cargoUser = isset($_SESSION['utilizador']) ? $_SESSION['utilizador']['Cargo'] : "Visitante";

// 📌 Função para criar alerta no sistema
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

// 📌 Verificar se existe alerta de promoção ativo para uma rota
function verificar_promocao($idRota)
{
    global $conn;
    $sql = "SELECT * FROM alertas WHERE Tipo_Alerta = 'Promoção' AND Texto_Alerta LIKE '%$idRota%' AND NOW() BETWEEN Data_Emissao AND DATE_ADD(Data_Emissao, INTERVAL 24 HOUR)";
    $result = mysqli_query($conn, $sql);

    // Se existe alerta de promoção, aplica o desconto
    if ($result && mysqli_num_rows($result) > 0) {
        $alerta = mysqli_fetch_assoc($result);
        $desconto = $alerta['Texto_Alerta'];
        preg_match('/(\d+)%/', $desconto, $matches);
        if (isset($matches[1])) {
            return $matches[1] / 100;  // Retorna o desconto como uma fração
        }
    }
    return 0;  // Sem desconto
}

// 📌 Atualizar bilhetes expirados automaticamente
$dataAtual = date('Y-m-d');
$horaAtual = date('H:i:s');
$sql = "UPDATE bilhetes SET estado_bilhete = 'Expirado' WHERE estado_bilhete = 'Ativo' AND (data < '$dataAtual' OR (data = '$dataAtual' AND hora < '$horaAtual'))";
mysqli_query($conn, $sql);



/// 📌 Função para aplicar a promoção no preço
function aplicar_promocao($preco, $idRota)
{
    // Verifica se existe promoção ativa para a rota
    $desconto = verificar_promocao($idRota);

    if ($desconto > 0) {
        return $preco * (1 - $desconto);  // Aplica o desconto encontrado
    }
    return $preco;  // Sem desconto
}


// 📌 Obter bilhetes conforme o estado selecionado
function obterBilhetesPorEstado($estado)
{
    global $conn;
    $sql = "SELECT b.*, v.nome_veiculo, r.origem, r.destino, b.preco
            FROM bilhetes b
            INNER JOIN veiculos v ON b.id_veiculo = v.Id_Veiculo
            INNER JOIN rota r ON b.id_rota = r.Id_Rota
            WHERE b.estado_bilhete = '$estado'
            ORDER BY b.data DESC, b.hora DESC";
    return mysqli_query($conn, $sql);
}

// 📌 Determinar qual estado exibir (Padrão: Ativo)
$estadoSelecionado = isset($_POST['estado']) ? $_POST['estado'] : 'Ativo';
$bilhetes = obterBilhetesPorEstado($estadoSelecionado);

// 📌 Editar bilhete ativo
if (isset($_POST['editarBilheteConfirmado'])) {
    $idBilhete = $_POST['idBilhete'];
    $novaData = $_POST['novaData'];
    $novaHora = $_POST['novaHora'];

    $sql = "UPDATE bilhetes SET data='$novaData', hora='$novaHora' WHERE id_bilhete='$idBilhete' AND estado_bilhete = 'Ativo'";

    if (mysqli_query($conn, $sql)) {
        criar_alerta("Bilhete ID $idBilhete atualizado", "Editar Bilhete");
        echo "<p>Bilhete atualizado com sucesso!</p>";
        header("Refresh: 2; url=gestao_bilhetes.php");
    } else {
        echo "<p>Erro ao editar bilhete: " . mysqli_error($conn) . "</p>";
    }
}

// 📌 Eliminar bilhete (vai para "Cancelado")
if (isset($_POST['eliminarBilhete'])) {
    $idBilhete = $_POST['eliminarBilhete'];

    $sql = "UPDATE bilhetes SET estado_bilhete = 'Cancelado' WHERE id_bilhete = '$idBilhete' AND estado_bilhete = 'Ativo'";
    if (mysqli_query($conn, $sql)) {
        criar_alerta("Bilhete $idBilhete cancelado", "Cancelar Bilhete");
        $mensagem = "<p class='mensagem sucesso'>Bilhete ID $idBilhete foi cancelado com sucesso.</p>";
    } else {
        $mensagem = "<p class='mensagem erro'>Erro ao cancelar bilhete: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Bilhetes</title>
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
        <h1>Gestão de Bilhetes</h1>

        <form method="POST">
        <button class='adicionar-btn' type="submit" name="adicionarBilhete">Adicionar Bilhete</button>
            <button class='estado-btn' type="submit" name="estado" value="Ativo">Bilhetes Ativos</button>
            <button class='estado-btn' type="submit" name="estado" value="Expirado">Bilhetes Expirados</button>
            <button class='estado-btn' type="submit" name="estado" value="Cancelado">Bilhetes Cancelados</button>
        </form>

        <?php
        if (isset($_POST['adicionarBilhete'])) {
            echo "
                <form method='POST'>
                    <div class='card'>
                        <h2>Adicionar Bilhete</h2>
                        <label>Rota (ID):</label>
                        <input class='texto-Adicionar' type='text' name='idRota' required>

                        <label>Data:</label>
                        <input class='texto-Adicionar' type='date' name='dataBilhete' required>

                        <label>Hora:</label>
                        <input class='texto-Adicionar' type='time' name='horaBilhete' required>

                        <label>Veículo (ID):</label>
                        <input class='texto-Adicionar' type='text' name='idVeiculo' required style='width: 100px;'>


                        <button class='aceitar-btn' type='submit' name='confirmarBilhete'>Confirmar</button>
                        <button class='recusar-btn' type='button' onclick='window.location.href=\"gestao_bilhetes.php\"'>Cancelar</button>
                    </div>
                </form>";
        }

        echo "<div class='grid-container'>";
        while ($bilhete = mysqli_fetch_assoc($bilhetes)) {
            echo "<div class='grid-container'>
                    <div class='grid-container-lado'>
                        <h3>ID: " . $bilhete['id_bilhete'] . "</h3>
                        <p><b>Origem:</b> " . $bilhete['origem'] . "</p>
                        <p><b>Destino:</b> " . $bilhete['destino'] . "</p>
                        <p><b>Data:</b> " . $bilhete['data'] . "</p>
                        <p><b>Hora:</b> " . $bilhete['hora'] . "</p>
                        <p><b>Preço:</b> " . $bilhete['preco'] . " €</p>
                        <p><b>Estado:</b> " . $bilhete['estado_bilhete'] . "</p>";
        
            // **Apenas bilhetes ativos podem ser editados ou eliminados**
            if ($bilhete['estado_bilhete'] === "Ativo") {
                echo "<form method='POST'>
                        <button class='editar-btn' type='submit' name='editarBilhete' value='" . $bilhete['id_bilhete'] . "'>Editar</button>
                        <button class='eliminar-btn' type='submit' name='eliminarBilhete' value='" . $bilhete['id_bilhete'] . "'>Eliminar</button>
                      </form>";
            }
        
            echo "  </div> <!-- Fecha grid-container-lado -->
                 </div>"; // Fecha grid-container
        }
        

        if (isset($_POST['confirmarBilhete'])) {
            $idRota = $_POST['idRota'];
            $dataBilhete = $_POST['dataBilhete'];
            $horaBilhete = $_POST['horaBilhete'];
            $idVeiculo = $_POST['idVeiculo'];
        
            // Verifica se a rota existe
            $sql = "SELECT distancia FROM rota WHERE Id_Rota = '$idRota'";
            $result = mysqli_query($conn, $sql);
        
            if (!$result || mysqli_num_rows($result) == 0) {
                echo "<p>Erro: A rota não existe!</p>";
            } else {
                $row = mysqli_fetch_assoc($result);
                $distancia = $row['distancia'];
                $preco = $distancia * 1.00; // Preço por Km
        
                // Aplica o desconto caso haja promoção ativa
                $precoComDesconto = aplicar_promocao($preco, $idRota);
        
                // Agora o valor do preço com desconto é usado na inserção do bilhete
                $sql = "INSERT INTO bilhetes (id_rota, data, hora, id_veiculo, preco, estado_bilhete) 
                        VALUES ('$idRota', '$dataBilhete', '$horaBilhete', '$idVeiculo', '$precoComDesconto', 'Ativo')";
        
                if (mysqli_query($conn, $sql)) {
                    criar_alerta("Bilhete criado para a rota $idRota", "Criar Bilhete");
                    echo "<p>Bilhete adicionado com sucesso com o preço de $precoComDesconto €!</p>";
                    header("Refresh: 2; url=gestao_bilhetes.php");
                } else {
                    echo "<p>Erro ao adicionar bilhete: " . mysqli_error($conn) . "</p>";
                }
            }
        }
        

        // 📌 Formulário de edição de bilhete
        if (isset($_POST['editarBilhete'])) {
            $idBilhete = $_POST['editarBilhete'];
            echo "
                <form method='POST'>
                    <div class='card'>
                        <h2>Editar Bilhete ID: $idBilhete</h2>
                        <label>Nova Data:</label>
                        <input class='texto-Adicionar' type='date' name='novaData' required>

                        <label>Nova Hora:</label>
                        <input class='texto-Adicionar' type='time' name='novaHora' required>

                        <input type='hidden' name='idBilhete' value='$idBilhete'>
                        <button class='aceitar-btn' type='submit' name='editarBilheteConfirmado'>Guardar Alterações</button>
                        <button class='recusar-btn' type='button' onclick='window.location.href=\"gestao_bilhetes.php\"'>Cancelar</button>
                    </div>
                </form>";
        }
        ?>
    </div>
</body>

</html>