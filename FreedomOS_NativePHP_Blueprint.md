# FreedomOS — Native PHP 8.3 Blueprint
### Zero frameworks · Pure PHP · Full ownership
### PHP 8.3 · MySQL 8 · Nginx · PHPMailer · cURL

---

## 1. Philosophy

No Laravel, no Symfony, no Composer framework. Every file you open, you understand.
The only allowed external code:
- **PHPMailer** (1 library, SMTP email — include directly)
- **HTMX** (CDN link in HTML, zero install)
- **Tailwind CSS** (CDN link in HTML, zero install)

Everything else — routing, auth, database, caching, sessions — you write yourself.
This keeps the codebase lean, fast, and fully yours.

---

## 2. Folder Structure

```
freedomos/
├── public/
│   ├── index.php            ← ONLY entry point (Nginx rewrites here)
│   ├── manifest.json        ← PWA manifest
│   ├── sw.js                ← Service Worker (offline SOS)
│   └── assets/
│       ├── css/style.css
│       └── js/app.js
│
├── core/                    ← Your mini-framework (pure PHP)
│   ├── Router.php
│   ├── Request.php
│   ├── Response.php
│   ├── Database.php         ← PDO singleton
│   ├── Auth.php             ← Session-based auth
│   ├── Csrf.php
│   ├── Mailer.php           ← PHPMailer wrapper
│   ├── AI.php               ← Anthropic API via cURL
│   ├── Scripture.php        ← ESV API via cURL
│   ├── Cache.php            ← File-based cache
│   └── helpers.php          ← view(), redirect(), dd(), env()
│
├── controllers/
│   ├── AuthController.php
│   ├── SobrietyController.php
│   ├── SOSController.php
│   ├── AccountabilityController.php
│   ├── DevotionalController.php
│   ├── PurposeController.php
│   └── BlockerController.php
│
├── models/
│   ├── User.php
│   ├── CheckIn.php
│   ├── Streak.php
│   ├── SOSEvent.php
│   ├── Partner.php
│   ├── Devotional.php
│   ├── Goal.php
│   ├── Testimony.php
│   └── BlockerLog.php
│
├── views/
│   ├── layout.php           ← master template
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── sobriety/
│   │   ├── dashboard.php
│   │   └── checkin.php
│   ├── sos/
│   │   └── index.php        ← must be fastest page in app
│   ├── accountability/
│   │   ├── index.php
│   │   └── partner.php
│   ├── devotional/
│   │   ├── today.php
│   │   └── archive.php
│   └── purpose/
│       ├── index.php
│       ├── goals.php
│       └── testimony.php
│
├── cron/                    ← PHP-CLI scripts run by crontab
│   ├── checkin_reminder.php
│   ├── generate_devotional.php
│   ├── weekly_digest.php
│   └── update_blocklist.php
│
├── freedomguard/            ← Blocker sidecar
│   ├── daemon.php
│   ├── api.php
│   ├── rules/
│   │   ├── domains.txt
│   │   └── keywords.txt
│   └── extension/           ← Chrome/Firefox extension source
│       ├── manifest.json
│       ├── background.js
│       └── blocked.html
│
├── storage/
│   ├── cache/               ← File cache (JSON files)
│   ├── logs/                ← app.log, error.log
│   └── uploads/             ← profile photos, testimony images
│
├── config/
│   └── config.php           ← DB creds, API keys, app settings
│
└── .htaccess                ← Apache fallback (optional)
```

---

## 3. Core Files

### config/config.php
```php
<?php
return [
    'db' => [
        'host'    => $_ENV['DB_HOST']     ?? 'localhost',
        'name'    => $_ENV['DB_NAME']     ?? 'freedomos',
        'user'    => $_ENV['DB_USER']     ?? 'root',
        'pass'    => $_ENV['DB_PASS']     ?? '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'    => 'FreedomOS',
        'url'     => $_ENV['APP_URL']     ?? 'http://localhost',
        'key'     => $_ENV['APP_KEY']     ?? 'change-this-32char-secret-key!!',
        'debug'   => $_ENV['APP_DEBUG']   ?? false,
    ],
    'mail' => [
        'host'    => $_ENV['MAIL_HOST']   ?? 'smtp.mailgun.org',
        'port'    => $_ENV['MAIL_PORT']   ?? 587,
        'user'    => $_ENV['MAIL_USER']   ?? '',
        'pass'    => $_ENV['MAIL_PASS']   ?? '',
        'from'    => $_ENV['MAIL_FROM']   ?? 'hello@freedomos.app',
    ],
    'ai' => [
        'key'     => $_ENV['ANTHROPIC_KEY'] ?? '',
        'model'   => 'claude-sonnet-4-20250514',
    ],
    'esv' => [
        'key'     => $_ENV['ESV_KEY']     ?? '',
    ],
    'twilio' => [
        'sid'     => $_ENV['TWILIO_SID']  ?? '',
        'token'   => $_ENV['TWILIO_TOKEN'] ?? '',
        'from'    => $_ENV['TWILIO_FROM'] ?? '',
    ],
];
```

---

### public/index.php
```php
<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

// Load env (simple key=value .env parser)
if (file_exists(ROOT . '/.env')) {
    foreach (file(ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!str_starts_with(trim($line), '#') && str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $_ENV[trim($k)] = trim($v);
        }
    }
}

// Autoload core + controllers + models
spl_autoload_register(function (string $class): void {
    $dirs = [ROOT . '/core/', ROOT . '/controllers/', ROOT . '/models/'];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

require_once ROOT . '/core/helpers.php';

session_start();

// Route
$router = new Router();
require_once ROOT . '/routes.php';
$router->dispatch();
```

---

### core/Router.php
```php
<?php
class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->routes[] = ['GET', $path, $handler, $middleware];
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->routes[] = ['POST', $path, $handler, $middleware];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = strtok($_SERVER['REQUEST_URI'], '?');

        foreach ($this->routes as [$routeMethod, $path, $handler, $middleware]) {
            $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';

            if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
                // Run middleware
                foreach ($middleware as $mw) {
                    (new $mw())->handle();
                }
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                // Call controller or closure
                if (is_callable($handler)) {
                    call_user_func($handler, ...array_values($params));
                } else {
                    [$class, $method] = $handler;
                    (new $class())->$method(...array_values($params));
                }
                return;
            }
        }
        http_response_code(404);
        require ROOT . '/views/404.php';
    }
}
```

---

### core/Database.php
```php
<?php
class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $cfg = require ROOT . '/config/config.php';
            $c   = $cfg['db'];
            $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";
            self::$instance = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function row(string $sql, array $params = []): ?array
    {
        return self::query($sql, $params)->fetch() ?: null;
    }

    public static function rows(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): string
    {
        $cols = implode(',', array_keys($data));
        $vals = implode(',', array_fill(0, count($data), '?'));
        self::query("INSERT INTO {$table} ({$cols}) VALUES ({$vals})", array_values($data));
        return self::get()->lastInsertId();
    }
}
```

---

### core/Auth.php
```php
<?php
class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $user = Database::row('SELECT * FROM users WHERE email = ?', [$email]);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user']    = $user;
            session_regenerate_id(true);
            return true;
        }
        return false;
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public static function register(array $data): string
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $id = Database::insert('users', $data);
        // Seed streak row
        Database::insert('streaks', ['user_id' => $id, 'current_days' => 0, 'longest_days' => 0]);
        return $id;
    }
}
```

---

### core/helpers.php
```php
<?php
function view(string $template, array $data = []): void
{
    extract($data);
    require ROOT . '/views/layout.php';
}

function partial(string $template, array $data = []): void
{
    extract($data);
    require ROOT . '/views/' . $template . '.php';
}

function redirect(string $url): never
{
    header("Location: {$url}");
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('CSRF token mismatch.');
        }
    }
}

function json(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function sanitize(string $input): string
{
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
```

---

### core/AI.php  (Anthropic API — pure cURL)
```php
<?php
class AI
{
    private string $key;
    private string $model;

    public function __construct()
    {
        $cfg         = require ROOT . '/config/config.php';
        $this->key   = $cfg['ai']['key'];
        $this->model = $cfg['ai']['model'];
    }

    public function complete(string $prompt, int $maxTokens = 500): string
    {
        $payload = json_encode([
            'model'      => $this->model,
            'max_tokens' => $maxTokens,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'x-api-key: ' . $this->key,
                'anthropic-version: 2023-06-01',
            ],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) throw new RuntimeException("AI cURL error: {$err}");

        $data = json_decode($response, true);
        return $data['content'][0]['text'] ?? 'Could not generate response.';
    }

    public function generateDevotional(int $streak, float $avgMood, bool $recentTrigger): string
    {
        $context = $recentTrigger ? 'They faced a temptation trigger recently.' : 'They are doing well today.';
        $prompt  = "You are a compassionate Christian devotional writer helping people recover from porn addiction.
Write a 200-word daily devotional. Person has been clean for {$streak} days. Mood avg: {$avgMood}/5. {$context}
Include: (1) an identity truth about who they are in Christ, (2) one ESV scripture, (3) a short prayer.
Tone: warm, grace-filled, non-condemning, hopeful. No bullet points. Pure prose.";

        return $this->complete($prompt, 500);
    }
}
```

---

### core/Cache.php  (File-based — no Redis required)
```php
<?php
class Cache
{
    private static string $dir = ROOT . '/storage/cache/';

    public static function get(string $key): mixed
    {
        $file = self::$dir . md5($key) . '.json';
        if (!file_exists($file)) return null;

        $data = json_decode(file_get_contents($file), true);
        if ($data['expires'] && time() > $data['expires']) {
            unlink($file);
            return null;
        }
        return $data['value'];
    }

    public static function set(string $key, mixed $value, int $ttlSeconds = 3600): void
    {
        if (!is_dir(self::$dir)) mkdir(self::$dir, 0755, true);
        file_put_contents(
            self::$dir . md5($key) . '.json',
            json_encode(['value' => $value, 'expires' => time() + $ttlSeconds])
        );
    }

    public static function remember(string $key, int $ttl, callable $fn): mixed
    {
        $cached = self::get($key);
        if ($cached !== null) return $cached;
        $value = $fn();
        self::set($key, $value, $ttl);
        return $value;
    }
}
```

---

## 4. Routes (routes.php)

```php
<?php
// ── Auth
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/logout',   [AuthController::class, 'logout']);

// ── Sobriety (requires auth)
$router->get('/dashboard',      [SobrietyController::class, 'dashboard'],      [AuthMiddleware::class]);
$router->post('/checkin',       [SobrietyController::class, 'checkin'],        [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/relapse',       [SobrietyController::class, 'logRelapse'],     [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/progress',       [SobrietyController::class, 'progress'],       [AuthMiddleware::class]);

// ── SOS (fast — minimal middleware)
$router->get('/sos',            [SOSController::class, 'show'],                [AuthMiddleware::class]);
$router->post('/sos/trigger',   [SOSController::class, 'trigger'],             [AuthMiddleware::class]);
$router->post('/sos/resolve',   [SOSController::class, 'resolve'],             [AuthMiddleware::class]);

// ── Accountability
$router->get('/accountability',         [AccountabilityController::class, 'index'],         [AuthMiddleware::class]);
$router->post('/accountability/invite', [AccountabilityController::class, 'invite'],        [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/accountability/accept/{token}', [AccountabilityController::class, 'accept'],[AuthMiddleware::class]);

// ── Devotional
$router->get('/devotional',         [DevotionalController::class, 'today'],   [AuthMiddleware::class]);
$router->get('/devotional/{day}',   [DevotionalController::class, 'show'],    [AuthMiddleware::class]);
$router->post('/devotional/ai',     [DevotionalController::class, 'generate'],[AuthMiddleware::class]);

// ── Purpose
$router->get('/purpose',            [PurposeController::class, 'index'],      [AuthMiddleware::class]);
$router->post('/purpose/goals',     [PurposeController::class, 'storeGoal'],  [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/purpose/testimony',  [PurposeController::class, 'testimony'],  [AuthMiddleware::class]);
$router->post('/purpose/testimony', [PurposeController::class, 'saveTestimony'],[AuthMiddleware::class, CsrfMiddleware::class]);

// ── Blocker API (called by browser extension)
$router->post('/blocker/check',     [BlockerController::class, 'check']);     // no auth — device token
$router->post('/blocker/override',  [BlockerController::class, 'requestOverride'], [AuthMiddleware::class]);
$router->get('/blocked',            [BlockerController::class, 'blockedPage']);
```

---

## 5. Key Controllers

### controllers/SOSController.php
```php
<?php
class SOSController
{
    public function show(): void
    {
        // Pre-load scripture from cache — this page must be INSTANT
        $scripture = Cache::remember('sos_scripture_' . Auth::id(), 300, function () {
            return Database::row(
                'SELECT * FROM scriptures WHERE category = "sos" ORDER BY RAND() LIMIT 1'
            );
        });
        view('sos/index', ['scripture' => $scripture]);
    }

    public function trigger(): void
    {
        csrf_check();
        $userId = Auth::id();
        $note   = sanitize($_POST['note'] ?? '');

        // Log the event
        $id = Database::insert('sos_events', [
            'user_id'      => $userId,
            'trigger_note' => $note,
            'created_at'   => now(),
        ]);

        // Notify partner asynchronously via cron queue file
        $this->queueNotification('sos_alert', $userId, $id);

        // HTMX response — swap in resolve button
        echo '<div class="sos-triggered">
            <p class="text-green-400">Partner notified. You are not alone. 🙏</p>
            <button hx-post="/sos/resolve" hx-swap="outerHTML"
                class="btn-resolve">I am okay now</button>
        </div>';
    }

    public function resolve(): void
    {
        Database::query(
            'UPDATE sos_events SET resolved = 1, resolved_at = ? WHERE user_id = ? AND resolved = 0',
            [now(), Auth::id()]
        );
        echo '<p class="text-blue-300">Well done. Keep going. God is with you. 💙</p>';
    }

    private function queueNotification(string $type, int $userId, int $refId): void
    {
        // Simple file queue — cron worker picks up every minute
        $job = json_encode(['type' => $type, 'user_id' => $userId, 'ref_id' => $refId, 'at' => now()]);
        file_put_contents(ROOT . '/storage/queue/' . uniqid() . '.json', $job);
    }
}
```

### controllers/SobrietyController.php
```php
<?php
class SobrietyController
{
    public function dashboard(): void
    {
        $user   = Auth::user();
        $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$user['id']]);
        $recent = Database::rows(
            'SELECT * FROM check_ins WHERE user_id = ? ORDER BY checked_in_at DESC LIMIT 14',
            [$user['id']]
        );
        $todayDone = Database::row(
            'SELECT id FROM check_ins WHERE user_id = ? AND checked_in_at = ?',
            [$user['id'], date('Y-m-d')]
        );
        view('sobriety/dashboard', compact('user', 'streak', 'recent', 'todayDone'));
    }

    public function checkin(): void
    {
        csrf_check();
        $userId   = Auth::id();
        $today    = date('Y-m-d');
        $mood     = (int) ($_POST['mood'] ?? 3);
        $relapsed = isset($_POST['relapsed']);

        // Upsert check-in
        $existing = Database::row(
            'SELECT id FROM check_ins WHERE user_id = ? AND checked_in_at = ?',
            [$userId, $today]
        );
        if ($existing) {
            Database::query(
                'UPDATE check_ins SET mood = ?, relapsed = ?, note = ? WHERE id = ?',
                [$mood, $relapsed, sanitize($_POST['note'] ?? ''), $existing['id']]
            );
        } else {
            Database::insert('check_ins', [
                'user_id'       => $userId,
                'mood'          => $mood,
                'relapsed'      => $relapsed ? 1 : 0,
                'note'          => sanitize($_POST['note'] ?? ''),
                'prayer_done'   => isset($_POST['prayer']) ? 1 : 0,
                'scripture_read'=> isset($_POST['scripture']) ? 1 : 0,
                'checked_in_at' => $today,
                'created_at'    => now(),
            ]);
        }

        // Update streak
        $this->updateStreak($userId, $relapsed);

        if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
            echo '<div class="check-success">✅ Check-in saved. Keep going!</div>';
        } else {
            redirect('/dashboard');
        }
    }

    private function updateStreak(int $userId, bool $relapsed): void
    {
        $streak = Database::row('SELECT * FROM streaks WHERE user_id = ?', [$userId]);

        if ($relapsed) {
            Database::query(
                'UPDATE streaks SET current_days = 0, total_relapses = total_relapses + 1 WHERE user_id = ?',
                [$userId]
            );
            return;
        }

        $last        = $streak['last_clean_date'];
        $yesterday   = date('Y-m-d', strtotime('-1 day'));
        $newCurrent  = ($last === $yesterday || $last === date('Y-m-d')) 
                       ? $streak['current_days'] + 1 
                       : 1;

        Database::query(
            'UPDATE streaks SET current_days = ?, longest_days = GREATEST(longest_days, ?),
             last_clean_date = ? WHERE user_id = ?',
            [$newCurrent, $newCurrent, date('Y-m-d'), $userId]
        );
    }
}
```

---

## 6. Nginx Configuration

```nginx
server {
    listen 80;
    server_name freedomos.app www.freedomos.app;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name freedomos.app;
    root /var/www/freedomos/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/freedomos.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/freedomos.app/privkey.pem;

    # FreedomGuard: block adult URLs at Nginx level
    map $request_uri $blocked {
        default 0;
        ~*(?i)(porn|xxx|nude|naked|hentai|adult|escort|onlyfans|livejasmin) 1;
    }
    if ($blocked) { return 302 /blocked; }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    # Block direct access to sensitive folders
    location ~ ^/(core|controllers|models|config|storage|cron|freedomguard) {
        deny all;
        return 404;
    }
}
```

---

## 7. Cron Jobs (crontab -e)

```bash
# Daily check-in reminder at 7:00 AM
0 7 * * * php /var/www/freedomos/cron/checkin_reminder.php >> /var/log/freedomos-cron.log 2>&1

# Generate tomorrow's AI devotional at midnight
0 0 * * * php /var/www/freedomos/cron/generate_devotional.php >> /var/log/freedomos-cron.log 2>&1

# Weekly accountability digest on Sundays 8 AM
0 8 * * 0 php /var/www/freedomos/cron/weekly_digest.php >> /var/log/freedomos-cron.log 2>&1

# Update FreedomGuard blocklist weekly
0 3 * * 1 php /var/www/freedomos/cron/update_blocklist.php >> /var/log/freedomos-cron.log 2>&1

# Process notification queue every minute
* * * * * php /var/www/freedomos/cron/process_queue.php >> /var/log/freedomos-queue.log 2>&1
```

---

## 8. FreedomGuard Blocker (freedomguard/daemon.php)

```php
<?php
// Run: php freedomguard/daemon.php
// Cron: 0 3 * * 1 php /var/www/freedomos/freedomguard/daemon.php

define('ROOT', dirname(__DIR__));

$sources = [
    'https://blocklistproject.github.io/Lists/adult.txt',
    'https://raw.githubusercontent.com/StevenBlack/hosts/master/alternates/porn/hosts',
];

$domains = [];
foreach ($sources as $url) {
    $lines = @file($url, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if (str_starts_with($line, '#') || str_starts_with($line, '!')) continue;
        // Parse "0.0.0.0 domain.com" format
        if (preg_match('/^0\.0\.0\.0\s+(\S+)/', $line, $m)) {
            $domains[] = $m[1];
        }
    }
}

// Also load local custom list
$custom = file(ROOT . '/freedomguard/rules/domains.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
$domains = array_unique(array_merge($domains, $custom));

// Write hosts file
$hostsContent = "# FreedomGuard — updated " . date('Y-m-d H:i') . "\n";
foreach ($domains as $domain) {
    $hostsContent .= "0.0.0.0 {$domain}\n";
    $hostsContent .= "0.0.0.0 www.{$domain}\n";
}

file_put_contents('/etc/hosts.freedomguard', $hostsContent);

// Update DB for reporting
$db = new PDO(/* connection */);
$stmt = $db->prepare('INSERT INTO blocker_rules (domain, updated_at) VALUES (?, ?) ON DUPLICATE KEY UPDATE updated_at = ?');
foreach (array_slice($domains, 0, 5000) as $domain) {
    $now = date('Y-m-d H:i:s');
    $stmt->execute([$domain, $now, $now]);
}

echo "FreedomGuard updated: " . count($domains) . " domains blocked.\n";
```

---

## 9. MySQL Schema (run once)

```sql
CREATE DATABASE freedomos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE freedomos;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','mentor','admin') DEFAULT 'user',
  phone VARCHAR(20) DEFAULT NULL,
  timezone VARCHAR(60) DEFAULT 'Africa/Lagos',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE streaks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  current_days INT DEFAULT 0,
  longest_days INT DEFAULT 0,
  last_clean_date DATE DEFAULT NULL,
  total_relapses INT DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE check_ins (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  mood TINYINT NOT NULL DEFAULT 3,
  note TEXT,
  relapsed TINYINT(1) DEFAULT 0,
  prayer_done TINYINT(1) DEFAULT 0,
  scripture_read TINYINT(1) DEFAULT 0,
  checked_in_at DATE NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_date (user_id, checked_in_at),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE sos_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  trigger_note TEXT,
  scripture_shown VARCHAR(30),
  partner_alerted TINYINT(1) DEFAULT 0,
  resolved TINYINT(1) DEFAULT 0,
  resolved_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE accountability_pairs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  partner_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','active','paused') DEFAULT 'pending',
  invite_token VARCHAR(64),
  sos_alerts TINYINT(1) DEFAULT 1,
  paired_at DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE scriptures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(30) NOT NULL,
  text TEXT NOT NULL,
  category ENUM('sos','identity','purpose','worship','general') DEFAULT 'general'
);

CREATE TABLE devotionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  day_number SMALLINT DEFAULT NULL,
  title VARCHAR(200),
  scripture_ref VARCHAR(30),
  scripture_text TEXT,
  body TEXT,
  prayer TEXT,
  ai_generated TINYINT(1) DEFAULT 0,
  published_date DATE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE goals (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  category ENUM('spiritual','relational','physical','vocational','ministry'),
  title VARCHAR(200),
  description TEXT,
  target_date DATE,
  completed_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE testimonies (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(200),
  body LONGTEXT,
  is_public TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE blocker_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED DEFAULT NULL,
  device_id VARCHAR(100),
  blocked_url VARCHAR(500),
  reason VARCHAR(50),
  partner_notified TINYINT(1) DEFAULT 0,
  attempted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_date (attempted_at)
);
```

---

## 10. Build Order (recommended)

Week 1: Folder structure + core/ classes + Nginx config + MySQL schema + Auth (register/login)
Week 2: Sobriety Tracker (dashboard, check-in, streak logic)
Week 3: SOS Panic Button (fast page, scripture cache, HTMX trigger, file queue)
Week 4: Accountability (pairing, partner dashboard, email invite)
Week 5: Devotional Companion (90-day seeded content + AI generation)
Week 6: Purpose Map (goals, testimony builder)
Week 7: FreedomGuard daemon + Nginx blocker + override flow
Week 8: PWA manifest + service worker + cron jobs + testing
Week 9: Security audit + production deployment (Nginx + PHP-FPM + Let's Encrypt)
Week 10: Beta test with real users + fix, polish, launch

---

*FreedomOS — Luke 4:18 · Built to set captives free*
