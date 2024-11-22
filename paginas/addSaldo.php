<?php
session_start();
include("../basedados/basedados.h");

// Verifica se "addSaldo" foi enviado e se é um array
if (isset($_GET["addSaldo"]) && is_array($_GET["addSaldo"])) {
    $opcoes = $_GET["addSaldo"]; // Captura todas as opções marcadas
} else {
    $opcoes = [];
}

// Garante que apenas uma checkbox foi marcada
if (count($opcoes) === 0) {
    echo "Tem de adicionar uma quantidade que deseja adicionar";
    header("Refresh: 2; url=addSaldo.html");
    exit();
} elseif (count($opcoes) > 1) {
    echo "Erro: Apenas uma opção pode ser selecionada.";
    header("Refresh: 2; url=addSaldo.html");
    exit();
}

// Apenas uma opção está marcada, obtém o valor
$opcao = $opcoes[0];


$xxx = 0;
switch ($opcao) {
    case "5":
    case "10":
    case "20":
        $valorToUpdate = $opcao + 0;

        $sql = "SELECT saldo FROM user WHERE estado = 'online' ";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $proxSaldo = $row['saldo'] + $valorToUpdate;
                $update_sql = "UPDATE user SET saldo = '$proxSaldo'  WHERE estado = 'online'";
                $update_result = mysqli_query($conn, $update_sql);
            }
            //buscar o saldo antigo e adicionar 
        }
        echo("Adicionou $valorToUpdate ha sua carteira" );
        header("Refresh: 2; url=inicio.php");
        break;
    case "OutraOp":
        if(!isset($_GET["valorOutraOpcao"]) && $_GET["valorOutraOpcao"] === null ) {
            echo ("Tem de inserir o valor");
            header("Refresh: 2; url=addSaldo.html");
        }


        $valorToUpdate = $_GET["valorOutraOpcao"] + 0;

        $sql = "SELECT saldo FROM user WHERE estado = 'online' ";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $proxSaldo = $row['saldo'] + $valorToUpdate;
                $update_sql = "UPDATE user SET saldo = '$proxSaldo'  WHERE estado = 'online'";
                $update_result = mysqli_query($conn, $update_sql);
            }
            
        }
        echo("Adicionou $valorToUpdate ha sua carteira" );
        header("Refresh: 2; url=inicio.php");
        break;
    default:
        echo "Erro: Opção inválida.";
        header("Refresh: 2; url=addSaldo.html");
        exit();
}
?>
