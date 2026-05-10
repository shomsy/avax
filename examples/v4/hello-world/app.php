<?php

declare(strict_types=1);

/**
 * Hello World reference application.
 *
 * Proves: App API, route registration, response normalization.
 *
 * Run: php examples/v4/hello-world/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

$app->get('/', static fn () => 'Hello AvaX');

$app->get('/health', static fn () => ['status' => 'ok']);

// Only run if executed directly (not when included in tests)
if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
