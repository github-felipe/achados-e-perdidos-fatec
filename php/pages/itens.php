<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'itens';

    // Gerenciar itens é liberado para Root (0), Direção (1) e Secretaria (2)
    restricao(2);

    require_once("../components/dados_mock.php");

    $itens      = itens_mock();
    $categorias = categorias_mock();
    $status     = status_itens_mock();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar itens</title>
    <?php require_once("../components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("../components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Gerenciar itens</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">
            <div class="w3-container w3-padding-16 app-card">
                <h4><b>Itens cadastrados</b></h4>

                <div class="w3-responsive">
                    <table class="w3-table w3-bordered w3-striped fatec-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Categoria</th>
                                <th>Local</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th class="w3-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['nome']) ?></td>
                                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                                    <td><?= htmlspecialchars($item['local_encontrado']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_encontrado']))) ?></td>
                                    <td>
                                        <span class="w3-tag w3-round <?= classe_status($item['status']) ?>">
                                            <?= htmlspecialchars($item['status']) ?>
                                        </span>
                                    </td>
                                    <td class="w3-center">
                                        <!--
                                            Os dados do item ficam em atributos data-* e são lidos
                                            pelo JavaScript para preencher os modais ao clicar.
                                        -->
                                        <button class="w3-button w3-small app-btn-secondary"
                                                onclick="abrirEditar(this)"
                                                data-nome="<?= htmlspecialchars($item['nome']) ?>"
                                                data-categoria="<?= htmlspecialchars($item['categoria']) ?>"
                                                data-local="<?= htmlspecialchars($item['local_encontrado']) ?>"
                                                data-data="<?= htmlspecialchars($item['data_encontrado']) ?>"
                                                data-status="<?= htmlspecialchars($item['status']) ?>"
                                                data-descricao="<?= htmlspecialchars($item['descricao']) ?>">
                                            Editar
                                        </button>

                                        <?php if ($item['status'] !== 'Entregue'): ?>
                                            <button class="w3-button w3-small app-btn-primary"
                                                    onclick="abrirDevolucao(this)"
                                                    data-nome="<?= htmlspecialchars($item['nome']) ?>">
                                                Devolução
                                            </button>
                                        <?php endif; ?>

                                        <!-- Exclusão pede confirmação antes (mockado) -->
                                        <button class="w3-button w3-small w3-red"
                                                onclick="confirmarExclusao('<?= htmlspecialchars($item['nome'], ENT_QUOTES) ?>')">
                                            Excluir
                                        </button>
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

    <!-- ───────────── Modal: Editar item ───────────── -->
    <div id="modalEditar" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:600px">
            <header class="w3-container app-page-header">
                <span onclick="document.getElementById('modalEditar').style.display='none'"
                      class="w3-button w3-display-topright">&times;</span>
                <h3><b>Editar item</b></h3>
            </header>

            <!-- A atualização será feita com Prepared Statement futuramente -->
            <form class="w3-container w3-padding-16" action="#" method="post">
                <label class="app-label"><b>Nome do objeto</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="nome" id="editNome" required>

                <label class="app-label"><b>Categoria</b></label>
                <select class="w3-select w3-border w3-margin-bottom app-input" name="categoria" id="editCategoria">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="app-label"><b>Local encontrado</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="local" id="editLocal" required>

                <label class="app-label"><b>Data encontrada</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="date" name="data" id="editData" required>

                <label class="app-label"><b>Status</b></label>
                <select class="w3-select w3-border w3-margin-bottom app-input" name="status" id="editStatus">
                    <?php foreach ($status as $st): ?>
                        <option value="<?= htmlspecialchars($st) ?>"><?= htmlspecialchars($st) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="app-label"><b>Descrição</b></label>
                <textarea class="w3-input w3-border w3-margin-bottom app-input" name="descricao" id="editDescricao" rows="3"></textarea>

                <button class="w3-button app-btn-primary w3-margin-top" type="submit">Salvar</button>
                <button class="w3-button app-btn-secondary w3-margin-top" type="button"
                        onclick="document.getElementById('modalEditar').style.display='none'">Cancelar</button>
            </form>
        </div>
    </div>

    <!-- ───────────── Modal: Registrar devolução ───────────── -->
    <div id="modalDevolucao" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:500px">
            <header class="w3-container app-page-header">
                <span onclick="document.getElementById('modalDevolucao').style.display='none'"
                      class="w3-button w3-display-topright">&times;</span>
                <h3><b>Registrar devolução</b></h3>
            </header>

            <!-- O registro da devolução ao dono será feito com Prepared Statement futuramente -->
            <form class="w3-container w3-padding-16" action="#" method="post">
                <p>Item: <b id="devNome"></b></p>

                <label class="app-label"><b>Retirado por (nome do dono)</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="retirado_por" required>

                <label class="app-label"><b>Data da retirada</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="date" name="data_retirada" required>

                <button class="w3-button app-btn-primary w3-margin-top" type="submit">Confirmar devolução</button>
                <button class="w3-button app-btn-secondary w3-margin-top" type="button"
                        onclick="document.getElementById('modalDevolucao').style.display='none'">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // Abre o modal de edição preenchendo os campos com os dados do botão clicado
        function abrirEditar(botao) {
            document.getElementById('editNome').value      = botao.dataset.nome;
            document.getElementById('editCategoria').value = botao.dataset.categoria;
            document.getElementById('editLocal').value     = botao.dataset.local;
            document.getElementById('editData').value      = botao.dataset.data;
            document.getElementById('editStatus').value    = botao.dataset.status;
            document.getElementById('editDescricao').value = botao.dataset.descricao;
            document.getElementById('modalEditar').style.display = 'block';
        }

        // Abre o modal de devolução mostrando o nome do item
        function abrirDevolucao(botao) {
            document.getElementById('devNome').textContent = botao.dataset.nome;
            document.getElementById('modalDevolucao').style.display = 'block';
        }

        // Confirmação simples antes de excluir (mockado)
        function confirmarExclusao(nome) {
            if (confirm('Deseja realmente excluir o item "' + nome + '"?')) {
                alert('Exclusão será implementada com o banco de dados.');
            }
        }
    </script>
</body>
</html>
