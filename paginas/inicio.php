<html>

<body>
    <a href="logout.php"><input type="submit" value="logout" /></a>

    <label for="pesquisa">Pesquisa:</label><br>
    <input type="text" name="pesquisa">
    <input type="submit" value="pesquisar" /><br>
    <br>
</body>

</html>

<?php

include("../basedados/basedados.h");
date_default_timezone_set('Europe/Lisbon');

echo "Bem-vindo!<br>";
$dataHoraAtual = date('Y-m-d') . '<br>' . date('H:i:s');
echo "$dataHoraAtual <br>";

// Consulta para selecionar todos os usuários com estado 'online'
$sql = "SELECT * FROM user WHERE estado = 'online'";

// Executa a consulta
$result = mysqli_query($conn, $sql);



// Verifica se a consulta retornou resultados
if (mysqli_num_rows($result) > 0) {
    
    // Percorre todos os resultados e imprime os dados
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['tipoUtilizador'] == 3) {
            echo "<a href='menuAdmin.html'><button type='button'>Menu Admin</button></a><br>";
        } else if ($row["tipoUtilizador"] == 2) {
            echo "<button type='submit' name='funcionario'>Menu funcionario</button><br>";
        }
 

        // Imprime os dados do usuário
        echo "Saldo: " . $row['saldo'] . " <a href='addSaldo.html'><input type='submit' value='Adicionar Saldo' /></a> <br>";
        echo "Nome: " . $row['nome'] . "<br>";
        echo "Email: " . $row['email'] . "<br>";
        echo "Estado: " . $row['estado'] . "<br><br>";
    }
} else {
    // Se não houver usuários online
    echo "Nenhum usuário está online.";
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>