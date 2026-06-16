<?php
    require_once("../guardinha.php");

    $rootPath = '../';
    $paginaAtiva = 'chat';

    // Chat disponível para todos os níveis logados (0 a 2)
    restricao(2);

    require_once("../components/banco.php");
    require_once("../components/ai.php");

    $con = conectar_banco();
    $erroIa = '';
    $respostaIa = '';
    $mensagemUsuario = trim($_POST['mensagem'] ?? '');

    if (!isset($_SESSION['chat_ia'])) {
        $_SESSION['chat_ia'] = [];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mensagemUsuario !== '') {
        $analise = ai_analisar_solicitacao_chat($mensagemUsuario);

        if (!$analise['ok']) {
            $erroIa = $analise['error'] ?? 'Não foi possível consultar a IA no momento.';
            $respostaIa = 'O serviço de IA não está disponível no momento. Tente novamente mais tarde.';
        } elseif (empty($analise['specific'])) {
            $respostaIa = $analise['follow_up'] !== ''
                ? $analise['follow_up']
                : 'Preciso de mais detalhes concretos para procurar com segurança. Informe cor, marca, tipo ou outro detalhe.';
        } else {
            $termos = $analise['search_terms'];
            if (empty($termos)) {
                $termos = normalizar_termos_busca($mensagemUsuario);
            }

            $resultados = buscar_itens_disponiveis_por_termos($con, $termos, 5);

            $contextoResposta = [
                'specific' => true,
                'query' => $mensagemUsuario,
                'reason' => $analise['reason'] ?? '',
                'match_count' => count($resultados),
                'matches' => $resultados,
                'instruction' => 'Responda com foco em orientar o usuário a procurar a secretaria. Não liste todo o banco.',
            ];

            $respostaFinal = ai_responder_chat($contextoResposta);
            if ($respostaFinal['ok']) {
                $respostaIa = trim((string) ($respostaFinal['content'] ?? ''));
            } else {
                if (empty($resultados)) {
                    $respostaIa = 'Não encontrei um item compatível no banco no momento. Se puder, envie mais detalhes e vá à secretaria para confirmar presencialmente.';
                } else {
                    $respostaIa = 'Encontrei um possível item compatível no sistema. Vá até a secretaria para verificar presencialmente.';
                }
            }
        }

        $_SESSION['chat_ia'][] = ['role' => 'user', 'content' => $mensagemUsuario];
        $_SESSION['chat_ia'][] = ['role' => 'assistant', 'content' => $respostaIa];
        $_SESSION['chat_ia'] = array_slice($_SESSION['chat_ia'], -12);
    }

    $historico = $_SESSION['chat_ia'];
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
            <div class="w3-container w3-padding-16 app-card w3-margin-bottom">
                <h4><b>Chat com IA</b></h4>
                <p class="w3-text-grey">Descreva o item com o máximo de detalhes possível. A IA só consulta o banco se a solicitação for específica o suficiente.</p>

                <form action="" method="post">
                    <label class="app-label"><b>Mensagem</b></label>
                    <textarea class="w3-input w3-border w3-margin-bottom app-input" name="mensagem" rows="4" placeholder="Ex.: carteira preta de couro com documento da Fatec..."><?= htmlspecialchars($mensagemUsuario) ?></textarea>
                    <button class="w3-button app-btn-primary" type="submit">Enviar</button>
                </form>

                <?php if ($erroIa !== ''): ?>
                    <p class="w3-small w3-text-red w3-margin-top"><?= htmlspecialchars($erroIa) ?></p>
                <?php endif; ?>
            </div>

            <div class="w3-container w3-padding-16 app-card">
                <h4><b>Conversa</b></h4>

                <?php if (empty($historico)): ?>
                    <p class="w3-text-grey">Ainda não há mensagens.</p>
                <?php else: ?>
                    <?php foreach ($historico as $itemChat): ?>
                        <div class="w3-margin-bottom">
                            <?php if ($itemChat['role'] === 'user'): ?>
                                <div class="w3-panel w3-rightbar w3-light-grey w3-padding-small">
                                    <p class="w3-small w3-text-grey w3-margin-bottom-0"><b>Você</b></p>
                                    <p class="w3-margin-bottom-0"><?= htmlspecialchars($itemChat['content']) ?></p>
                                </div>
                            <?php else: ?>
                                <div class="w3-panel w3-leftbar w3-pale-green w3-padding-small">
                                    <p class="w3-small w3-text-grey w3-margin-bottom-0"><b>IA</b></p>
                                    <p class="w3-margin-bottom-0"><?= htmlspecialchars($itemChat['content']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <?php require_once("../components/footer.php"); ?>
    </div>
</body>
</html>
