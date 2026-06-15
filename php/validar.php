<?php
    session_start();

    // Dados enviados pelo formulário de login
    $usuario = $_POST['txtuser'] ?? '';
    $senha   = $_POST['txtsenha'] ?? '';

    /*
     * LOGIN MOCKADO (temporário).
     *
     * Enquanto o banco de dados não existe, usamos uma lista fixa de usuários
     * apenas para permitir testar as telas e os diferentes níveis de acesso.
     * Senha de teste para todos: 1234
     *
     * Quando o banco estiver pronto, esta lógica será substituída por:
     *   1. SELECT do usuário com Prepared Statement.
     *   2. Conferência da senha com password_verify($senha, $hashDoBanco).
     */
    $usuariosTeste = [
        'admin'  => ['id' => 1, 'nome' => 'Administrador (Root)', 'nivel' => 0],
        'maria'  => ['id' => 2, 'nome' => 'Maria da Direção',     'nivel' => 1],
        'joao'   => ['id' => 3, 'nome' => 'João da Secretaria',   'nivel' => 2],
        'carlos' => ['id' => 4, 'nome' => 'Carlos Aluno',         'nivel' => 3],
    ];

    // Verifica se o usuário existe e se a senha de teste confere
    if (isset($usuariosTeste[$usuario]) && $senha === '1234') {
        $dados = $usuariosTeste[$usuario];
        $_SESSION['id']       = $dados['id'];
        $_SESSION['username'] = $dados['nome'];
        $_SESSION['nivel']    = $dados['nivel'];
        header("location: index.php");
        exit;
    }

    // Credenciais inválidas: guarda a mensagem e volta para o login
    $_SESSION['msg'] = "Usuário e/ou senha inválido!";
    unset($_SESSION['nivel']);
    header("location: login.php");
    exit;
?>
