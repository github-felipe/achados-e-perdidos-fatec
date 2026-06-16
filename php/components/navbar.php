<?php
    $rootPath = $rootPath ?? '';
    $paginaAtiva = $paginaAtiva ?? '';

    // Links disponíveis para todos os níveis de acesso
    $links = [
        ['id' => 'dashboard',  'label' => 'Dashboard',         'href' => $rootPath . 'index.php'],
        ['id' => 'perfil',     'label' => 'Perfil',            'href' => $rootPath . 'pages/perfil.php'],
        ['id' => 'chat',       'label' => 'Procurar item',     'href' => $rootPath . 'pages/chat.php'],
        ['id' => 'novo_item',  'label' => 'Encontrei um item', 'href' => $rootPath . 'pages/novo_item.php'],
    ];

    // Links administrativos: liberados apenas para Root (0) e Secretaria (1)
    if (isset($_SESSION['nivel']) && (int) $_SESSION['nivel'] <= 1) {
        array_splice($links, 1, 0, [
            ['id' => 'itens',    'label' => 'Gerenciar itens',    'href' => $rootPath . 'pages/itens.php'],
            ['id' => 'cadastro', 'label' => 'Cadastrar usuário',  'href' => $rootPath . 'pages/cadastro.php'],
        ]);
    }

    $username = $_SESSION['username'] ?? 'Usuário';
    $cargo    = $_SESSION['perfil'] ?? (isset($_SESSION['nivel']) ? nivel_label($_SESSION['nivel']) : '');
?>

<!-- Sidebar (menu lateral fixo no desktop / recolhível no mobile) -->
<nav class="w3-sidebar w3-bar-block w3-animate-left app-sidebar fatec-sidebar" id="appSidebar">
    <div class="w3-container w3-padding-16 fatec-sidebar-brand">
        <h3 class="w3-wide"><b>Achados e Perdidos</b></h3>
        <p class="w3-small w3-opacity">FATEC</p>
    </div>

    <div class="w3-container w3-padding-16 w3-border-top w3-border-bottom fatec-sidebar-user">
        <p class="w3-small w3-opacity">Logado como</p>
        <p class="w3-margin-bottom-0"><b><?= htmlspecialchars($username) ?></b></p>
        <?php if ($cargo !== ''): ?>
            <span class="w3-tag w3-small fatec-tag-cargo"><?= htmlspecialchars($cargo) ?></span>
        <?php endif; ?>
    </div>

    <div class="fatec-sidebar-nav">
        <?php foreach ($links as $link): ?>
            <a href="<?= htmlspecialchars($link['href']) ?>"
               class="w3-bar-item w3-button w3-border-bottom fatec-nav-link <?= $paginaAtiva === $link['id'] ? 'fatec-nav-link--active' : '' ?>">
                <?= htmlspecialchars($link['label']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <a href="<?= htmlspecialchars($rootPath . 'logout.php') ?>"
       class="w3-bar-item w3-button fatec-nav-link fatec-sidebar-logout">
        Sair
    </a>
</nav>

<!-- Barra superior com botão de menu (aparece apenas em telas pequenas) -->
<div class="w3-bar fatec-topbar w3-hide-large">
    <button class="w3-bar-item w3-button fatec-topbar-btn" onclick="abrirMenu()">☰</button>
    <span class="w3-bar-item"><b>Achados e Perdidos</b></span>
</div>

<!-- Sobreposição escura ao abrir o menu no mobile -->
<div class="w3-overlay w3-hide-large" onclick="fecharMenu()" id="appOverlay"></div>

<script>
    // Abre a sidebar no mobile, exibindo também a sobreposição escura
    function abrirMenu() {
        document.getElementById('appSidebar').style.display = 'block';
        document.getElementById('appOverlay').style.display = 'block';
    }
    // Fecha a sidebar no mobile
    function fecharMenu() {
        document.getElementById('appSidebar').style.display = 'none';
        document.getElementById('appOverlay').style.display = 'none';
    }
</script>
