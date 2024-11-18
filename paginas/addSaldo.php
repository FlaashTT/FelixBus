<?php

session_start();
$opcao = $_GET["addSaldo"];

switch ($opcao) {
    case 0:
        echo "O ",$eleitor," votou em branco <br>";
    break;
    case 1:
        echo "O ",$eleitor," ,votou em <br>";
        
        for($i=0;$i<=$NumOpcoes;$i++){
            $selected = $opcao[$i];
        }
        
    break;
    case 2:
        echo "O ",$eleitor," votou em branco <br>";
    break;
    case 3:
        echo "O ",$eleitor," tem o seu voto anulado <br>";
    break;
    case 4:
        echo "O ",$eleitor,", tem seu voto seus voto anulado! <br>";
    break;
    }
?>