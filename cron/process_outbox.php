<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    foreach ([ROOT . '/core/', ROOT . '/controllers/', ROOT . '/models/'] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

require_once ROOT . '/core/helpers.php';

$result = Mailer::processQueue((int) ($_SERVER['argv'][1] ?? 100));
echo "Processed {$result['processed']} queued email(s): {$result['sent']} sent, {$result['failed']} failed.\n";
