<?php

if (!function_exists('categorias_mock')) {
    // Lista de categorias usada nos selects de Novo Item e Procurar Item
    function categorias_mock(): array {
        return [
            'Eletrônicos',
            'Documentos',
            'Vestuário',
            'Acessórios',
            'Material Escolar',
            'Chaves',
            'Outros',
        ];
    }
}

if (!function_exists('status_itens_mock')) {
    // Status possíveis de um item
    function status_itens_mock(): array {
        return ['Disponível', 'Reservado', 'Entregue'];
    }
}

if (!function_exists('itens_mock')) {
    // Itens encontrados cadastrados (dados de exemplo)
    function itens_mock(): array {
        return [
            [
                'id' => 1,
                'nome' => 'Carteira de couro preta',
                'categoria' => 'Acessórios',
                'descricao' => 'Carteira masculina com documentos dentro.',
                'local_encontrado' => 'Bloco B — Sala 12',
                'data_encontrado' => '2026-06-01',
                'foto' => '',
                'status' => 'Disponível',
                'usuario_cadastro' => 'João da Secretaria',
                'usuario_retirada' => null,
                'data_retirada' => null,
            ],
            [
                'id' => 2,
                'nome' => 'Garrafa térmica azul',
                'categoria' => 'Outros',
                'descricao' => 'Garrafa de inox de 500ml, cor azul.',
                'local_encontrado' => 'Cantina',
                'data_encontrado' => '2026-06-03',
                'foto' => '',
                'status' => 'Disponível',
                'usuario_cadastro' => 'Maria da Direção',
                'usuario_retirada' => null,
                'data_retirada' => null,
            ],
            [
                'id' => 3,
                'nome' => 'Óculos de grau',
                'categoria' => 'Acessórios',
                'descricao' => 'Armação preta com estojo marrom.',
                'local_encontrado' => 'Biblioteca',
                'data_encontrado' => '2026-05-28',
                'foto' => '',
                'status' => 'Reservado',
                'usuario_cadastro' => 'João da Secretaria',
                'usuario_retirada' => null,
                'data_retirada' => null,
            ],
            [
                'id' => 4,
                'nome' => 'Carregador de notebook',
                'categoria' => 'Eletrônicos',
                'descricao' => 'Fonte Dell 65W, cabo enrolado com elástico.',
                'local_encontrado' => 'Laboratório 3',
                'data_encontrado' => '2026-05-20',
                'foto' => '',
                'status' => 'Entregue',
                'usuario_cadastro' => 'Maria da Direção',
                'usuario_retirada' => 'Carlos Aluno',
                'data_retirada' => '2026-05-25',
            ],
            [
                'id' => 5,
                'nome' => 'Caderno universitário',
                'categoria' => 'Material Escolar',
                'descricao' => 'Caderno de 10 matérias, capa vermelha.',
                'local_encontrado' => 'Bloco A — Sala 5',
                'data_encontrado' => '2026-06-10',
                'foto' => '',
                'status' => 'Disponível',
                'usuario_cadastro' => 'João da Secretaria',
                'usuario_retirada' => null,
                'data_retirada' => null,
            ],
            [
                'id' => 6,
                'nome' => 'Molho de chaves',
                'categoria' => 'Chaves',
                'descricao' => 'Três chaves em um chaveiro da Fatec.',
                'local_encontrado' => 'Estacionamento',
                'data_encontrado' => '2026-06-12',
                'foto' => '',
                'status' => 'Disponível',
                'usuario_cadastro' => 'Maria da Direção',
                'usuario_retirada' => null,
                'data_retirada' => null,
            ],
        ];
    }
}

if (!function_exists('usuarios_mock')) {
    // Usuários cadastrados (dados de exemplo) — senha NÃO é exibida nas telas
    function usuarios_mock(): array {
        return [
            ['id' => 1, 'nome' => 'Administrador do Sistema', 'email' => 'root@fatec.sp.gov.br',   'telefone' => '(11) 90000-0000', 'usuario' => 'root',  'nivel' => 0, 'ativo' => 1, 'criado_em' => '2025-01-10'],
            ['id' => 2, 'nome' => 'Maria da Direção',         'email' => 'maria@fatec.sp.gov.br',  'telefone' => '(11) 91111-1111', 'usuario' => 'maria', 'nivel' => 1, 'ativo' => 1, 'criado_em' => '2025-02-15'],
            ['id' => 3, 'nome' => 'João da Secretaria',       'email' => 'joao@fatec.sp.gov.br',   'telefone' => '(11) 92222-2222', 'usuario' => 'joao',  'nivel' => 2, 'ativo' => 1, 'criado_em' => '2025-03-20'],
            ['id' => 4, 'nome' => 'Carlos Aluno',             'email' => 'carlos@fatec.sp.gov.br', 'telefone' => '(11) 93333-3333', 'usuario' => 'carlos','nivel' => 3, 'ativo' => 1, 'criado_em' => '2025-08-01'],
            ['id' => 5, 'nome' => 'Ana Professora',           'email' => 'ana@fatec.sp.gov.br',    'telefone' => '(11) 94444-4444', 'usuario' => 'ana',   'nivel' => 3, 'ativo' => 0, 'criado_em' => '2025-09-12'],
        ];
    }
}

if (!function_exists('classe_status')) {
    // Devolve a classe CSS da etiqueta de cor de acordo com o status do item
    function classe_status(string $status): string {
        $classes = [
            'Disponível' => 'fatec-status-disponivel',
            'Reservado'  => 'fatec-status-reservado',
            'Entregue'   => 'fatec-status-entregue',
        ];
        return $classes[$status] ?? '';
    }
}

if (!function_exists('contar_itens_por_status')) {
    // Conta quantos itens estão em um determinado status
    function contar_itens_por_status(array $itens, string $status): int {
        $total = 0;
        foreach ($itens as $item) {
            if ($item['status'] === $status) {
                $total++;
            }
        }
        return $total;
    }
}
?>
