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

// Apenas Funcionários, Administradores e Clientes podem acessar esta página
validar_acesso(['Funcionario', 'Admin', 'Cliente']);
?>

<!DOCTYPE html>
<html lang="pt-pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FelixBus</title>
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
        // Apenas utilizadores autenticados veem "Perfil"
        if ($cargoUser !== "Visitante") {
            echo '<a href="perfil.php">Perfil</a>';
        }

        // Funcionários e Administradores têm mais opções
        if ($cargoUser === 'Funcionario' || $cargoUser === 'Admin') {
            echo '<a href="gestao_veiculos.php">Gestão Veículos</a>';
            echo '<a href="gestao_rotas.php">Gestão de Rotas</a>';
            echo '<a href="gestao_utilizadores.php">Gestão de Utilizadores</a>';
            echo '<a href="gestao_bilhetes.php">Gestão de Bilhetes</a>';
        }

        // Apenas Administradores veem estas opções adicionais
        if ($cargoUser === 'Admin') {
            echo '<a href="gestao_pedidos.php">Gestão de Pedidos</a>';
            echo '<a href="gestao_alertas.php">Gestão de Alertas</a>';
        }

        // "Sair" apenas para utilizadores autenticados, "Iniciar Sessão" para visitantes
        if ($cargoUser !== "Visitante") {
            echo '<a href="logout.php" class="logout">Sair</a>';
        } else {
            echo '<a href="login.php" class="login-btn">Iniciar Sessão</a>';
        }
        ?>

    </div>

    <!-- Conteúdo Principal -->
    <div class="content">
        <div class="card">
            <!-- Informações do utilizador -->
            <?php
            $userId = $_SESSION['utilizador']['id'];
            // Ajustando o SQL para utilizar a tabela de utilizadores do seu sistema
            $sql = "SELECT * FROM utilizadores WHERE id = '$userId' AND estado = 'Online'";

            if ($result = mysqli_query($conn, $sql)) {
                if (mysqli_num_rows($result) > 0) {
                    $user = mysqli_fetch_assoc($result);
                    echo "
                        <h1>Olá, " . $user['Nome'] . "</h1>
                        <form method='POST'>
                            <p>Saldo: " . $user['Saldo'] . "€  
                        </form>";
                }
            }
            ?>
            <!-- Botões abaixo do saldo -->
            <div class="button-container" style="margin-top: 20px; text-align: center;">
                <!-- Botão para adicionar saldo -->
                <a href="adicionar_Saldo.php">
                    <button class="btn">Adicionar Saldo</button>
                </a>

                <!-- Botão para levantar dinheiro -->
                <a href="levantar_Saldo.php">
                    <button class="btn">Levantar Dinheiro</button>
                </a>

                <!-- Botão para editar perfil (Definições) -->
                <a href="definicoes.php">
                    <button class="btn">Definições</button>
                </a>
            </div>
        </div>

        <div class="grid-container">

            <?php
            // Ajustando o SQL para refletir a estrutura do seu banco de dados
            $sql = "SELECT b.*, r.*
                    FROM compras_bilhetes cb
                    INNER JOIN bilhetes b ON cb.id_bilhete = b.id_bilhete
                    INNER JOIN rota r ON b.id_rota = r.id_rota
                    WHERE cb.id_utilizador = $userId
                    ORDER BY cb.data_compra DESC";

            if ($result = mysqli_query($conn, $sql)) {
                if (mysqli_num_rows($result) > 0) {
                    echo "<div>";
                    echo "<h1 style='text-align: center;' >Seus Bilhetes</h1>";
                    while ($row = mysqli_fetch_assoc($result)) {

                        echo "<div class='card-Inicio'>";
                        echo "<p><strong>Bilhete ID:</strong> " . $row['id_bilhete'] . "</p>";
                        echo "<p><strong>Data:</strong> " . $row['data'] . "</p>";
                        echo "<p><strong>De:</strong> " . $row['Origem'] . "</p>";
                        echo "<p><strong>Para:</strong> " . $row['Destino'] . "</p>";
                        echo "<p><strong>Preço:</strong> " . $row['preco'] . "€</p>";
                        echo "<p><strong>Lugares:</strong> " . $row['lugaresComprados'] . "</p>";
                        echo "<p><strong>Total da Compra:</strong> " . number_format($row['lugaresComprados'] * $row['preco'], 2, ',', '.') . "€</p>";


                        if ($row['estado_bilhete'] === "Expirado" || $row['estado_bilhete'] === "Cancelado") {
                            echo "<form method='POST'>
                                <button class='eliminar-btn' type='submit' name='eliminarBilhete' value='" . $row['id_bilhete'] . "'>Eliminar Bilhete</button>
                            </form>";
                        } else {
                            echo "<form method='POST'>
                                <input type='hidden' name='valorAReceber' value='" . $row['preco'] . "'>
                                <button class='reembolsar-btn' type='submit' name='reembolsarBilhete' value='" . $row['id_bilhete'] . "'>Reembolsar Bilhete</button>
                            </form>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "Você não possui bilhetes comprados.";
                }
            }

            

            // Eliminar bilhete
            if (isset($_POST['eliminarBilhete'])) {
                $idBilhete = $_POST['eliminarBilhete'];
                $sql = "DELETE FROM bilhetes WHERE id_bilhete = '$idBilhete'";

                if (mysqli_query($conn, $sql)) {
                    echo "Bilhete removido do perfil com sucesso!";
                    header("Refresh: 2; url=perfil.php");
                } else {
                    echo "Erro ao remover bilhete.";
                }
            }

            // Reembolsar bilhete
            if (isset($_POST['reembolsarBilhete']) && isset($_POST['valorAReceber'])) {
                $idBilhete = $_POST['reembolsarBilhete'];
                $valor = $_POST['valorAReceber'];  // Este valor vem do preço do bilhete

                // Passo 1: Obter o número de lugares comprados e o valor gasto da tabela bilhetes
                $sqlCompra = "SELECT lugaresComprados, preco 
                              FROM bilhetes 
                              WHERE id_bilhete = ?";

                $stmtCompra = $conn->prepare($sqlCompra);
                $stmtCompra->bind_param("i", $idBilhete);
                $stmtCompra->execute();
                $resultCompra = $stmtCompra->get_result();

                if ($resultCompra->num_rows > 0) {
                    $compra = $resultCompra->fetch_assoc();
                    $quantidadeLugares = $compra['lugaresComprados'];
                    $precoUnitario = $compra['preco'];

                    // Calcular o valor total do reembolso
                    $totalReembolso = $precoUnitario * $quantidadeLugares;

                    // Passo 2: Devolver o valor do bilhete ao saldo do utilizador
                    $sqlSaldo = "UPDATE utilizadores SET Saldo = Saldo + ? WHERE id = ?";
                    $stmtSaldo = $conn->prepare($sqlSaldo);
                    $stmtSaldo->bind_param("di", $totalReembolso, $userId);

                    if ($stmtSaldo->execute()) {
                        // Passo 3: Liberar o lugar no bilhete
                        $sqlUpdateBilhete = "UPDATE bilhetes SET lugaresComprados = lugaresComprados - ? WHERE id_bilhete = ?";
                        $stmtUpdateBilhete = $conn->prepare($sqlUpdateBilhete);
                        $stmtUpdateBilhete->bind_param("ii", $quantidadeLugares, $idBilhete);

                        if ($stmtUpdateBilhete->execute()) {
                            // Passo 4: Remover a transação de compra da tabela de compras
                            $sqlDeleteCompra = "DELETE FROM compras_bilhetes WHERE id_bilhete = ? AND id_utilizador = ?";
                            $stmtDeleteCompra = $conn->prepare($sqlDeleteCompra);
                            $stmtDeleteCompra->bind_param("ii", $idBilhete, $userId);

                            if ($stmtDeleteCompra->execute()) {
                                // Passo 5: Confirmar sucesso
                                echo "Bilhete reembolsado com sucesso!";
                                header("Refresh: 2; url=perfil.php");
                            } else {
                                echo "Erro ao remover a compra do bilhete.";
                            }
                        } else {
                            echo "Erro ao liberar o lugar no bilhete.";
                        }
                    } else {
                        echo "Erro ao atualizar o saldo.";
                    }
                } else {
                    echo "Erro ao obter detalhes da compra.";
                }
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
        updateTime(); // Inicializa a hora ao carregar a página
    </script>
</body>

</html>