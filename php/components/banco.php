<?php

require_once __DIR__ . '/hash.php';

function conectar_banco()
{
    $host = '127.0.0.1';
    $port = 3306;
    $user = 'root';
    $pass = '';
    $dbname = 'sistema_itens';

    $con = mysqli_connect($host, $user, $pass, $dbname, $port);

    if (!$con) {
        die('Erro ao conectar no banco de dados: ' . mysqli_connect_error());
    }

    mysqli_set_charset($con, 'utf8mb4');

    return $con;
}

function resumo_painel($con): array
{
    $sql = "
        SELECT
            COUNT(*) AS total_itens,
            SUM(CASE WHEN data_retirada IS NULL THEN 1 ELSE 0 END) AS itens_disponiveis,
            SUM(CASE WHEN data_retirada IS NOT NULL THEN 1 ELSE 0 END) AS itens_entregues
        FROM itens
    ";

    $resultado = mysqli_query($con, $sql);
    $resumo = $resultado ? mysqli_fetch_assoc($resultado) : [];

    $resultadoUsuarios = mysqli_query($con, "SELECT COUNT(*) AS total_usuarios FROM users");
    $totalUsuarios = 0;
    if ($resultadoUsuarios) {
        $linhaUsuarios = mysqli_fetch_assoc($resultadoUsuarios);
        $totalUsuarios = (int) ($linhaUsuarios['total_usuarios'] ?? 0);
    }

    return [
        'itensDisponiveis' => (int) ($resumo['itens_disponiveis'] ?? 0),
        'itensEntregues'   => (int) ($resumo['itens_entregues'] ?? 0),
        'totalItens'       => (int) ($resumo['total_itens'] ?? 0),
        'totalUsuarios'    => $totalUsuarios,
    ];
}

function itens_recentes($con, int $limite = 5): array
{
    $limite = max(1, (int) $limite);

    $sql = "
        SELECT
            i.id,
            i.categoria_id,
            i.local_id,
            i.descricao AS item,
            COALESCE(c.descricao, 'Sem categoria') AS categoria,
            COALESCE(l.local, 'Sem local') AS local_encontrado,
            i.data_cadastro AS data_cadastro,
            CASE
                WHEN i.data_retirada IS NULL THEN 'Disponível'
                ELSE 'Entregue'
            END AS status
        FROM itens i
        LEFT JOIN categorias c ON c.id = i.categoria_id
        LEFT JOIN locais l ON l.id = i.local_id
        ORDER BY i.data_cadastro DESC, i.id DESC
        LIMIT {$limite}
    ";

    $resultado = mysqli_query($con, $sql);
    $itens = [];

    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $itens[] = $linha;
        }
    }

    return $itens;
}

function categorias_todas($con): array
{
    $resultado = mysqli_query($con, "SELECT id, descricao FROM categorias ORDER BY descricao ASC");
    $categorias = [];

    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $categorias[] = $linha;
        }
    }

    return $categorias;
}

function locais_todos($con): array
{
    $resultado = mysqli_query($con, "SELECT id, local FROM locais ORDER BY local ASC");
    $locais = [];

    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $locais[] = $linha;
        }
    }

    return $locais;
}

function niveis_cadastro_disponiveis($con, int $nivelUsuario): array
{
    $nivelUsuario = (int) $nivelUsuario;
    $sql = "SELECT id, nivel, descricao FROM niveis ORDER BY nivel ASC";
    $resultado = mysqli_query($con, $sql);
    $niveis = [];

    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $nivel = (int) $linha['nivel'];
            if ($nivelUsuario === 0 || $nivel > $nivelUsuario) {
                $niveis[] = $linha;
            }
        }
    }

    return $niveis;
}

function usuarios_cadastrados($con, int $nivelUsuario): array
{
    $nivelUsuario = (int) $nivelUsuario;
    $sql = "
        SELECT
            u.id,
            u.nome,
            u.email,
            u.status,
            n.nivel,
            n.descricao AS nivel_descricao
        FROM users u
        INNER JOIN niveis n ON n.id = u.nivel_id
        ORDER BY u.nome ASC
    ";

    $resultado = mysqli_query($con, $sql);
    $usuarios = [];

    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $nivel = (int) $linha['nivel'];
            if ($nivelUsuario === 0 || $nivel > $nivelUsuario) {
                $usuarios[] = [
                    'id' => (int) $linha['id'],
                    'nome' => $linha['nome'],
                    'usuario' => $linha['email'],
                    'email' => $linha['email'],
                    'nivel' => $nivel,
                    'nivel_descricao' => $linha['nivel_descricao'],
                    'ativo' => strtolower((string) $linha['status']) === 'ativo' ? 1 : 0,
                ];
            }
        }
    }

    return $usuarios;
}

function usuario_por_id($con, int $id): array
{
    $id = (int) $id;
    $sql = "
        SELECT
            u.id,
            u.nome,
            u.email,
            u.status,
            n.nivel,
            n.descricao AS nivel_descricao
        FROM users u
        INNER JOIN niveis n ON n.id = u.nivel_id
        WHERE u.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $idUsuario, $nome, $email, $status, $nivel, $nivelDescricao);

    if (!mysqli_stmt_fetch($stmt)) {
        return [];
    }

    return [
        'id' => (int) $idUsuario,
        'nome' => $nome,
        'usuario' => $email,
        'email' => $email,
        'nivel' => (int) $nivel,
        'nivel_descricao' => $nivelDescricao,
        'ativo' => strtolower((string) $status) === 'ativo' ? 1 : 0,
    ];
}

function usuario_campo_existe($con, string $campo, string $valor, ?int $ignorarId = null): bool
{
    $permitidos = ['email'];
    if (!in_array($campo, $permitidos, true)) {
        return false;
    }

    $sql = "SELECT id FROM users WHERE {$campo} = ?";
    $tipos = 's';
    $valores = [$valor];

    if ($ignorarId !== null) {
        $sql .= ' AND id <> ?';
        $tipos .= 'i';
        $valores[] = (int) $ignorarId;
    }

    $sql .= ' LIMIT 1';
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, $tipos, ...$valores);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    return mysqli_stmt_num_rows($stmt) > 0;
}

function criar_usuario_db($con, string $nome, string $email, string $senha, int $nivelId, string $statusAtivo): array
{
    if (usuario_campo_existe($con, 'email', $email)) {
        return ['ok' => false, 'error' => 'Já existe um usuário com esse e-mail.'];
    }
    $senhaHash = hashsenha($senha);
    $sql = "INSERT INTO users (nome, email, senha, nivel_id, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar cadastro de usuário.'];
    }

    mysqli_stmt_bind_param($stmt, 'sssis', $nome, $email, $senhaHash, $nivelId, $statusAtivo);

    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao cadastrar usuário.'];
    }

    return ['ok' => true, 'id' => mysqli_insert_id($con)];
}

function atualizar_usuario_contato_db($con, int $id, string $email): array
{
    if (usuario_campo_existe($con, 'email', $email, $id)) {
        return ['ok' => false, 'error' => 'Esse e-mail já está em uso.'];
    }

    $sql = "UPDATE users SET email = ? WHERE id = ?";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar atualização do perfil.'];
    }

    mysqli_stmt_bind_param($stmt, 'si', $email, $id);
    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao atualizar o perfil.'];
    }

    return ['ok' => true];
}

function atualizar_usuario_senha_db($con, int $id, string $senhaAtual, string $senhaNova): array
{
    $sqlSenha = "SELECT senha FROM users WHERE id = ? LIMIT 1";
    $stmtSenha = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmtSenha, $sqlSenha)) {
        return ['ok' => false, 'error' => 'Falha ao verificar a senha atual.'];
    }

    mysqli_stmt_bind_param($stmtSenha, 'i', $id);
    mysqli_stmt_execute($stmtSenha);
    mysqli_stmt_bind_result($stmtSenha, $senhaBanco);
    if (!mysqli_stmt_fetch($stmtSenha)) {
        return ['ok' => false, 'error' => 'Usuário não encontrado.'];
    }

    if (($senhaBanco ?? '') !== hashsenha($senhaAtual)) {
        return ['ok' => false, 'error' => 'A senha atual está incorreta.'];
    }

    $sql = "UPDATE users SET senha = ? WHERE id = ?";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar a nova senha.'];
    }

    $senhaHash = hashsenha($senhaNova);
    mysqli_stmt_bind_param($stmt, 'si', $senhaHash, $id);
    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao atualizar a senha.'];
    }

    return ['ok' => true];
}

function formatar_descricao_item(string $nome, string $descricao): string
{
    $nome = trim($nome);
    $descricao = trim($descricao);

    if ($nome === '') {
        return $descricao;
    }

    if ($descricao === '') {
        return $nome;
    }

    return $nome . ' — ' . $descricao;
}

function inserir_imagem_upload_db($con, array $arquivo): array
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => ''];
    }

    $tmpName = $arquivo['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['ok' => false, 'error' => 'Arquivo de imagem inválido.'];
    }

    $mime = function_exists('mime_content_type') ? (string) mime_content_type($tmpName) : (string) ($arquivo['type'] ?? 'application/octet-stream');
    $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mime, $permitidos, true)) {
        return ['ok' => false, 'error' => 'Envie uma imagem JPG, PNG, GIF ou WEBP.'];
    }

    $conteudo = file_get_contents($tmpName);
    if ($conteudo === false) {
        return ['ok' => false, 'error' => 'Não foi possível ler a imagem enviada.'];
    }

    $nomeArquivo = basename((string) ($arquivo['name'] ?? 'imagem'));
    $tamanho = (int) strlen($conteudo);
    $sql = "INSERT INTO imagens (nome_arquivo, tipo_mime, imagem, tamanho) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar a imagem.'];
    }

    $null = null;
    mysqli_stmt_bind_param($stmt, 'ssbi', $nomeArquivo, $mime, $null, $tamanho);
    mysqli_stmt_send_long_data($stmt, 2, $conteudo);

    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao salvar a imagem.'];
    }

    return ['ok' => true, 'id' => mysqli_insert_id($con)];
}

function inserir_item_encontrado_db($con, string $nome, string $descricao, int $categoriaId, int $localId, int $cadastrouId, ?int $fotoId, string $dataCadastro): array
{
    $descricaoCompleta = formatar_descricao_item($nome, $descricao);
    $sql = "INSERT INTO itens (descricao, categoria_id, foto_id, local_id, data_cadastro, cadastrou_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar item.'];
    }

    if ($fotoId === null) {
        $sql = "INSERT INTO itens (descricao, categoria_id, foto_id, local_id, data_cadastro, cadastrou_id) VALUES (?, ?, NULL, ?, ?, ?)";
        $stmt = mysqli_stmt_init($con);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            return ['ok' => false, 'error' => 'Falha ao preparar item.'];
        }
        mysqli_stmt_bind_param($stmt, 'siisi', $descricaoCompleta, $categoriaId, $localId, $dataCadastro, $cadastrouId);
    } else {
        mysqli_stmt_bind_param($stmt, 'siiisi', $descricaoCompleta, $categoriaId, $fotoId, $localId, $dataCadastro, $cadastrouId);
    }

    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao salvar o item.'];
    }

    return ['ok' => true, 'id' => mysqli_insert_id($con)];
}

function atualizar_item_encontrado_db($con, int $id, string $nome, string $descricao, int $categoriaId, int $localId, string $dataCadastro, string $status): array
{
    $descricaoCompleta = formatar_descricao_item($nome, $descricao);
    $ehEntregue = mb_strtolower(trim($status), 'UTF-8') === 'entregue';
    $sql = "UPDATE itens SET descricao = ?, categoria_id = ?, local_id = ?, data_cadastro = ?, data_retirada = " . ($ehEntregue ? 'COALESCE(data_retirada, CURRENT_TIMESTAMP)' : 'NULL') . ", retirou_id = " . ($ehEntregue ? 'COALESCE(retirou_id, ?)' : 'NULL') . " WHERE id = ?";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar atualização do item.'];
    }

    if ($ehEntregue) {
        $usuarioId = (int) ($_SESSION['id'] ?? 0);
        mysqli_stmt_bind_param($stmt, 'siisii', $descricaoCompleta, $categoriaId, $localId, $dataCadastro, $usuarioId, $id);
    } else {
        mysqli_stmt_bind_param($stmt, 'siisi', $descricaoCompleta, $categoriaId, $localId, $dataCadastro, $id);
    }

    if (!mysqli_stmt_execute($stmt)) {
        return ['ok' => false, 'error' => 'Falha ao atualizar o item.'];
    }

    return ['ok' => true];
}

function registrar_devolucao_item_db($con, int $itemId, int $usuarioId, string $dataRetirada): array
{
    $sql = "UPDATE itens SET data_retirada = ?, retirou_id = ? WHERE id = ? AND data_retirada IS NULL";
    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return ['ok' => false, 'error' => 'Falha ao preparar devolução.'];
    }

    mysqli_stmt_bind_param($stmt, 'sii', $dataRetirada, $usuarioId, $itemId);
    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) === 0) {
        return ['ok' => false, 'error' => 'Não foi possível registrar a devolução.'];
    }

    return ['ok' => true];
}

function normalizar_termos_busca(string $texto): array
{
    $texto = mb_strtolower(trim($texto), 'UTF-8');
    $texto = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $texto);
    $partes = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $stopwords = ['de', 'da', 'do', 'dos', 'das', 'e', 'em', 'na', 'no', 'para', 'por', 'um', 'uma', 'o', 'a'];
    $termos = [];

    foreach ($partes as $parte) {
        if (mb_strlen($parte, 'UTF-8') < 3) {
            continue;
        }

        if (in_array($parte, $stopwords, true)) {
            continue;
        }

        $termos[] = $parte;
    }

    return array_values(array_unique($termos));
}

function mysqli_bind_params_dynamic(mysqli_stmt $stmt, string $types, array &$values): bool
{
    $params = [$stmt, $types];

    foreach ($values as $index => &$value) {
        $params[] = &$value;
    }

    return call_user_func_array('mysqli_stmt_bind_param', $params);
}

function buscar_itens_disponiveis_por_termos($con, array $termos, int $limite = 5): array
{
    $termos = array_values(array_filter(array_map('trim', $termos)));
    if (empty($termos)) {
        return [];
    }

    $limite = max(1, (int) $limite);
    $condicoes = [];
    $valores = [];

    foreach ($termos as $termo) {
        $condicoes[] = '(LOWER(i.descricao) LIKE ? OR LOWER(c.descricao) LIKE ? OR LOWER(l.local) LIKE ?)';
        $like = '%' . mb_strtolower($termo, 'UTF-8') . '%';
        $valores[] = $like;
        $valores[] = $like;
        $valores[] = $like;
    }

    $sql = "
        SELECT
            i.id,
            i.descricao AS item,
            COALESCE(c.descricao, 'Sem categoria') AS categoria,
            COALESCE(l.local, 'Sem local') AS local_encontrado,
            i.data_cadastro AS data_cadastro
        FROM itens i
        LEFT JOIN categorias c ON c.id = i.categoria_id
        LEFT JOIN locais l ON l.id = i.local_id
        WHERE i.data_retirada IS NULL
          AND (" . implode(' OR ', $condicoes) . ")
        ORDER BY i.data_cadastro DESC, i.id DESC
        LIMIT {$limite}
    ";

    $stmt = mysqli_stmt_init($con);
    if (!mysqli_stmt_prepare($stmt, $sql)) {
        return [];
    }

    $tipos = str_repeat('s', count($valores));
    if ($tipos !== '' && !mysqli_bind_params_dynamic($stmt, $tipos, $valores)) {
        return [];
    }

    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    $itens = [];
    if ($resultado) {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $itens[] = $linha;
        }
    }

    return $itens;
}