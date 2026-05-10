<?php

declare(strict_types=1);

/**
 * Observability Demo reference application.
 *
 * Proves: Logs, metrics, traces, audit, correlation concepts.
 *
 * Run: php examples/v4/observability-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated observability stores
$metrics = [
    'http_requests_total' => 0,
    'http_errors_total' => 0,
    'business_events_total' => 0,
    'avg_response_time_ms' => 0,
];
$traces = [];
$auditLog = [];
$traceCounter = 0;
$auditCounter = 0;

$app->get('/health', static fn () => ['status' => 'ok']);

$app->get('/metrics', static function () use (&$metrics) {
    return [
        'metrics' => $metrics,
        'collected_at' => date('c'),
    ];
});

$app->post('/events', static function () use (&$metrics, &$traces, &$traceCounter, &$auditLog, &$auditCounter) {
    // Simulate receiving an event with tracing headers
    $correlationId = $_SERVER['HTTP_X_CORRELATION_ID'] ?? uniqid('corr_', true);
    $traceId = $_SERVER['HTTP_X_TRACE_ID'] ?? uniqid('trace_', true);
    $spanId = uniqid('span_', true);

    // Parse event payload
    $eventType = $_POST['event_type'] ?? 'unknown';
    $eventData = $_POST['event_data'] ?? '';

    $traceCounter++;
    $trace = [
        'trace_id' => $traceId,
        'span_id' => $spanId,
        'correlation_id' => $correlationId,
        'event_type' => $eventType,
        'timestamp' => date('c'),
        'duration_ms' => rand(1, 50),
    ];

    $traces[] = $trace;

    // Update metrics
    $metrics['http_requests_total']++;
    $metrics['business_events_total']++;

    // Write audit entry
    $auditCounter++;
    $auditLog[] = [
        'id' => 'audit-' . $auditCounter,
        'action' => 'event_received',
        'event_type' => $eventType,
        'correlation_id' => $correlationId,
        'timestamp' => date('c'),
    ];

    return [
        'status' => 'logged',
        'trace' => $trace,
        'correlation_id' => $correlationId,
    ];
});

$app->get('/audit', static function () use (&$auditLog) {
    return [
        'audit_trail' => $auditLog,
        'total_entries' => count($auditLog),
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
