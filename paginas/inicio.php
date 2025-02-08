<?php
include("../basedados/basedados.h");
include("../paginas/validacao.php");
session_start();

// Verifica se o utilizador está autenticado
if (isset($_SESSION['utilizador'])) {
    $utilizador = $_SESSION['utilizador'];
    $cargoUser = $utilizador['Cargo']; // Obtém o cargo do utilizador
} else {
    // Se não estiver autenticado, assume como visitante
    $cargoUser = "Visitante";
}
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
        <h1>Dashboard - Página Inicial</h1>
        <h2>Visão geral de Procura Bilhetes</h2>

        <div class="card">
            <form action="pesquisa.php" class="formulario" method="POST">
                <div class="campo">
                    <label for="de">De:</label>
                    <input type="text" id="de" class="input-text" name="de">
                </div>

                <div class="campo">
                    <label for="para">Para:</label>
                    <input type="text" id="para" class="input-text" name="para">
                </div>

                <div class="campo">
                    <label for="data">Data de Ida:</label>
                    <input type="date" id="data" class="input-text" name="data">
                </div>

                <div class="campo">
                    <button type="submit" class="btn-pesquisar">Pesquisar</button>
                </div>
            </form>
        </div>

        <div class="cards-container">
            <div class="card-Inicio">
                <h3>Saúde e Segurança</h3>
                <p>Mantém-te a ti e aos outros em segurança enquanto viajas connosco.</p>
            </div>

            <div class="card-Inicio">
                <h3>Conforto a bordo</h3>
                <p>Os nossos autocarros estão equipados com assentos grandes e confortáveis, WC, Wi-Fi e tomadas.</p>
            </div>

            <div class="card-Inicio">
                <h3>Grande rede de autocarros</h3>
                <p>Escolhe a partir de 3 000 destinos de viagem em 35 países e descobre a Europa com a FelixBus.</p>
            </div>

            <div class="card-Inicio">
                <h3>Viaja de forma ecológica</h3>
                <p>Os nossos autocarros provaram ter uma excelente pegada de carbono por passageiro conduzido/quilómetro.</p>
            </div>
        </div>
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