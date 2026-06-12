<?php
session_start();
unset($_SESSION['nivel']);
header('location: login.php');
?>