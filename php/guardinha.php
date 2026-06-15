<?php
    // Inicia (ou retoma) a sessão para controlar o usuário logado
    session_start();

    /*
     * restricao() — Controle de acesso das páginas.
     *
     * Cada usuário possui um nível de acesso:
     *   0 = Root            (acesso total)
     *   1 = Direção
     *   2 = Secretaria
     *   3 = Aluno / Professor
     *
     * Quanto MENOR o número, MAIOR o acesso.
     *
     * $nivelMinimo = nível máximo (número) permitido para abrir a página.
     * Ex.: restricao(2) libera os níveis 0, 1 e 2 e bloqueia o nível 3.
     *
     * Observação: a página deve definir $rootPath ANTES de chamar restricao(),
     * pois o caminho até a raiz do projeto é usado nos redirecionamentos.
     */
    function restricao(int $nivelMinimo){
        // Caminho até a raiz do projeto (ex.: '' na raiz, '../' dentro de pages/)
        $rootPath = $GLOBALS['rootPath'] ?? '';

        // Verifica se o usuário está autenticado; se não, volta para o login
        if(!isset($_SESSION['nivel'])){
            header("location: " . $rootPath . "login.php");
            exit; // Interrompe a execução para impedir que a página continue carregando
        }

        // Verifica se o usuário possui permissão suficiente para acessar a página
        if((int) $_SESSION['nivel'] > $nivelMinimo){
            // Sem permissão: redireciona para o painel (acessível a todos os níveis)
            header("location: " . $rootPath . "index.php");
            exit;
        }
    }

    /*
     * nivel_label() — Converte o número do nível no nome do cargo.
     * Usado na sidebar, no perfil e nas listagens de usuários.
     */
    function nivel_label($nivel){
        $niveis = [
            0 => 'Root',
            1 => 'Direção',
            2 => 'Secretaria',
            3 => 'Aluno / Professor',
        ];
        return $niveis[(int) $nivel] ?? 'Desconhecido';
    }
?>
