<?php

require_once __DIR__ . '/env.php';

carregar_variaveis_env(__DIR__ . '/../../.env');

function nvidia_chat_completion(array $messages, int $maxTokens = 1024, float $temperature = 0.2): array
{
    $apiKey = env('NVIDIA_API_KEY');
    if (!$apiKey) {
        return [
            'ok' => false,
            'error' => 'NVIDIA_API_KEY não configurada no ambiente.',
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'error' => 'Extensão cURL indisponível no PHP.',
        ];
    }

    $payload = [
        'model' => 'openai/gpt-oss-120b',
        'messages' => $messages,
        'temperature' => $temperature,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
        'max_tokens' => $maxTokens,
        'stream' => false,
        'reasoning_effort' => 'medium',
    ];

    $ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT => 60,
    ]);

    $raw = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return [
            'ok' => false,
            'error' => 'Falha no cURL: ' . $curlError,
        ];
    }

    $decoded = json_decode($raw, true);
    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            'ok' => false,
            'error' => $decoded['error']['message'] ?? 'Erro HTTP ' . $httpCode,
            'raw' => $raw,
        ];
    }

    $content = $decoded['choices'][0]['message']['content'] ?? '';
    return [
        'ok' => true,
        'content' => $content,
        'raw' => $decoded,
    ];
}

function ai_analisar_solicitacao_chat(string $mensagem): array
{
    $promptSistema = <<<PROMPT
Você é a IA do chat do sistema de achados e perdidos da Fatec.
Você funcionará como alguém da secretaria que receberá uma mensagem de alguém procurando por um item e procurará por ele caso a mensagem seja específica o suficiente.
Você deve analisar a mensagem do usuário e decidir se ela é específica o suficiente para consultar o banco de dados com segurança.

Regras:
- Considere específica apenas se houver pelo menos dois sinais concretos, como cor, marca, material, tipo de objeto, local, tamanho, acessório ou detalhe visual.
- Se a mensagem for vaga, genérica ou puder servir para muitos itens, marque como não específica.
- Não invente dados.
- Retorne APENAS JSON válido, sem markdown, sem texto extra.

Formato exato do JSON:
{
  "specific": true/false,
  "reason": "texto curto",
  "search_terms": ["termo1", "termo2"],
  "follow_up": "texto curto para pedir mais detalhes"
}
PROMPT;

    $resultado = nvidia_chat_completion([
        ['role' => 'system', 'content' => $promptSistema],
        ['role' => 'user', 'content' => $mensagem],
    ], 700, 0.2);

    if (!$resultado['ok']) {
        return $resultado;
    }

    $conteudo = trim((string) $resultado['content']);
    $json = json_decode($conteudo, true);

    if (!is_array($json)) {
        if (preg_match('/\{.*\}/s', $conteudo, $matches)) {
            $json = json_decode($matches[0], true);
        }
    }

    if (!is_array($json)) {
        return [
            'ok' => false,
            'error' => 'Não foi possível interpretar a resposta da IA.',
        ];
    }

    return [
        'ok' => true,
        'specific' => (bool) ($json['specific'] ?? false),
        'reason' => trim((string) ($json['reason'] ?? '')),
        'search_terms' => array_values(array_filter(array_map('trim', (array) ($json['search_terms'] ?? [])))),
        'follow_up' => trim((string) ($json['follow_up'] ?? '')),
    ];
}

function ai_responder_chat(array $contexto): array
{
    $promptSistema = <<<PROMPT
Você é a IA do chat de achados e perdidos da Fatec.
Seu objetivo é responder de forma curta, educada e segura.

Regras:
- Se houver itens compatíveis no contexto, diga que existe um item compatível e oriente o usuário a ir até a secretaria para confirmar.
- Não revele a lista completa do banco.
- Não diga IDs internos, SQL ou detalhes sensíveis.
- Se a mensagem for vaga, peça mais detalhes objetivos.
- Se houver retorno vazio, diga que não encontrou item compatível no momento.
- Responda em português do Brasil, de forma breve.
PROMPT;

    return nvidia_chat_completion([
        ['role' => 'system', 'content' => $promptSistema],
        ['role' => 'user', 'content' => json_encode($contexto, JSON_UNESCAPED_UNICODE)],
    ], 700, 0.4);
}