<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'novo_item';

    // Cadastrar item encontrado é liberado para todos os níveis (0 a 2)
    restricao(2);

    require_once("../components/banco.php");

    $con = conectar_banco();
    $categorias = categorias_todas($con);
    $locais = locais_todos($con);
    $mensagem = '';
    $erro = '';

    // Usuário responsável pelo cadastro = usuário logado
    $responsavel = $_SESSION['username'] ?? 'Usuário';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome = trim($_POST['nome'] ?? '');
        $categoriaId = (int) ($_POST['categoria'] ?? 0);
        $localId = (int) ($_POST['local_id'] ?? 0);
        $data = trim($_POST['data'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');

        if ($nome === '' || $categoriaId <= 0 || $localId <= 0 || $data === '') {
            $erro = 'Preencha os campos obrigatórios.';
        } else {
            mysqli_begin_transaction($con);

            try {
                $fotoId = null;
                if (!empty($_FILES['foto']['name']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    $imagem = inserir_imagem_upload_db($con, $_FILES['foto']);
                    if (empty($imagem['ok'])) {
                        throw new RuntimeException($imagem['error'] ?: 'Falha ao salvar a imagem.');
                    }
                    $fotoId = (int) $imagem['id'];
                }

                $resultadoItem = inserir_item_encontrado_db(
                    $con,
                    $nome,
                    $descricao,
                    $categoriaId,
                    $localId,
                    (int) ($_SESSION['id'] ?? 0),
                    $fotoId,
                    $data . ' 00:00:00'
                );

                if (empty($resultadoItem['ok'])) {
                    throw new RuntimeException($resultadoItem['error'] ?? 'Falha ao salvar o item.');
                }

                mysqli_commit($con);
                $_SESSION['msg'] = 'Item cadastrado com sucesso.';
                header('location: novo_item.php');
                exit;
            } catch (Throwable $e) {
                mysqli_rollback($con);
                $erro = $e->getMessage();
            }
        }
    }
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

                <?php if (!empty($_SESSION['msg'])): ?>
                    <div class="app-alert-success w3-margin-bottom"><?= htmlspecialchars($_SESSION['msg']) ?></div>
                    <?php $_SESSION['msg'] = ''; ?>
                <?php endif; ?>

                <?php if ($erro !== ''): ?>
                    <div class="app-alert-error w3-margin-bottom"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form action="" method="post" enctype="multipart/form-data">
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
                                    <option value="<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['descricao']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="w3-row-padding">
                        <div class="w3-half">
                            <label class="app-label"><b>Local encontrado</b></label>
                            <select class="w3-select w3-border w3-margin-bottom app-input" name="local_id" required>
                                <option value="" disabled selected>Selecione...</option>
                                <?php foreach ($locais as $local): ?>
                                    <option value="<?= (int) $local['id'] ?>"><?= htmlspecialchars($local['local']) ?></option>
                                <?php endforeach; ?>
                            </select>
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

                    <button class="w3-button app-btn-primary w3-margin-top" type="submit" name="acao" value="cadastrar_item">
                        Cadastrar item
                    </button>
                </form>
            </div>
        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
