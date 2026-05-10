<?php

declare(strict_types=1);

/**
 * Feature Flag Demo reference application.
 *
 * Proves: FeatureFlags and config override concepts.
 *
 * Run: php examples/v4/feature-flag-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated feature flag store
$featureFlags = [
    'dark-mode' => [
        'name' => 'dark-mode',
        'enabled' => true,
        'description' => 'Enable dark mode UI',
        'updated_at' => date('c'),
    ],
    'beta-checkout' => [
        'name' => 'beta-checkout',
        'enabled' => false,
        'description' => 'New checkout flow',
        'updated_at' => date('c'),
    ],
    'new-dashboard' => [
        'name' => 'new-dashboard',
        'enabled' => false,
        'description' => 'Redesigned dashboard',
        'updated_at' => date('c'),
    ],
];

$app->get('/health', static fn () => ['status' => 'ok']);

$app->get('/features', static function () use (&$featureFlags) {
    return [
        'flags' => array_values($featureFlags),
        'count' => count($featureFlags),
    ];
});

$app->post('/features/{name}/toggle', static function ($name) use (&$featureFlags) {
    if (!isset($featureFlags[$name])) {
        return ['error' => 'Feature flag not found', 'name' => $name];
    }

    $flag = &$featureFlags[$name];
    $flag['enabled'] = !$flag['enabled'];
    $flag['updated_at'] = date('c');

    return [
        'status' => 'toggled',
        'flag' => $flag,
    ];
});

$app->get('/features/{name}/check', static function ($name) use (&$featureFlags) {
    if (!isset($featureFlags[$name])) {
        return ['error' => 'Feature flag not found', 'name' => $name, 'enabled' => false];
    }

    return [
        'name' => $name,
        'enabled' => $featureFlags[$name]['enabled'],
        'description' => $featureFlags[$name]['description'],
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
