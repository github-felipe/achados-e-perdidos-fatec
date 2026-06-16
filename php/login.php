<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php $rootPath = ''; require_once("components/head.php"); ?>
</head>
<body class="app-body">
    <div class="app-login-wrapper w3-container">
        <div class="app-card app-login-card">
            <header class="w3-container w3-center app-page-header app-login-header">
                <h2 class="w3-margin-bottom-0"><b>Achados e Perdidos</b></h2>
                <p class="w3-opacity">FATEC</p>
            </header>

            <form class="w3-container w3-padding-16" action="validar.php" method="post">
                <label class="app-label"><b>Usuário</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="txtuser" required autofocus>

                <label class="app-label"><b>Senha</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="password" name="txtsenha" required>

                <button class="w3-button w3-block w3-section app-btn-primary" type="submit" name="btn" value="Entrar">
                    Entrar
                </button>

                <?php if (!empty($_SESSION['msg'])): ?>
                    <div class="app-alert-error w3-margin-top">
                        <?= htmlspecialchars($_SESSION['msg']) ?>
                    </div>
                    <?php $_SESSION['msg'] = ""; // Limpa a mensagem após exibir ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
