<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'perfil';

    // Perfil é liberado para todos os níveis (0 a 2)
    restricao(2);

    require_once("../components/banco.php");

    $con = conectar_banco();
    $usuario = usuario_por_id($con, (int) ($_SESSION['id'] ?? 0));
    $mensagem = '';
    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';
        $usuarioId = (int) ($_SESSION['id'] ?? 0);

        if ($acao === 'atualizar_contato') {
            $email = trim($_POST['email'] ?? '');

            if ($email === '') {
                $erro = 'Preencha o e-mail.';
            } else {
                $resultado = atualizar_usuario_contato_db($con, $usuarioId, $email);
                if (!empty($resultado['ok'])) {
                    $_SESSION['msg'] = 'Perfil atualizado com sucesso.';
                    header('location: perfil.php');
                    exit;
                }
                $erro = $resultado['error'] ?? 'Não foi possível atualizar o perfil.';
            }
        }

        if ($acao === 'atualizar_senha') {
            $senhaAtual = (string) ($_POST['senha_atual'] ?? '');
            $senhaNova = (string) ($_POST['senha_nova'] ?? '');
            $senhaConfirma = (string) ($_POST['senha_confirma'] ?? '');

            if ($senhaAtual === '' || $senhaNova === '' || $senhaConfirma === '') {
                $erro = 'Preencha todos os campos da senha.';
            } elseif ($senhaNova !== $senhaConfirma) {
                $erro = 'A confirmação da nova senha não confere.';
            } else {
                $resultado = atualizar_usuario_senha_db($con, $usuarioId, $senhaAtual, $senhaNova);
                if (!empty($resultado['ok'])) {
                    $_SESSION['msg'] = 'Senha atualizada com sucesso.';
                    header('location: perfil.php');
                    exit;
                }
                $erro = $resultado['error'] ?? 'Não foi possível atualizar a senha.';
            }
        }
    }

    if (!$usuario) {
        $usuario = [
            'nome' => $_SESSION['username'] ?? 'Usuário',
            'usuario' => '',
            'email' => '',
            'nivel' => (int) ($_SESSION['nivel'] ?? 2),
            'nivel_descricao' => nivel_label((int) ($_SESSION['nivel'] ?? 2)),
        ];
    }
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

                        <?php if (!empty($_SESSION['msg'])): ?>
                            <div class="app-alert-success w3-margin-bottom"><?= htmlspecialchars($_SESSION['msg']) ?></div>
                            <?php $_SESSION['msg'] = ''; ?>
                        <?php endif; ?>

                        <?php if ($erro !== ''): ?>
                            <div class="app-alert-error w3-margin-bottom"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>

                        <form action="" method="post">
                            <input type="hidden" name="acao" value="atualizar_contato">
                            <label class="app-label"><b>Nome completo</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" value="<?= htmlspecialchars($usuario['nome']) ?>" disabled>

                            <label class="app-label"><b>Usuário</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                type="text" value="<?= htmlspecialchars($usuario['usuario'] ?: $usuario['email']) ?>" disabled>

                            <label class="app-label"><b>E-mail</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="email" name="email"
                                   value="<?= htmlspecialchars($usuario['email']) ?>" required>

                                     <!-- Telefone removido: uso do cadastro institucional -->

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
                        <form action="" method="post">
                            <input type="hidden" name="acao" value="atualizar_senha">
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
