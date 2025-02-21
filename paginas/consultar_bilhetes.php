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
        <h1>Consulta de Bilhetes</h1>

        <?php
        // Consulta para buscar os bilhetes comprados pelo utilizador logado
        $sql = "SELECT b.*, r.*, cb.*
                FROM compras_bilhetes cb
                INNER JOIN bilhetes b ON cb.id_bilhete = b.id_bilhete
                INNER JOIN rota r ON b.id_rota = r.id_rota
                WHERE cb.id_utilizador = ?
                ORDER BY cb.data_compra DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<div>";
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
            echo "<p>Você ainda não comprou bilhetes.</p>";
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

            // Obter o número de lugares comprados e o valor gasto da tabela bilhetes
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

                // Devolver o valor do bilhete ao saldo do utilizador
                $sqlSaldo = "UPDATE utilizadores SET Saldo = Saldo + ? WHERE id = ?";
                $stmtSaldo = $conn->prepare($sqlSaldo);
                $stmtSaldo->bind_param("di", $totalReembolso, $userId);

                if ($stmtSaldo->execute()) {
                    // Liberar o lugar no bilhete
                    $sqlUpdateBilhete = "UPDATE bilhetes 
                     SET lugaresComprados = lugaresComprados - ? 
                     WHERE id_bilhete = ? AND lugaresComprados >= ?";
                    $stmtUpdateBilhete = $conn->prepare($sqlUpdateBilhete);
                    $stmtUpdateBilhete->bind_param("iii", $quantidadeLugares, $idBilhete, $quantidadeLugares);
                    $stmtUpdateBilhete->execute();


                    if ($stmtUpdateBilhete->execute()) {
                        // Remover a transação de compra da tabela de compras
                        $sqlDeleteCompra = "DELETE FROM compras_bilhetes WHERE id_bilhete = ? AND id_utilizador = ?";
                        $stmtDeleteCompra = $conn->prepare($sqlDeleteCompra);
                        $stmtDeleteCompra->bind_param("ii", $idBilhete, $userId);

                        if ($stmtDeleteCompra->execute()) {
                            // Confirmar sucesso
                            echo "Bilhete reembolsado com sucesso!";
                            header("Refresh: 2; url=consultar_bilhetes.php");
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

        $stmt->close();
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