# achados-e-perdidos-fatec

Guia de instalação e execução do projeto de Achados e Perdidos da Fatec.

Este projeto foi feito em PHP com MySQL/MariaDB e roda no padrão do XAMPP/LAMP. Ele usa `mysqli` nas telas do sistema e um script de inicialização (`db/init.php`) para criar o banco de dados, tabelas e dados iniciais.

## Pré-requisitos

Antes de começar, você precisa de:

- PHP 8.x com as extensões `mysqli`, `curl`, `json` e `mbstring` habilitadas.
- MySQL ou MariaDB em execução.
- Um servidor Apache para abrir o projeto no navegador.
- A chave de API da NVIDIA configurada em `NVIDIA_API_KEY` para o chat com IA.

Se você estiver usando XAMPP no Linux, o caminho padrão deste projeto neste ambiente é:

```text
/opt/lampp/htdocs/trabalho/achados-e-perdidos-fatec
```

## Estrutura do projeto

Os arquivos principais ficam assim:

- `php/login.php` - tela de login.
- `php/validar.php` - autenticação do usuário.
- `php/index.php` - painel inicial após o login.
- `php/pages/perfil.php` - perfil do usuário logado.
- `php/pages/cadastro.php` - cadastro de usuários.
- `php/pages/itens.php` - gerenciamento de itens.
- `php/pages/novo_item.php` - cadastro de item encontrado.
- `php/pages/chat.php` - chat com IA para procurar itens.
- `php/components/banco.php` - funções de acesso ao banco.
- `php/components/ai.php` - integração com a API da NVIDIA.
- `db/init.php` - cria e recria o banco completo.

## Instalação do zero

### Windows / XAMPP

1. Copie a pasta `achados-e-perdidos-fatec` para dentro do diretório `htdocs` do XAMPP.

	Exemplo:

	```text
	C:\xampp\htdocs\trabalho\achados-e-perdidos-fatec
	```

2. Abra o XAMPP Control Panel e inicie `Apache` e `MySQL`.

3. Configure a variável `NVIDIA_API_KEY`.

	Você pode criar um arquivo `.env` na raiz do projeto com o conteúdo abaixo:

	```env
	NVIDIA_API_KEY=sua-chave-aqui
	```

	Se preferir, configure no Apache:

	```apache
	SetEnv NVIDIA_API_KEY "sua-chave-aqui"
	```

4. Crie o banco de dados executando `db/init.php`.

	No navegador:

	```text
	http://localhost/trabalho/achados-e-perdidos-fatec/db/init.php
	```

	Ou pelo terminal do Windows, se o PHP estiver no PATH:

	```bash
	php C:\xampp\htdocs\trabalho\achados-e-perdidos-fatec\db\init.php
	```

5. Acesse o login do sistema.

	```text
	http://localhost/trabalho/achados-e-perdidos-fatec/php/login.php
	```

### Linux / XAMPP

1. Copie a pasta `achados-e-perdidos-fatec` para dentro do diretório público do Apache.

	Exemplo neste ambiente:

	```bash
	cp -r achados-e-perdidos-fatec /opt/lampp/htdocs/trabalho/
	```

2. Inicie `Apache` e `MySQL/MariaDB`.

	Se estiver usando terminal:

	```bash
	sudo /opt/lampp/lampp start
	```

3. Configure a variável de ambiente `NVIDIA_API_KEY` ou crie um `.env` na raiz do projeto.

	Exemplo apenas na sessão atual do terminal:

	```bash
	export NVIDIA_API_KEY="sua-chave-aqui"
	```

	Exemplo de `.env`:

	```env
	NVIDIA_API_KEY=sua-chave-aqui
	```

	Se quiser deixar permanente, configure no ambiente do Apache ou no perfil do usuário que inicia o servidor.

4. Crie o banco de dados executando `db/init.php`.

	No navegador:

	```text
	http://localhost/trabalho/achados-e-perdidos-fatec/db/init.php
	```

	Ou pelo terminal:

	```bash
	php /opt/lampp/htdocs/trabalho/achados-e-perdidos-fatec/db/init.php
	```

5. Acesse o login do sistema.

	```text
	http://localhost/trabalho/achados-e-perdidos-fatec/php/login.php
	```

### Observação sobre a IA

Sem a variável `NVIDIA_API_KEY`, o restante do projeto continua abrindo normalmente, mas o chat com IA não consegue responder.

## Login inicial

O `db/init.php` cria um usuário administrador inicial com os seguintes dados:

- Usuário / e-mail: `admin@sistema.com`
- Senha: `123456`

No login, você pode informar tanto o e-mail quanto o nome do usuário, porque a validação aceita os dois campos.

## Regras de acesso

O projeto usa controle de acesso por nível na tela e no menu lateral.

Os níveis atuais são:

- `0` - Root
- `1` - Secretaria
- `2` - Aluno / Professor / Funcionário

Regras principais:

- Root acessa tudo.
- Secretaria acessa cadastro de usuários e gerenciamento de itens.
- Aluno / Professor / Funcionário acessa as telas públicas e o chat.

## Como funciona o chat com IA

O chat em `php/pages/chat.php` funciona assim:

1. O usuário descreve o item perdido.
2. A IA analisa se a mensagem é específica o suficiente.
3. Se a mensagem for vaga, a IA pede mais detalhes.
4. Se for específica, o sistema consulta o banco em busca de itens compatíveis.
5. Se houver compatibilidade, a IA responde orientando o usuário a ir até a secretaria para conferência presencial.

Esse comportamento foi feito para evitar exposição desnecessária de todos os itens cadastrados.

## Recriando o banco

Se você quiser reiniciar tudo do zero, basta executar o `db/init.php` novamente. Ele apaga e recria as tabelas, então use isso apenas quando quiser resetar o ambiente.

```bash
php /opt/lampp/htdocs/trabalho/achados-e-perdidos-fatec/db/init.php
```

## Problemas comuns

### Erro de conexão com banco

Verifique se o MySQL/MariaDB está ligado e se o projeto está usando a porta correta. Neste ambiente o código está configurado para a porta `3306`.

### Chat sem resposta da IA

Verifique se:

- `NVIDIA_API_KEY` está definida.
- O servidor tem saída para a internet.
- A extensão `curl` está habilitada no PHP.

### Login não funciona

Confira se o banco foi criado novamente com `db/init.php` e se o usuário inicial `admin@sistema.com` com senha `123456` existe.

## Observação técnica

O projeto segue o padrão procedural usado em aula, com arquivos separados por responsabilidade:

- autenticação
- proteção de páginas
- funções de banco
- integração com IA
- telas principais

Isso facilita evoluir o sistema sem misturar lógica de acesso, apresentação e consulta ao banco.
