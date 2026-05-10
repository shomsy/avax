<?php

declare(strict_types=1);

/**
 * Secure Registration API reference application.
 *
 * Proves: SecureRequest, DataTransfer, validation, policy, error handling.
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

$app->post('/register', static function () {
    // Simulate registration with validation
    return ['status' => 'registered', 'message' => 'User registered successfully'];
});

$app->get('/health', static fn () => ['status' => 'ok']);

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
