<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página com Navbar e Sidebar Dinâmica</title>
    <style>
        /* Reset básico */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Navbar */
        .navbar {
            background-color: #333;
            overflow: hidden;
            display: flex;
            align-items: center;
            padding: 0 20px;
            height: 50px;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            display: inline-block;
        }

        .navbar a:hover {
            background-color: #575757;
            border-radius: 4px;
        }

        .navbar .Hora {
            color: white;
            font-size: 18px;
            font-weight: bold;
            margin-left: 1650px;
        }

        /* Sidebar */
        .sidebar {
            height: calc(100vh - 50px);
            width: 200px;
            position: fixed;
            top: 50px;
            left: 0;
            background-color: #f4f4f4;
            overflow-x: hidden;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #333;
            display: block;
        }

        .sidebar a:hover {
            background-color: #ddd;
        }

        .logout {
            padding: 10px 15px;
            background-color: #e74c3c;
            color: white;
            text-align: center;
            border: none;
            cursor: pointer;
            width: 90%;
            margin: 10px auto;
            display: block;
            text-decoration: none;
        }

        .logout:hover {
            background-color: #c0392b;
        }

        /* Conteúdo principal */
        .content {
            margin-left: 200px;
            margin-top: 50px;
            padding: 20px;
            z-index: 1;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="#contato">Contato</a>
        <a href="#ajuda">Suporte</a>
        <p class="Hora"><?php echo date('Y-m-d H:i'); ?></p>
    </div>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="?page=dashboard">Paginal Inicial</a>
        <a href="?page=configuracoes">Configurações</a>
        <a href="?page=perfil">Perfil</a>

        <!-- Links adicionais basedados no tipo de usuário -->
        <?php
        include("../basedados/basedados.h");
        session_start();

        if (!isset($_SESSION['user'])) {
            header("Location: login.php");
            exit;
        }

        $user = $_SESSION['user'];
        $tipoUser = $user['TipoUser'];

        if ($tipoUser == '3') { // Admin
            echo '<a href="?page=gerenciar-utilizadores">Gerenciar Utilizadores</a>';
            echo '<a href="?page=Pedidos">Pedidos</a>';
        } elseif ($tipoUser == '2') { // Funcionário
            echo '<a href="?page=tarefas">Minhas Tarefas</a>';
            echo '<a href="?page=horarios">Horários</a>';
        } elseif ($tipoUser == '1') { // Cliente
            echo '<a href="?page=meus-pedidos">Meus Pedidos</a>';
            echo '<a href="?page=suporte">Suporte</a>';
        }
        ?>

        <!-- Botão de logout -->
        <a class="logout" href="logout.php">Sair</a>
    </div>

    <!-- Conteúdo Principal -->
    <div class="content">
        <?php
        // Verifica se o usuário está logado


        // Puxa os dados do usuário
        // Obtém o tipo do usuário (1: Cliente, 2: Funcionário, 3: Admin)

        // Verifica qual página exibir com base no parâmetro 'page' na URL
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';

        switch ($page) {
            case 'dashboard':
                echo "<h1>Bem-vindo, " . ($user['Nome']) . "!</h1>";
                echo "<h2>Pagina Inicial</h2><p>Bem-vindo ao seu painel de controle!</p>";
                break;

            case 'configuracoes':
                echo "<h2>Configurações</h2><p>Configurações da conta e preferências.</p>";
                break;

            case 'perfil':
                echo "<h2>Perfil</h2><p>Detalhes do seu perfil de usuário.</p>";
                break;

            case 'gerenciar-utilizadores':
                echo "
                <style>
                    table {
                        width: 30%;
                        border-collapse: collapse;
                        margin-top: 20px;
                    }

                    th,
                    td {
                        padding: 10px;
                        text-align: left;
                        border: 1px solid #ddd;
                    }

                    th {
                        background-color: #f4f4f4;
                    }

                    td {
                        background-color: #fafafa;
                    }

                    button {
                        padding: 8px 15px;
                        margin-top: 5px;
                        cursor: pointer;
                        background-color: #007BFF;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        font-size: 14px;
                    }

                    button:hover {
                        background-color: #0056b3;
                    }

                    form {
                        display: flex;
                        flex-direction: column;
                    }
                </style>
                ";
                if ($tipoUser == '3') {
                    echo "
                        <label for='roles'>Filtro</label>
                        <select id='roles' name='role'>
                            <option value='todos'>Todos</option>
                            <option value='admin'>Admin</option>
                            <option value='funcionario'>Funcionário</option>
                            <option value='cliente'>Cliente</option>
                            
                        </select>
                    ";
                    echo ("<h1>Lista Administradores</h1>");
                    echo "<hr>";
                    
                    $sql = "SELECT * FROM users ";
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        echo "<table>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Tipo Utilizador</th>
                                    <th>Validação</th>
                                    <th>Saldo</th>
                                    <th>Editar</th>
                                    <th>Adicionar saldo</th>
                                    <th>Alterar tipo Utilizador</th>
                                    <th>Eliminar</th>
                                    
                                </tr>";
                        while ($mostrar = mysqli_fetch_assoc($result)) {
                            if($mostrar['TipoUser'] === "3"){
                                $tipo = "Administrador";
                            }else if($mostrar['TipoUser'] === "2"){
                                $tipo = "Funcionario";
                            }else if($mostrar['TipoUser'] === "1"){
                                $tipo = "Cliente";
                            } else {echo "OOPS!!...";}
                            
                            echo "
                                <form method='POST' action='../paginas/acoes.php'>
                                    <tr>
                                        <td>" . $mostrar['Nome'] . "</td>
                                        <td>" . $mostrar['Email'] . "</td>
                                        <td>" . $tipo . "</td>
                                        <td>" . $mostrar['Autenticacao'] . "</td>
                                        <td>" . $mostrar['Saldo'] . "</td>
                                        <td><button type='submit' name='alterarConta' value='" . $mostrar['Email'] . "'>Alterar</button></td>
                                        <td>
                                            <button type='submit' name='adicionarSaldo' value='" . $mostrar['Email'] . "'>Adicionar</button>
                                        </td>
                                        <td>
                                            <button type='submit' name='alterarTipo' value='" . $mostrar['Email'] . "'>Alterar Tipo</button>
                                        </td>
                                        <td>
                                            <button type='submit' name='EliminarConta' value='" . $mostrar['Email'] . "'>Eliminar</button>
                                        </td>
                                    </tr>
                                </form>";

                        }
                        echo "</table>";
                    } else {
                        echo "Sem Utilizadores !";
                    }
                } else {
                    echo "<p>Você não tem permissão para acessar esta página.</p>";
                }
                break;

            case 'Pedidos':
                if ($tipoUser == '3') {
                    echo "<h2>Pedidos de Aceitação</h2>";
                    $sql = "SELECT * FROM users WHERE Autenticacao = 'Pendente'";
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($userToverf = mysqli_fetch_assoc($result)) {
                            echo "<form action='' method='POST'>";
                            echo "<p>Nome: " . $userToverf['Nome'] . "</p>";
                            echo "<p>Email: " . $userToverf['Email'] . "</p>";
                            echo '<button type="submit" name="AceitarPedido" value="' . $userToverf['Email'] . '">Aceitar Utilizador</button>';
                            echo '<button type="submit" name="RejeitarPedido" value="' . $userToverf['Email'] . '">Rejeitar Utilizador</button>';
                            echo "<hr>";
                            echo "</form>";
                        }
                    } else {
                        echo "Sem nenhum utilizador para verificar";
                    }
                } else {
                    echo "<p>Você não tem permissão para acessar esta página.</p>";
                }
                break;

            case 'tarefas':
                if ($tipoUser == '2') {
                    echo "<h2>Minhas Tarefas</h2><p>Veja suas tarefas pendentes.</p>";
                } else {
                    echo "<p>Você não tem permissão para acessar esta página.</p>";
                }
                break;

            case 'horarios':
                if ($tipoUser == '2') {
                    echo "<h2>Horários</h2><p>Confira seu horário de trabalho.</p>";
                } else {
                    echo "<p>Você não tem permissão para acessar esta página.</p>";
                }
                break;

            default:
                echo "<h2>Início</h2><p>Bem-vindo ao sistema! Selecione uma opção no menu.</p>";
        }

        // Verifica se o botão foi clicado para aceitar o pedido
        if (isset($_POST['AceitarPedido'])) {
            $user_email = $_POST['AceitarPedido'];

            // Atualiza o campo 'Autenticacao' para 'Aceite' na base de dados
            $sql = "UPDATE users SET Autenticacao = 'Aceite' WHERE Email = '$user_email'";

            if (mysqli_query($conn, $sql)) {
                echo "<p>Utilizador aceito com sucesso! </p>";
                echo "<meta http-equiv='refresh' content='1;url=?page=Pedidos'>";
            } else {
                echo "Erro ao aceitar o usuário: " . mysqli_error($conn);
            }
        }

        if (isset($_POST['RejeitarPedido'])) {
            $user_email = $_POST['RejeitarPedido'];
            
            // Atualiza o campo 'Autenticacao' para 'Aceite' na base de dados
            $sql = "UPDATE users SET Autenticacao = 'Rejeitado' WHERE Email = '$user_email'";

            if (mysqli_query($conn, $sql)) {
                echo "<p>Utilizador Rejeitado com sucesso! </p>";
                echo "<meta http-equiv='refresh' content='1;url=?page=Pedidos'>";
            } else {
                echo "Erro ao aceitar o usuário: " . mysqli_error($conn);
            }
        }

        ?>
    </div>
</body>

</html>