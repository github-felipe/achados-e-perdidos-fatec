<?php

$host = '127.0.0.1';
$dbname = 'sistema_itens';
$user = 'root';
$pass = '';
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE $dbname");

    require_once __DIR__ . '/../php/components/hash.php';

    $sqlTabelas = <<<'SQL'
    DROP PROCEDURE IF EXISTS sp_registrar_retirada_item;
    DROP TABLE IF EXISTS mensagens;
    DROP TABLE IF EXISTS itens;
    DROP TABLE IF EXISTS imagens;
    DROP TABLE IF EXISTS locais;
    DROP TABLE IF EXISTS categorias;
    DROP TABLE IF EXISTS users;
    DROP TABLE IF EXISTS niveis;

    CREATE TABLE niveis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nivel INT NOT NULL,
        descricao VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        nivel_id INT,
        status VARCHAR(20) DEFAULT 'ativo',
        CONSTRAINT fk_usuario_nivel FOREIGN KEY (nivel_id) REFERENCES niveis(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    CREATE TABLE categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descricao VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE locais (
        id INT AUTO_INCREMENT PRIMARY KEY,
        local VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE imagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_arquivo VARCHAR(255) NOT NULL,
        tipo_mime VARCHAR(100) NOT NULL,
        imagem LONGBLOB NOT NULL,
        tamanho INT UNSIGNED NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        descricao VARCHAR(255) NOT NULL,
        categoria_id INT,
        foto_id INT,
        local_id INT,
        data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
        cadastrou_id INT,
        data_recebimento DATETIME,
        recebeu_id INT,
        data_retirada DATETIME,
        retirou_id INT,
        nome_retirou VARCHAR(100),

        CONSTRAINT fk_item_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
        CONSTRAINT fk_item_foto FOREIGN KEY (foto_id) REFERENCES imagens(id) ON DELETE SET NULL,
        CONSTRAINT fk_item_local FOREIGN KEY (local_id) REFERENCES locais(id) ON DELETE SET NULL,
        CONSTRAINT fk_item_cadastrou FOREIGN KEY (cadastrou_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_item_recebeu FOREIGN KEY (recebeu_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_item_retirou FOREIGN KEY (retirou_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

    CREATE TABLE mensagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role VARCHAR(20) NOT NULL DEFAULT 'user',
        mensagem TEXT NOT NULL,
        data DATETIME DEFAULT CURRENT_TIMESTAMP,
        id_usuario INT,
        CONSTRAINT fk_mensagem_usuario FOREIGN KEY (id_usuario) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    INSERT INTO niveis (nivel, descricao) VALUES
        (0, 'Root'),
        (1, 'Secretaria'),
        (2, 'Aluno'),
        (2, 'Professor'),
        (2, 'Funcionário');

    INSERT INTO categorias (descricao) VALUES
        ('Eletrônicos'),
        ('Documentos e Carteiras'),
        ('Vestuário'),
        ('Calçados'),
        ('Material Acadêmico/Escolar'),
        ('Bolsas e Mochilas'),
        ('Acessórios'),
        ('Chaves'),
        ('Óculos'),
        ('Esportes e Lazer'),
        ('Instrumentos Musicais'),
        ('Outros');
    INSERT INTO locais (local) VALUES
        ('Sala I'),
        ('Sala II'),
        ('Sala III'),
        ('Sala IV'),
        ('Sala V'),
        ('Sala VI'),
        ('Sala VII'),
        ('Sala VIII'),
        ('Sala IX'),
        ('Sala X'),
        ('Sala XI'),
        ('Sala XII'),
        ('Sala XIII'),
        ('Sala XIV'),
        ('Sala XV'),
        ('Sala XVI'),
        ('Sala XVII'),
        ('Sala XVIII'),
        ('Sala XIX'),
        ('Sala XX'),
        ('DA'),
        ('Coordenação'),
        ('Auditório 1'),
        ('Auditório 2'),
        ('Auditório 3'),
        ('Lab 1'),
        ('Lab 2'),
        ('Lab 3'),
        ('Lab 4'),
        ('Lab 5'),
        ('Lab 6'),
        ('Biblioteca'),
        ('Sala de estudos'),
        ('Cantina'),
        ('Corredor 1'),
        ('Corredor 2'),
        ('Corredor 3'),
        ('Banheiro 1º andar M'),
        ('Banheiro 1º andar F'),
        ('Banheiro 1º andar D'),
        ('Banheiro 2º andar M'),
        ('Banheiro 2º andar F'),
        ('Banheiro 2º andar D'),
        ('Banheiro 3º andar M'),
        ('Banheiro 3º andar F'),
        ('Banheiro 3º andar D'),
        ('Área externa'),
        ('Estacionamento'),
        ('Escadas'),
        ('Escadas de emergência'),
        ('Elevador')
        ('Guarita de Segurança');
    SQL;

    $pdo->exec($sqlTabelas);

    // Gerar hashes de senha para usuários padrão
    $senhaHash = hashsenha('senha123');

    // Adicionar usuários padrão
    $sqlUsuarios = <<<SQL
    INSERT INTO users (nome, email, senha, nivel_id, status) VALUES
        ('Admin', 'admin@sistema.com', '{$senhaHash}', 1, 'ativo'),
        ('Secretaria', 'f288acad@cps.sp.gov.br', '{$senhaHash}', 2, 'ativo'),
        ('Leonardo Ferrucci', 'leonardo.ferrucci@cps.sp.gov.br', '{$senhaHash}', 4, 'ativo'),
        ('Felipe Lima', 'felipe.lima3@aluno.cps.sp.gov.br', '{$senhaHash}', 3, 'ativo'),
        ('Funcionário Fatec', 'funcionario.fatec@gmail.com', '{$senhaHash}', 5, 'ativo');
    SQL;

    $pdo->exec($sqlUsuarios);

    // Itens de teste para demonstração do sistema
    $sqlItens = <<<'SQL'
    INSERT INTO itens (descricao, categoria_id, local_id, data_cadastro, cadastrou_id) VALUES
        ('Carregador de notebook Dell 65W — cabo enrolado com elástico', 1, 28, '2026-06-10 08:30:00', 2),
        ('Carteira de couro preta — com documentos e cartões dentro', 2, 32, '2026-06-11 10:15:00', 2),
        ('Garrafa térmica azul Stanley 500ml', 11, 34, '2026-06-11 13:45:00', 5),
        ('Óculos de grau — armação preta fina com estojo marrom', 9, 1, '2026-06-12 09:00:00', 2),
        ('Caderno universitário 10 matérias — capa vermelha com foto do gabriel medina nas olimpíadas na frente', 5, 5, '2026-06-12 14:20:00', 4),
        ('Molho de chaves — três chaves e um chaveiro da Fatec', 8, 48, '2026-06-13 07:50:00', 5),
        ('Guarda-chuva preto dobrável — cabo de madeira', 10, 35, '2026-06-13 16:30:00', 2),
        ('Fone de ouvido Bluetooth branco — sem case', 1, 21, '2026-06-14 11:00:00', 3),
        ('Mochila preta com zíper vermelho — tag com nome Leonardo', 6, 26, '2026-06-14 15:10:00', 2),
        ('Capa de chuva amarela tamanho M — dobrada em embalagem original', 10, 49, '2026-06-15 08:45:00', 5);
    SQL;

    $pdo->exec($sqlItens);

    // Registrar a devolução do item 9 (mochila) como exemplo de item já entregue
    $pdo->exec("UPDATE itens SET data_retirada = '2026-06-15 10:00:00', retirou_id = 2, nome_retirou = 'Leonardo Ferrucci' WHERE id = 9");

    $sqlProcedure = <<<'SQL'
    CREATE PROCEDURE sp_registrar_retirada_item(
        IN p_item_id INT,
        IN p_usuario_id INT
    )
    BEGIN
        DECLARE v_ja_retirado INT;

        SELECT COUNT(*) INTO v_ja_retirado FROM itens WHERE id = p_item_id AND data_retirada IS NOT NULL;

        IF v_ja_retirado > 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erro: Este item já foi retirado anteriormente.';
        ELSE
            UPDATE itens SET data_retirada = CURRENT_TIMESTAMP, retirou_id = p_usuario_id WHERE id = p_item_id;
        END IF;
    END
    SQL;

    $pdo->exec($sqlProcedure);

    echo "<h1>Tudo pronto!</h1>";
    echo "<p>Banco, tabelas, dados iniciais e procedure criados com sucesso.</p>";

} catch (PDOException $e) {
    echo "<h1>Erro no banco de dados</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}

?>