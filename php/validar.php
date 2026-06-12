<?php
    session_start();
    $senha = $_POST['txtsenha'];
    $usuario = $_POST['txtuser'];
    
    //Aqui será implementado a lógica de login com hash quando o banco de dados estiver pronto, no momento será apenas simulado um login simples para fazermos o front-end.
    if($senha=="1234" && $usuario=="admin"){
        $_SESSION['id'] = 12345;
        $_SESSION['username'] = "teste da silva";
        $_SESSION['nivel'] = "1";
        header("location: index.php");
    } else{
        $_SESSION['msg'] = "<p>Usuário e/ou senha inválido!";
        unset($_SESSION['nivel']);
        header("location: login.php");
    }
?>