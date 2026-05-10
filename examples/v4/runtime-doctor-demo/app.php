<?php

declare(strict_types=1);

/**
 * Runtime Doctor Demo reference application.
 *
 * Proves: /health, /health/live, /health/ready, doctor concepts.
 *
 * Run: php examples/v4/runtime-doctor-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated system state
$systemState = [
    'database' => ['connected' => true, 'latency_ms' => 5],
    'cache' => ['connected' => true, 'latency_ms' => 1],
    'queue' => ['connected' => true, 'workers' => 3],
    'disk' => ['available_mb' => 8192, 'used_percent' => 45],
    'memory' => ['available_mb' => 512, 'used_percent' => 30],
];

$app->get('/health', static fn () => ['status' => 'ok']);

$app->get('/health/live', static fn () => [
    'status' => 'alive',
    'pid' => getmypid(),
    'uptime' => 'running',
]);

$app->get('/health/ready', static function () use (&$systemState) {
    $checks = [];
    $ready = true;

    foreach ($systemState as $component => $state) {
        $isHealthy = $state['connected'] ?? true;
        $checks[$component] = [
            'status' => $isHealthy ? 'ok' : 'fail',
            'details' => $state,
        ];

        if (!$isHealthy) {
            $ready = false;
        }
    }

    return [
        'status' => $ready ? 'ready' : 'not_ready',
        'checks' => $checks,
    ];
});

$app->get('/doctor', static function () use (&$systemState) {
    $diagnostics = [];
    $overallStatus = 'healthy';

    // Database check
    $dbLatency = $systemState['database']['latency_ms'];
    if ($dbLatency > 100) {
        $diagnostics['database'] = ['status' => 'degraded', 'issue' => 'High latency: ' . $dbLatency . 'ms'];
        $overallStatus = 'degraded';
    } else {
        $diagnostics['database'] = ['status' => 'healthy', 'latency_ms' => $dbLatency];
    }

    // Cache check
    $diagnostics['cache'] = [
        'status' => $systemState['cache']['connected'] ? 'healthy' : 'down',
        'latency_ms' => $systemState['cache']['latency_ms'],
    ];

    // Queue check
    $workers = $systemState['queue']['workers'];
    if ($workers < 1) {
        $diagnostics['queue'] = ['status' => 'critical', 'issue' => 'No workers available'];
        $overallStatus = 'critical';
    } else {
        $diagnostics['queue'] = ['status' => 'healthy', 'workers' => $workers];
    }

    // Disk check
    $diskUsed = $systemState['disk']['used_percent'];
    if ($diskUsed > 90) {
        $diagnostics['disk'] = ['status' => 'critical', 'issue' => 'Disk usage above 90%'];
        $overallStatus = 'critical';
    } elseif ($diskUsed > 75) {
        $diagnostics['disk'] = ['status' => 'warning', 'issue' => 'Disk usage above 75%'];
        if ($overallStatus === 'healthy') {
            $overallStatus = 'degraded';
        }
    } else {
        $diagnostics['disk'] = ['status' => 'healthy', 'available_mb' => $systemState['disk']['available_mb']];
    }

    // Memory check
    $memUsed = $systemState['memory']['used_percent'];
    if ($memUsed > 90) {
        $diagnostics['memory'] = ['status' => 'critical', 'issue' => 'Memory usage above 90%'];
        $overallStatus = 'critical';
    } else {
        $diagnostics['memory'] = ['status' => 'healthy', 'available_mb' => $systemState['memory']['available_mb']];
    }

    return [
        'status' => $overallStatus,
        'diagnostics' => $diagnostics,
        'checked_at' => date('c'),
    ];
});

$app->get('/status', static function () use (&$systemState) {
    return [
        'status' => 'running',
        'services' => [
            'database' => $systemState['database']['connected'] ? 'connected' : 'disconnected',
            'cache' => $systemState['cache']['connected'] ? 'connected' : 'disconnected',
            'queue' => $systemState['queue']['connected'] ? 'connected' : 'disconnected',
        ],
        'checked_at' => date('c'),
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
