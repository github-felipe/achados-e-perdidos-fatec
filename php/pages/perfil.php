<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'perfil';

    // Perfil é liberado para todos os níveis (0 a 3)
    restricao(3);

    require_once("../components/dados_mock.php");

    /*
     * Dados do usuário logado.
     * Por enquanto são mockados: usamos o nome vindo da sessão e dados de exemplo.
     * Quando o banco existir, estes dados virão de um SELECT com Prepared Statement
     * usando o $_SESSION['id'].
     */
    $usuario = [
        'nome'     => $_SESSION['username'] ?? 'Usuário',
        'usuario'  => 'usuario.exemplo',
        'email'    => 'usuario@fatec.sp.gov.br',
        'telefone' => '(11) 90000-0000',
        'nivel'    => $_SESSION['nivel'] ?? 3,
    ];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <?php require_once("../components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("../components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Meu perfil</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">
            <div class="w3-row-padding">

                <!-- Coluna 1: dados de contato -->
                <div class="w3-half w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4><b>Dados de contato</b></h4>

                        <!-- O salvamento será implementado com Prepared Statement futuramente -->
                        <form action="#" method="post">
                            <label class="app-label"><b>Nome completo</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" value="<?= htmlspecialchars($usuario['nome']) ?>" disabled>

                            <label class="app-label"><b>Usuário</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" value="<?= htmlspecialchars($usuario['usuario']) ?>" disabled>

                            <label class="app-label"><b>E-mail</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="email" name="email"
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>

                            <label class="app-label"><b>Telefone</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" name="telefone"
                                   value="<?= htmlspecialchars($usuario['telefone']) ?>" required>

                            <button class="w3-button app-btn-primary w3-margin-top" type="submit">
                                Salvar alterações
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Coluna 2: alteração de senha -->
                <div class="w3-half w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4><b>Alterar senha</b></h4>

                        <!-- A nova senha será gravada com password_hash() futuramente -->
                        <form action="#" method="post">
                            <label class="app-label"><b>Senha atual</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="password" name="senha_atual" required>

                            <label class="app-label"><b>Nova senha</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="password" name="senha_nova" required>

                            <label class="app-label"><b>Confirmar nova senha</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="password" name="senha_confirma" required>

                            <button class="w3-button app-btn-secondary w3-margin-top" type="submit">
                                Atualizar senha
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
