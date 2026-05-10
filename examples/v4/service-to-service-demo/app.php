<?php

declare(strict_types=1);

/**
 * Service-to-Service Demo reference application.
 *
 * Proves: Service registry and signed internal request concepts.
 *
 * Run: php examples/v4/service-to-service-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated service registry
$services = [];
$callLog = [];

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/services/register', static function () use (&$services) {
    $name = $_POST['name'] ?? '';
    $endpoint = $_POST['endpoint'] ?? '';
    $secret = $_POST['secret'] ?? bin2hex(random_bytes(16));

    if ($name === '' || $endpoint === '') {
        return ['error' => 'name and endpoint are required'];
    }

    if (isset($services[$name])) {
        return ['error' => 'Service already registered', 'name' => $name];
    }

    $services[$name] = [
        'name' => $name,
        'endpoint' => $endpoint,
        'secret' => $secret,
        'registered_at' => date('c'),
        'status' => 'active',
    ];

    return [
        'status' => 'registered',
        'service' => [
            'name' => $name,
            'endpoint' => $endpoint,
        ],
        'secret' => $secret,
    ];
});

$app->get('/services/{name}/resolve', static function ($name) use (&$services) {
    if (!isset($services[$name])) {
        return ['error' => 'Service not found', 'name' => $name];
    }

    $service = $services[$name];

    return [
        'name' => $service['name'],
        'endpoint' => $service['endpoint'],
        'status' => $service['status'],
    ];
});

$app->post('/services/{name}/call', static function ($name) use (&$services, &$callLog) {
    if (!isset($services[$name])) {
        return ['error' => 'Service not found', 'name' => $name];
    }

    $service = $services[$name];
    $payload = $_POST['payload'] ?? '';
    $signature = $_SERVER['HTTP_X_SERVICE_SIGNATURE'] ?? '';
    $callerId = $_SERVER['HTTP_X_SERVICE_CALLER'] ?? 'unknown';

    // Simulate signature verification (HMAC of payload + secret)
    $expectedSignature = hash_hmac('sha256', $payload . $name, $service['secret']);

    $callRecord = [
        'caller' => $callerId,
        'target' => $name,
        'timestamp' => date('c'),
        'signature_valid' => $signature === $expectedSignature,
    ];

    $callLog[] = $callRecord;

    return [
        'status' => 'called',
        'service' => $name,
        'endpoint' => $service['endpoint'],
        'signature_valid' => $callRecord['signature_valid'],
        'caller' => $callerId,
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
