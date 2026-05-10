<?php

declare(strict_types=1);

/**
 * System Design Report Demo reference application.
 *
 * Proves: Runtime architecture/capacity/risk report concepts.
 *
 * Run: php examples/v4/system-design-report-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated system architecture state
$architecture = [
    'layers' => [
        'presentation' => ['components' => ['HttpKernel', 'Router', 'ResponseFactory']],
        'application' => ['components' => ['Flows', 'RunApplication', 'StateReset']],
        'domain' => ['components' => ['Runtime', 'RequestScope', 'ComponentRegistry']],
        'infrastructure' => ['components' => ['Database', 'Cache', 'Queue', 'Filesystem']],
    ],
    'dependencies' => [
        'psr/http-message' => '2.0',
        'psr/container' => '2.0',
        'psr/log' => '3.0',
    ],
];

$capacity = [
    'current_workers' => 4,
    'max_connections' => 1024,
    'current_memory_mb' => 128,
    'max_memory_mb' => 512,
    'requests_per_second' => 250,
];

$risks = [
    'high_memory_usage' => [
        'severity' => 'medium',
        'description' => 'Memory usage approaching limit',
        'mitigation' => 'Increase max_memory_mb or optimize hot paths',
    ],
    'single_worker_failure' => [
        'severity' => 'low',
        'description' => 'No worker redundancy configured',
        'mitigation' => 'Add redundant workers for critical flows',
    ],
];

$app->get('/health', static fn () => ['status' => 'ok']);

$app->get('/report', static function () use (&$architecture) {
    return [
        'architecture' => $architecture,
        'pattern' => 'vertical-slice',
        'layer_count' => count($architecture['layers']),
        'generated_at' => date('c'),
    ];
});

$app->get('/capacity', static function () use (&$capacity) {
    $memoryUtilization = round(($capacity['current_memory_mb'] / $capacity['max_memory_mb']) * 100, 1);

    $recommendations = [];

    if ($memoryUtilization > 75) {
        $recommendations[] = 'Increase max memory or optimize memory-heavy components';
    }

    if ($capacity['requests_per_second'] > 200) {
        $recommendations[] = 'Consider adding more workers for sustained high throughput';
    }

    if ($capacity['max_connections'] < 2048) {
        $recommendations[] = 'Increase max connections for production traffic';
    }

    if (count($recommendations) === 0) {
        $recommendations[] = 'Capacity is within healthy range';
    }

    return [
        'current' => $capacity,
        'memory_utilization_percent' => $memoryUtilization,
        'recommendations' => $recommendations,
    ];
});

$app->get('/risks', static function () use (&$risks) {
    $bySeverity = ['critical' => [], 'high' => [], 'medium' => [], 'low' => []];

    foreach ($risks as $name => $risk) {
        $bySeverity[$risk['severity']][] = [
            'name' => $name,
            'description' => $risk['description'],
            'mitigation' => $risk['mitigation'],
        ];
    }

    return [
        'risks' => array_values($risks),
        'by_severity' => $bySeverity,
        'total' => count($risks),
        'generated_at' => date('c'),
    ];
});

$app->get('/simulate', static function () use (&$risks, &$capacity) {
    $scenario = $_GET['scenario'] ?? '';

    $scenarios = [
        'worker_crash' => [
            'scenario' => 'worker_crash',
            'impact' => 'One worker goes down, remaining workers must handle full load',
            'remaining_workers' => max(1, $capacity['current_workers'] - 1),
            'estimated_rps_after' => round($capacity['requests_per_second'] * 0.75),
            'mitigation' => 'Auto-restart worker, add health checks',
        ],
        'memory_spike' => [
            'scenario' => 'memory_spike',
            'impact' => 'Memory usage doubles temporarily',
            'projected_memory_mb' => $capacity['current_memory_mb'] * 2,
            'exceeds_limit' => ($capacity['current_memory_mb'] * 2) > $capacity['max_memory_mb'],
            'mitigation' => 'Implement memory limits per request, add circuit breaker',
        ],
        'db_timeout' => [
            'scenario' => 'db_timeout',
            'impact' => 'Database connections timeout, requests fail or queue',
            'affected_flows' => ['RegisterUser', 'ReadUserProfile', 'UpdateSettings'],
            'mitigation' => 'Add retry with backoff, circuit breaker, fallback cache',
        ],
        'traffic_surge' => [
            'scenario' => 'traffic_surge',
            'impact' => '3x normal traffic hits the system',
            'projected_rps' => $capacity['requests_per_second'] * 3,
            'capacity_exceeded' => ($capacity['requests_per_second'] * 3) > ($capacity['max_connections'] * 0.8),
            'mitigation' => 'Auto-scale workers, add rate limiting, enable queue buffering',
        ],
    ];

    if ($scenario === '' || !isset($scenarios[$scenario])) {
        return [
            'error' => 'Unknown scenario',
            'available' => array_keys($scenarios),
        ];
    }

    return $scenarios[$scenario];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
