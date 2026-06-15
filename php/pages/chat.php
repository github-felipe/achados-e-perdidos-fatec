<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'chat';

    // Procurar item é liberado para todos os níveis (0 a 3)
    restricao(3);

    require_once("../components/dados_mock.php");

    $categorias = categorias_mock();

    // Lê os filtros enviados pelo formulário (método GET)
    $filtroNome      = trim($_GET['nome'] ?? '');
    $filtroCategoria = $_GET['categoria'] ?? '';
    $filtroLocal     = trim($_GET['local'] ?? '');
    $filtroData      = $_GET['data'] ?? '';

    /*
     * Aplica a busca sobre os dados mockados.
     * Itens já "Entregue" não aparecem, pois não estão mais disponíveis.
     * Quando houver banco, esta lógica vira um SELECT com Prepared Statement e
     * cláusulas WHERE montadas a partir dos mesmos filtros.
     */
    $resultados = [];
    foreach (itens_mock() as $item) {
        if ($item['status'] === 'Entregue') {
            continue;
        }
        // Cada filtro só restringe quando foi preenchido
        if ($filtroNome !== '' && stripos($item['nome'], $filtroNome) === false) {
            continue;
        }
        if ($filtroCategoria !== '' && $item['categoria'] !== $filtroCategoria) {
            continue;
        }
        if ($filtroLocal !== '' && stripos($item['local_encontrado'], $filtroLocal) === false) {
            continue;
        }
        if ($filtroData !== '' && $item['data_encontrado'] !== $filtroData) {
            continue;
        }
        $resultados[] = $item;
    }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procurar item</title>
    <?php require_once("../components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("../components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Procurar item</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">

            <!-- Formulário de busca -->
            <div class="w3-container w3-padding-16 app-card w3-margin-bottom">
                <h4><b>Filtros de busca</b></h4>
                <form action="" method="get">
                    <div class="w3-row-padding">
                        <div class="w3-quarter">
                            <label class="app-label"><b>Nome</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" name="nome" value="<?= htmlspecialchars($filtroNome) ?>">
                        </div>
                        <div class="w3-quarter">
                            <label class="app-label"><b>Categoria</b></label>
                            <select class="w3-select w3-border w3-margin-bottom app-input" name="categoria">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"
                                        <?= $filtroCategoria === $cat ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w3-quarter">
                            <label class="app-label"><b>Local</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="text" name="local" value="<?= htmlspecialchars($filtroLocal) ?>">
                        </div>
                        <div class="w3-quarter">
                            <label class="app-label"><b>Data encontrada</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input"
                                   type="date" name="data" value="<?= htmlspecialchars($filtroData) ?>">
                        </div>
                    </div>

                    <button class="w3-button app-btn-primary" type="submit">Buscar</button>
                    <a class="w3-button app-btn-secondary" href="chat.php">Limpar</a>
                </form>
            </div>

            <!-- Resultados da busca -->
            <h4><b>Resultados</b> <span class="w3-text-grey">(<?= count($resultados) ?>)</span></h4>

            <?php if (empty($resultados)): ?>
                <div class="w3-panel app-card w3-padding-16">
                    <p>Nenhum item encontrado com os filtros informados.</p>
                </div>
            <?php else: ?>
                <div class="w3-row-padding">
                    <?php foreach ($resultados as $item): ?>
                        <div class="w3-third w3-margin-bottom">
                            <div class="w3-container w3-padding-16 app-card">
                                <h5><b><?= htmlspecialchars($item['nome']) ?></b></h5>
                                <p class="w3-margin-bottom-0">
                                    <span class="w3-tag w3-round <?= classe_status($item['status']) ?>">
                                        <?= htmlspecialchars($item['status']) ?>
                                    </span>
                                </p>
                                <p class="w3-small w3-text-grey"><?= htmlspecialchars($item['categoria']) ?></p>
                                <p><?= htmlspecialchars($item['descricao']) ?></p>
                                <p class="w3-small">
                                    <b>Local:</b> <?= htmlspecialchars($item['local_encontrado']) ?><br>
                                    <b>Encontrado em:</b> <?= htmlspecialchars(date('d/m/Y', strtotime($item['data_encontrado']))) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
