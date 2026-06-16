<?php
    require_once("guardinha.php");

    $rootPath = '';
    $paginaAtiva = 'dashboard';

    // Painel liberado para qualquer usuário logado.
    restricao(2);

    require_once("components/banco.php");

    $con = conectar_banco();
    $resumo = resumo_painel($con);
    $itens = itens_recentes($con, 5);

    $itensDisponiveis = $resumo['itensDisponiveis'];
    $itensEntregues   = $resumo['itensEntregues'];
    $totalItens       = $resumo['totalItens'];
    $totalUsuarios    = $resumo['totalUsuarios'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <?php require_once("components/head.php"); ?>
</head>
<body class="app-body">
    <?php require_once("components/navbar.php"); ?>

    <div class="w3-main app-main">
        <header class="w3-container app-page-header">
            <h1><b>Dashboard</b></h1>
        </header>

        <div class="app-page-content w3-container w3-padding-32">

            <!-- Cards com as estatísticas gerais do sistema -->
            <div class="w3-row-padding">
                <div class="w3-quarter w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4>Itens disponíveis</h4>
                        <p class="app-card-value"><b><?= $itensDisponiveis ?></b></p>
                        <p class="w3-small app-card-label">Encontrados e aguardando retirada</p>
                    </div>
                </div>
                <div class="w3-quarter w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4>Itens entregues</h4>
                        <p class="app-card-value"><b><?= $itensEntregues ?></b></p>
                        <p class="w3-small app-card-label">Devolvidos aos donos</p>
                    </div>
                </div>
                <div class="w3-quarter w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4>Total de itens</h4>
                        <p class="app-card-value"><b><?= $totalItens ?></b></p>
                        <p class="w3-small app-card-label">Cadastrados no sistema</p>
                    </div>
                </div>
                <div class="w3-quarter w3-margin-bottom">
                    <div class="w3-container w3-padding-16 app-card">
                        <h4>Usuários</h4>
                        <p class="app-card-value"><b><?= $totalUsuarios ?></b></p>
                        <p class="w3-small app-card-label">Cadastrados no sistema</p>
                    </div>
                </div>
            </div>

            <!-- Tabela com os itens cadastrados mais recentes -->
            <div class="w3-container w3-padding-16 app-card w3-margin-top">
                <h4><b>Itens recentes</b></h4>
                <div class="w3-responsive">
                    <table class="w3-table w3-bordered w3-striped fatec-table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Item</th>
                                <th>Categoria</th>
                                <th>Local encontrado</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens as $item): ?>
                                <tr>
                                    <td>
                                        <?php $fotoId = $item['foto_id'] ?? 0; require('components/thumb_item.php'); ?>
                                    </td>
                                    <td><?= htmlspecialchars($item['item']) ?></td>
                                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                                    <td><?= htmlspecialchars($item['local_encontrado']) ?></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($item['data_cadastro']))) ?></td>
                                    <td>
                                        <span class="w3-tag w3-round <?= $item['status'] === 'Disponível' ? 'w3-green' : 'w3-blue' ?>">
                                            <?= htmlspecialchars($item['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <?php require_once("components/footer.php"); ?>
    </div>
</body>
</html>
