<html>
<a href='../paginas/menu.php?page=gerenciar-utilizadores'> Cancelar</a><br>

</html>

<?php
session_start();
include("../basedados/basedados.h");

if (isset($_POST['alterarConta'])) {
    $user_email = $_POST['alterarConta'];
    echo "Alterar Dados";
    echo $user_email;
}

if (isset($_POST['adicionarSaldo'])) {
    $AdicionarEmail = $_POST['adicionarSaldo'];

    echo "Adicionar Saldo";
    echo $AdicionarEmail;

    /*
    if (mysqli_query($conn, $sql)) {
        echo "<p>Utilizador aceito com sucesso! </p>";
        echo "<meta http-equiv='refresh' content='1;url=?page=gerenciar-utilizadores'>";
    } else {
        echo "Erro ao aceitar o usuário: " . mysqli_error($conn);
    }*/
}

if (isset($_POST['alterarTipo'])) {
    $AlterarEmail = $_POST['alterarTipo'];

    echo "Alterar Tipo";
    echo $AlterarEmail;


    /*
    if (mysqli_query($conn, $sql)) {
        echo "<p>Utilizador aceito com sucesso! </p>";
        echo "<meta http-equiv='refresh' content='1;url=?page=gerenciar-utilizadores'>";
    } else {
        echo "Erro ao aceitar o usuário: " . mysqli_error($conn);
    }*/
}

if (isset($_POST['EliminarConta'])) {
    $eliminarEmail = $_POST['EliminarConta'];
    echo '
    <form method="POST">
        <div>
            <label for="ConfirmarCheckbox">
                <input type="checkbox" name="ConfirmarEliminar" id="ConfirmarCheckbox" required>
                Confirmo que quero eliminar a conta do utilizador de forma permanente, tendo a consciência de que não posso reverter essa ação!
            </label>
            <br>
            <button type="submit" name="ConfirmarButton" value="' . $eliminarEmail . '">Eliminar Utilizador</button>
        </div>
    </form>
    ';
}

if (isset($_POST['ConfirmarButton'])) {
    $user_email= $_POST['ConfirmarButton'];

    $sql = "DELETE FROM users WHERE Email = '$user_email'";


    if (mysqli_query($conn, $sql)) {
        echo "<p>Utilizador eliminado com sucesso! </p>";
        //echo "<meta http-equiv='refresh' content='1;url=?page=gerenciar-utilizadores'>";
    } else {
        echo "Erro ao aceitar o usuário: " . mysqli_error($conn);
    }
}

header(" url=menu.php?page=gerenciar-utilizadores");
?>