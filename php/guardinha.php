<?php
    session_start();
    function restricao(int $nivel){
        if(!isset($_SESSION['nivel'])){
            header("location: login.php");
        }
        if($_SESSION['nivel'] > $nivel){
            header("location: login.php");
        }
    }
?>