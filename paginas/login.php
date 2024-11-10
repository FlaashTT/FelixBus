

<?php
if($_POST["username"] !="" && $_POST["password"] !=""){
    $username = $_POST["username"];
    $password = $_POST["password"];

    echo("bem vindo $username <br>");
    echo("pass $password");
}else{
    echo("Preencha todos os campos!");
}

?>