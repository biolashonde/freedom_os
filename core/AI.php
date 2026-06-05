<?php
declare(strict_types=1);

class AI
{
    private array $config;
    private string $provider;

    public function __construct(?int $userId = null)
    {
        if ($userId && class_exists('UserAISettings')) {
            $this->config = UserAISettings::configFor($userId);
        } else {
            $config = require ROOT . '/config/config.php';
            $this->config = $config['ai'] ?? [];
        }
        $this->provider = $this->resolveProvider((string) ($this->config['provider'] ?? 'auto'));
    }

    public function configured(): bool
    {
        return $this->provider !== 'none';
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function availableProviders(): array
    {
        return [
            'anthropic' => ((string) ($this->config['key'] ?? '')) !== '',
            'openai' => ((string) ($this->config['openai_key'] ?? '')) !== '',
            'gemini' => ((string) ($this->config['gemini_key'] ?? '')) !== '',
            'openrouter' => ((string) ($this->config['openrouter_key'] ?? '')) !== '',
        ];
    }

    public function complete(string $prompt, int $maxTokens = 700): string
    {
        return match ($this->provider) {
            'anthropic' => $this->completeAnthropic($prompt, $maxTokens),
            'openai' => $this->completeOpenAI($prompt, $maxTokens),
            'gemini' => $this->completeGemini($prompt, $maxTokens),
            'openrouter' => $this->completeOpenRouter($prompt, $maxTokens),
            default => throw new RuntimeException('AI is not configured. Add your AI provider key in AI Settings.'),
        };
    }

    public function generateDevotional(array $context): array
    {
        $prompt = "Write a recovery-focused Christian devotional for FreedomOS.\n"
            . "Return only JSON with keys: title, theme, scripture_ref, scripture_text, body, prayer.\n"
            . "Requirements: warm, grace-filled, practical, non-shaming, 180-260 words for body, one short prayer.\n"
            . "Avoid clinical claims. Avoid explicit sexual detail. Keep it suitable for a private recovery app.\n\n"
            . "Context:\n"
            . "- Current streak days: " . (int) ($context['current_days'] ?? 0) . "\n"
            . "- Recent average mood out of 5: " . ($context['avg_mood'] ?? 'n/a') . "\n"
            . "- Recent average urge out of 5: " . ($context['avg_urge'] ?? 'n/a') . "\n"
            . "- Risk level: " . ($context['risk_level'] ?? 'steady') . "\n"
            . "- Recent SOS count: " . (int) ($context['recent_sos_count'] ?? 0) . "\n";

        $text = $this->complete($prompt, 900);
        $json = $this->extractJson($text);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new RuntimeException('AI response was not valid JSON.');
        }

        return [
            'title' => sanitize((string) ($data['title'] ?? 'A Steady Step')),
            'theme' => sanitize((string) ($data['theme'] ?? 'Grace')),
            'scripture_ref' => sanitize((string) ($data['scripture_ref'] ?? 'Romans 8:1')),
            'scripture_text' => sanitize((string) ($data['scripture_text'] ?? 'There is therefore now no condemnation for those who are in Christ Jesus.')),
            'body' => trim(strip_tags((string) ($data['body'] ?? 'Take the next faithful step today.'))),
            'prayer' => trim(strip_tags((string) ($data['prayer'] ?? 'Lord, help me walk in grace today.'))),
        ];
    }

    private function resolveProvider(string $requested): string
    {
        $requested = strtolower(trim($requested));
        $available = $this->availableProviders();
        if ($requested !== 'auto') {
            return ($available[$requested] ?? false) ? $requested : 'none';
        }

        foreach (['anthropic', 'openai', 'gemini', 'openrouter'] as $provider) {
            if ($available[$provider]) {
                return $provider;
            }
        }

        return 'none';
    }

    private function completeAnthropic(string $prompt, int $maxTokens): string
    {
        $payload = [
            'model' => (string) ($this->config['model'] ?? 'claude-sonnet-4-20250514'),
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $data = $this->postJson('https://api.anthropic.com/v1/messages', $payload, [
            'x-api-key: ' . (string) $this->config['key'],
            'anthropic-version: 2023-06-01',
        ]);

        return trim((string) ($data['content'][0]['text'] ?? ''));
    }

    private function completeOpenAI(string $prompt, int $maxTokens): string
    {
        $payload = [
            'model' => (string) ($this->config['openai_model'] ?? 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $maxTokens,
            'response_format' => ['type' => 'json_object'],
        ];

        $data = $this->postJson('https://api.openai.com/v1/chat/completions', $payload, [
            'Authorization: Bearer ' . (string) $this->config['openai_key'],
        ]);

        return trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    }

    private function completeGemini(string $prompt, int $maxTokens): string
    {
        $model = rawurlencode((string) ($this->config['gemini_model'] ?? 'gemini-2.5-flash'));
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
                'responseMimeType' => 'application/json',
            ],
        ];

        $data = $this->postJson(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
            $payload,
            ['x-goog-api-key: ' . (string) $this->config['gemini_key']]
        );

        return trim((string) ($data['candidates'][0]['content']['parts'][0]['text'] ?? ''));
    }

    private function completeOpenRouter(string $prompt, int $maxTokens): string
    {
        $payload = [
            'model' => (string) ($this->config['openrouter_model'] ?? 'openai/gpt-4o-mini'),
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => $maxTokens,
            'response_format' => ['type' => 'json_object'],
        ];

        $data = $this->postJson('https://openrouter.ai/api/v1/chat/completions', $payload, [
            'Authorization: Bearer ' . (string) $this->config['openrouter_key'],
            'HTTP-Referer: ' . (string) ($this->config['openrouter_site_url'] ?? ''),
            'X-Title: ' . (string) ($this->config['openrouter_app_name'] ?? 'FreedomOS'),
        ]);

        return trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    }

    private function postJson(string $url, array $payload, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error !== '') {
            throw new RuntimeException('AI request failed: ' . $error);
        }

        $data = json_decode((string) $response, true);
        if (!is_array($data)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        if ($status >= 400) {
            $message = $data['error']['message'] ?? $data['error']['status'] ?? 'AI provider returned HTTP ' . $status;
            throw new RuntimeException((string) $message);
        }

        return $data;
    }

    private function extractJson(string $text): string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end < $start) {
            return $text;
        }

        return substr($text, $start, $end - $start + 1);
    }
}
