<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <form action="validar.php" method="post">
        <p>Usuário: <input type="text" name="txtuser"></p>
        <p>Senha: <input type="password" name="txtsenha"></p>
        <p><input type="submit" name="btn" value="Entrar"></p>
    </form>
    <?php if(!empty($_SESSION['msg'])){echo $_SESSION['msg']; $_SESSION['msg'] = "";}?>
</body>
</html>