<?php

function carregar_variaveis_env(string $arquivoEnv): void
{
    if (!is_file($arquivoEnv) || !is_readable($arquivoEnv)) {
        return;
    }

    $linhas = file($arquivoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($linhas === false) {
        return;
    }

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }

        if (!str_contains($linha, '=')) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $chave = trim($chave);
        $valor = trim($valor);

        if ($chave === '') {
            continue;
        }

        if (
            (str_starts_with($valor, '"') && str_ends_with($valor, '"')) ||
            (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
        ) {
            $valor = substr($valor, 1, -1);
        }

        if (getenv($chave) === false) {
            putenv($chave . '=' . $valor);
            $_ENV[$chave] = $valor;
            $_SERVER[$chave] = $valor;
        }
    }
}

function env(string $chave, ?string $padrao = null): ?string
{
    $valor = getenv($chave);
    if ($valor !== false && $valor !== '') {
        return $valor;
    }

    return $_ENV[$chave] ?? $_SERVER[$chave] ?? $padrao;
}