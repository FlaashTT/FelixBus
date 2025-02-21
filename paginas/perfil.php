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
            echo '<a href="rotas.php">Rotas</a>';
            echo '<a href="consultar_bilhetes.php">Bilhetes</a>';
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

    <div class="content">
        <div class="card">
            <!-- Informações do utilizador -->
            <?php
            $userId = $_SESSION['utilizador']['id'];
            // Ajuste do SQL para utilizar a tabela de utilizadores do sistema
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
            <div class="button-container" style="margin-top: 20px; text-align: center;">
                
                <a href="adicionar_Saldo.php">
                    <button class="btn">Adicionar Saldo</button>
                </a>

                
                <a href="levantar_Saldo.php">
                    <button class="btn">Levantar Dinheiro</button>
                </a>

                
                <a href="definicoes.php">
                    <button class="btn">Definições</button>
                </a>
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