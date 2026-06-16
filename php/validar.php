<?php
session_start();

require_once('components/banco.php');
require_once('components/hash.php');

$usuario = trim($_POST['txtuser'] ?? '');
$senha   = $_POST['txtsenha'] ?? '';

if ($usuario === '' || $senha === '') {
    $_SESSION['msg'] = 'Usuário e/ou senha inválido!';
    header('location: login.php');
    exit;
}

$con = conectar_banco();

// Busca usuário por email OU nome e traz o nível real do cadastro
$sql = "
    SELECT u.id, u.nome, n.nivel, n.descricao, u.senha
    FROM users u
    INNER JOIN niveis n ON n.id = u.nivel_id
    WHERE (u.email = ? OR u.nome = ?)
    LIMIT 1
";
$stmt = mysqli_stmt_init($con);
if (!mysqli_stmt_prepare($stmt, $sql)) {
    $_SESSION['msg'] = 'Erro interno (consulta)';
    header('location: login.php');
    exit;
}

$senhaHash = hashsenha($senha);
mysqli_stmt_bind_param($stmt, 'ss', $usuario, $usuario);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $id, $nome, $nivel, $perfil, $senhaBanco);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_fetch($stmt);
    // compara hashes
    if ($senhaBanco === $senhaHash) {
        $_SESSION['id'] = $id;
        $_SESSION['username'] = $nome;
        $_SESSION['nivel'] = $nivel;
        $_SESSION['perfil'] = $perfil;
        header('location: index.php');
        exit;
    }
}

$_SESSION['msg'] = 'Usuário e/ou senha inválido!';
unset($_SESSION['nivel']);
header('location: login.php');
exit;
?>
