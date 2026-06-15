<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'cadastro';

    // Cadastro de usuários é liberado para Root (0), Direção (1) e Secretaria (2)
    restricao(2);

    require_once("../components/dados_mock.php");

    $usuarios = usuarios_mock();

    /*
     * Define quais níveis o usuário logado PODE cadastrar:
     *   - Root (0)      → pode cadastrar qualquer nível.
     *   - Demais níveis → só podem cadastrar níveis abaixo do seu (número maior).
     * Ex.: Direção (1) cadastra 2 e 3; Secretaria (2) cadastra apenas 3.
     */
    $nivelUsuario = (int) ($_SESSION['nivel'] ?? 3);
    $niveisDisponiveis = [];
    foreach ([0, 1, 2, 3] as $nivel) {
        if ($nivelUsuario === 0 || $nivel > $nivelUsuario) {
            $niveisDisponiveis[] = $nivel;
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar usuário</title>
    <?php require_once("../components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("../components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Cadastrar usuário</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">

            <!-- Formulário de cadastro de usuário -->
            <div class="w3-container w3-padding-16 app-card w3-margin-bottom">
                <h4><b>Novo usuário</b></h4>

                <!-- A senha será gravada com password_hash() e os dados via Prepared Statement -->
                <form action="#" method="post">
                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Nome completo</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="nome" required>
                        </div>
                        <div class="w3-half">
                            <label class="app-label"><b>Usuário (login)</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="usuario" required>
                        </div>
                    </div>

                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>E-mail</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="email" name="email" required>
                        </div>
                        <div class="w3-half">
                            <label class="app-label"><b>Telefone</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="telefone" required>
                        </div>
                    </div>

                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Senha</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="password" name="senha" required>
                        </div>
                        <div class="w3-half">
                            <label class="app-label"><b>Nível de acesso</b></label>
                            <select class="w3-select w3-border w3-margin-bottom app-input" name="nivel" required>
                                <?php foreach ($niveisDisponiveis as $nivel): ?>
                                    <option value="<?= $nivel ?>"><?= htmlspecialchars(nivel_label($nivel)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Status</b></label>
                            <select class="w3-select w3-border w3-margin-bottom app-input" name="ativo" required>
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                    </div>

                    <button class="w3-button app-btn-primary w3-margin-top" type="submit">
                        Cadastrar usuário
                    </button>
                </form>
            </div>

            <!-- Lista dos usuários já cadastrados -->
            <div class="w3-container w3-padding-16 app-card">
                <h4><b>Usuários cadastrados</b></h4>
                <div class="w3-responsive">
                    <table class="w3-table w3-bordered w3-striped fatec-table">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Nível</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['nome']) ?></td>
                                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['telefone']) ?></td>
                                    <td><?= htmlspecialchars(nivel_label($u['nivel'])) ?></td>
                                    <td>
                                        <?php if ($u['ativo']): ?>
                                            <span class="w3-tag w3-round fatec-status-disponivel">Ativo</span>
                                        <?php else: ?>
                                            <span class="w3-tag w3-round fatec-status-entregue">Inativo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
