<?php
declare(strict_types=1);

return [
    'db' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'freedomos',
        'user' => $_ENV['DB_USER'] ?? 'root',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'FreedomOS',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost/freedom_os',
        'key' => $_ENV['APP_KEY'] ?? 'change-this-32char-secret-key!!',
        'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ],
    'mail' => [
        'host' => $_ENV['MAIL_HOST'] ?? '',
        'port' => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'user' => $_ENV['MAIL_USER'] ?? '',
        'pass' => $_ENV['MAIL_PASS'] ?? '',
        'from' => $_ENV['MAIL_FROM'] ?? 'hello@freedomos.app',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'FreedomOS',
    ],
    'ai' => [
        'provider' => $_ENV['AI_PROVIDER'] ?? 'auto',
        'key' => $_ENV['ANTHROPIC_KEY'] ?? '',
        'model' => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-20250514',
        'openai_key' => $_ENV['OPENAI_API_KEY'] ?? '',
        'openai_model' => $_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini',
        'gemini_key' => $_ENV['GEMINI_API_KEY'] ?? '',
        'gemini_model' => $_ENV['GEMINI_MODEL'] ?? 'gemini-2.5-flash',
        'openrouter_key' => $_ENV['OPENROUTER_API_KEY'] ?? '',
        'openrouter_model' => $_ENV['OPENROUTER_MODEL'] ?? 'openai/gpt-4o-mini',
        'openrouter_site_url' => $_ENV['OPENROUTER_SITE_URL'] ?? ($_ENV['APP_URL'] ?? 'http://localhost/freedom_os'),
        'openrouter_app_name' => $_ENV['OPENROUTER_APP_NAME'] ?? 'FreedomOS',
    ],
];
