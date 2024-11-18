<?php

session_start();


if (isset($_GET["addSaldo"])) {
    $opcao = $_GET["addSaldo"];
}else{
    $opcao = 0;
}
 


switch ($opcao) {
    case 0:
        echo "Tem de adicionar uma quantidade que deseja adicionar";
        header("Refresh: 2; url=addSaldo.html");
    break;
    case 1:
        echo "1"; 
    break;
    case 2:
        echo "2";
    break;
    case 3:
        echo "3";
    break;
    case 4:
        echo "4";
    break;
}
?>
