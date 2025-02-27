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

        // Apenas vistitantes veem
        if ($cargoUser === "Visitante") {
            echo '<a href="rotas.php">Rotas</a>';
            echo '<a href="consultar_bilhetes.php">Bilhetes</a>';
            echo ' <a href="alertas.php">Alertas</a>';
        }

        // Apenas utilizadores autenticados veem "Perfil"
        if ($cargoUser !== "Visitante") {
            echo '<a href="rotas.php">Rotas</a>';
            echo '<a href="consultar_bilhetes.php">Bilhetes</a>';
            echo ' <a href="alertas.php">Alertas</a>';
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
            <div class="card-p">
                <h3>Contacto</h3>
                <p>988823763</p>
            </div>
            <div class="card-p">
                <h3>Email</h3>
                <p>FelixBus@gmail.com</p>
            </div>
            <div class="card-p">
                <h3>Horário de Serviço</h3>
                <p>Todos os dias: 08h30 às 19h00</p>
                <p>Sábados: 08h30 às 18h00</p>
                <p style="color: red">Domingo: Encerrado</p>
            </div>
            <div class="card-p">
                <h3>Escola Superior de Tecnologia</h3>
                <p>Av Empressario Campos da talagueira, Postal: 6000-767</p>
                <p>Castelo Branco, Portugal</p>
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