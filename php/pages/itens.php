<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'itens';

    // Gerenciar itens é liberado para Root (0) e Secretaria (1)
    restricao(1);

    require_once("../components/banco.php");

    $con = conectar_banco();
    $itens = itens_recentes($con, 200);
    $categorias = categorias_todas($con);
    $locais = locais_todos($con);
    $status = ['Disponível', 'Entregue'];
    $mensagem = '';
    $erro = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'editar_item') {
            $itemId = (int) ($_POST['item_id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
            $localId = (int) ($_POST['local_id'] ?? 0);
            $data = trim($_POST['data'] ?? '');
            $statusItem = trim($_POST['status'] ?? 'Disponível');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($itemId <= 0 || $nome === '' || $categoriaId <= 0 || $localId <= 0 || $data === '') {
                $erro = 'Preencha os dados do item.';
            } else {
                $resultado = atualizar_item_encontrado_db($con, $itemId, $nome, $descricao, $categoriaId, $localId, $data . ' 00:00:00', $statusItem);
                if (!empty($resultado['ok'])) {
                    $_SESSION['msg'] = 'Item atualizado com sucesso.';
                    header('location: itens.php');
                    exit;
                }
                $erro = $resultado['error'] ?? 'Não foi possível atualizar o item.';
            }
        }

        if ($acao === 'registrar_devolucao') {
            $itemId = (int) ($_POST['item_id'] ?? 0);
            $dataRetirada = trim($_POST['data_retirada'] ?? '');
            $nomeRetirou = trim($_POST['nome_retirou'] ?? '');

            if ($itemId <= 0 || $dataRetirada === '' || $nomeRetirou === '') {
                $erro = 'Informe o item, a data da retirada e o nome de quem está retirando.';
            } else {
                $resultado = registrar_devolucao_item_db($con, $itemId, (int) ($_SESSION['id'] ?? 0), $dataRetirada . ' 00:00:00', $nomeRetirou);
                if (!empty($resultado['ok'])) {
                    $_SESSION['msg'] = 'Devolução registrada com sucesso.';
                    header('location: itens.php');
                    exit;
                }
                $erro = $resultado['error'] ?? 'Não foi possível registrar a devolução.';
            }
        }
    }
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

                <?php if (!empty($_SESSION['msg'])): ?>
                    <div class="app-alert-success w3-margin-bottom"><?= htmlspecialchars($_SESSION['msg']) ?></div>
                    <?php $_SESSION['msg'] = ''; ?>
                <?php endif; ?>

                <?php if ($erro !== ''): ?>
                    <div class="app-alert-error w3-margin-bottom"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <div class="w3-responsive">
                    <table class="w3-table w3-bordered w3-striped fatec-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
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
                                    <td>
                                        <?php $fotoId = $item['foto_id'] ?? 0; require('../components/thumb_item.php'); ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['item']) ?></td>
                                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                                    <td><?= htmlspecialchars($item['local_encontrado']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_cadastro']))) ?></td>
                                    <td>
                                        <span class="w3-tag w3-round <?= $item['status'] === 'Disponível' ? 'fatec-status-disponivel' : 'fatec-status-entregue' ?>">
                                            <?= htmlspecialchars($item['status']) ?>
                                        </span>
                                    </td>
                                    <td class="w3-center">
                                        
                                        <button class="w3-button w3-small app-btn-secondary"
                                                onclick="abrirEditar(this)"
                                            data-item-id="<?= (int) $item['id'] ?>"
                                                data-nome="<?= htmlspecialchars($item['item']) ?>"
                                            data-categoria-id="<?= (int) ($item['categoria_id'] ?? 0) ?>"
                                                data-local-id="<?= (int) ($item['local_id'] ?? 0) ?>"
                                                data-data="<?= htmlspecialchars($item['data_cadastro']) ?>"
                                                data-status="<?= htmlspecialchars($item['status']) ?>"
                                                data-descricao="<?= htmlspecialchars($item['item']) ?>">
                                            Editar
                                        </button>

                                        <?php if ($item['status'] !== 'Entregue'): ?>
                                            <button class="w3-button w3-small app-btn-primary"
                                                    onclick="abrirDevolucao(this)"
                                                    data-nome="<?= htmlspecialchars($item['item']) ?>">
                                                Devolução
                                            </button>
                                        <?php endif; ?>

                                
                                        <button class="w3-button w3-small w3-red"
                                                onclick="confirmarExclusao('<?= htmlspecialchars($item['item'], ENT_QUOTES) ?>')">
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

    <div id="modalEditar" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:600px">
            <header class="w3-container app-page-header">
                <span onclick="document.getElementById('modalEditar').style.display='none'"
                      class="w3-button w3-display-topright">&times;</span>
                <h3><b>Editar item</b></h3>
            </header>

            <!-- A atualização será feita com Prepared Statement futuramente -->
            <form class="w3-container w3-padding-16" action="" method="post">
                <input type="hidden" name="acao" value="editar_item">
                <input type="hidden" name="item_id" id="editItemId">
                <label class="app-label"><b>Nome do objeto</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="nome" id="editNome" required>

                <label class="app-label"><b>Categoria</b></label>
                <select class="w3-select w3-border w3-margin-bottom app-input" name="categoria_id" id="editCategoria">
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['descricao']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="app-label"><b>Local encontrado</b></label>
                <select class="w3-select w3-border w3-margin-bottom app-input" name="local_id" id="editLocal" required>
                    <?php foreach ($locais as $local): ?>
                        <option value="<?= (int) $local['id'] ?>"><?= htmlspecialchars($local['local']) ?></option>
                    <?php endforeach; ?>
                </select>

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

    <!-- Modal: Registrar devolução  -->
    <div id="modalDevolucao" class="w3-modal">
        <div class="w3-modal-content w3-card-4 w3-animate-top" style="max-width:500px">
            <header class="w3-container app-page-header">
                <span onclick="document.getElementById('modalDevolucao').style.display='none'"
                      class="w3-button w3-display-topright">&times;</span>
                <h3><b>Registrar devolução</b></h3>
            </header>

            <form class="w3-container w3-padding-16" action="" method="post">
                <input type="hidden" name="acao" value="registrar_devolucao">
                <input type="hidden" name="item_id" id="devItemId">
                <p>Item: <b id="devNome"></b></p>

                <label class="app-label"><b>Entregue por (funcionário)</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input"
                       type="text" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" disabled>

                <label class="app-label"><b>Nome de quem está retirando *</b></label>
                <input class="w3-input w3-border w3-margin-bottom app-input"
                       type="text" name="nome_retirou" id="devNomeRetirou"
                       placeholder="Nome completo do reclamante" required>

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
            document.getElementById('editItemId').value   = botao.dataset.itemId;
            document.getElementById('editNome').value      = botao.dataset.nome;
            document.getElementById('editCategoria').value = botao.dataset.categoriaId;
            document.getElementById('editLocal').value     = botao.dataset.localId;
            document.getElementById('editData').value      = botao.dataset.data;
            document.getElementById('editStatus').value    = botao.dataset.status;
            document.getElementById('editDescricao').value = botao.dataset.descricao;
            document.getElementById('modalEditar').style.display = 'block';
        }

        // Abre o modal de devolução mostrando o nome do item
        function abrirDevolucao(botao) {
            document.getElementById('devItemId').value = botao.closest('tr').querySelector('[data-item-id]').dataset.itemId;
            document.getElementById('devNome').textContent = botao.dataset.nome;
            document.getElementById('modalDevolucao').style.display = 'block';
        }

        // Confirmação simples antes de excluir
        function confirmarExclusao(nome) {
            if (confirm('Deseja realmente excluir o item "' + nome + '"?')) {
                alert('Exclusão será implementada com o banco de dados.');
            }
        }
    </script>
</body>
</html>
