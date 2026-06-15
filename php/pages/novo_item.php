<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'novo_item';

    // Cadastrar item encontrado é liberado para todos os níveis (0 a 3)
    restricao(3);

    require_once("../components/dados_mock.php");

    $categorias = categorias_mock();

    // Usuário responsável pelo cadastro = usuário logado
    $responsavel = $_SESSION['username'] ?? 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encontrei um item</title>
    <?php require_once("../components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("../components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Encontrei um item</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">
            <div class="w3-container w3-padding-16 app-card">
                <h4><b>Cadastrar item encontrado</b></h4>
                <p class="w3-text-grey">Preencha os dados do objeto que você encontrou.</p>

                <!--
                    enctype="multipart/form-data" é necessário para envio de imagem.
                    O upload será validado (tipo e tamanho) e gravado via Prepared
                    Statement quando o banco de dados estiver pronto.
                -->
                <form action="#" method="post" enctype="multipart/form-data">
                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Nome do objeto</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="nome" required>
                        </div>
                        <div class="w3-half">
                            <label class="app-label"><b>Categoria</b></label>
                            <select class="w3-select w3-border w3-margin-bottom app-input" name="categoria" required>
                                <option value="" disabled selected>Selecione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Local encontrado</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="text" name="local" required>
                        </div>
                        <div class="w3-half">
                            <label class="app-label"><b>Data encontrada</b></label>
                            <input class="w3-input w3-border w3-margin-bottom app-input" type="date" name="data" required>
                        </div>
                    </div>

                    <label class="app-label"><b>Descrição</b></label>
                    <textarea class="w3-input w3-border w3-margin-bottom app-input" name="descricao" rows="3"
                              placeholder="Detalhes que ajudem a identificar o objeto..."></textarea>

                    <label class="app-label"><b>Foto do objeto</b></label>
                    <input class="w3-input w3-border w3-margin-bottom app-input" type="file" name="foto" accept="image/*">

                    <label class="app-label"><b>Responsável pelo cadastro</b></label>
                    <input class="w3-input w3-border w3-margin-bottom app-input"
                           type="text" value="<?= htmlspecialchars($responsavel) ?>" disabled>

                    <button class="w3-button app-btn-primary w3-margin-top" type="submit">
                        Cadastrar item
                    </button>
                </form>
            </div>
        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
