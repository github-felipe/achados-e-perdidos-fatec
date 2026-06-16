<?php
    require_once('guardinha.php');

    $rootPath = '';
    restricao(2);

    require_once('components/banco.php');

    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(404);
        exit;
    }

    $con = conectar_banco();
    $imagem = imagem_por_id($con, $id);

    if (!$imagem || empty($imagem['conteudo'])) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: ' . $imagem['tipo_mime']);
    header('Content-Length: ' . $imagem['tamanho']);
    echo $imagem['conteudo'];
    exit;
