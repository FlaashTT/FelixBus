<html>
<body>
<form action="logout.php" method=”GET”>

<input type="submit" value="logout"/>

</form>
</body>
</html>

<?php
include("../basedados/basedados.h");  

echo "Bem-vindo!<br>";

// Consulta para selecionar todos os usuários com estado 'online'
$sql = "SELECT * FROM user WHERE estado = 'online'";

// Executa a consulta
$result = mysqli_query($conn, $sql);

// Verifica se a consulta retornou resultados
if (mysqli_num_rows($result) > 0) {
    // Percorre todos os resultados e imprime os dados
    while ($row = mysqli_fetch_assoc($result)) {
        // Imprime os dados do usuário
        echo "Nome: " . $row['nome'] . "<br>"; // Substitua 'nome' pela coluna desejada
        echo "Email: " . $row['email'] . "<br>"; // Substitua 'email' pela coluna desejada
        echo "Estado: " . $row['estado'] . "<br><br>"; // Substitua 'estado' pela coluna desejada
    }
} else {
    // Se não houver usuários online
    echo "Nenhum usuário está online.";
}

// Fecha a conexão com o banco de dados
mysqli_close($conn);
?>
