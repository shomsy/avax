<?php

declare(strict_types=1);

/**
 * Queue Worker Demo reference application.
 *
 * Proves: Queue dispatch, job listing, worker concepts.
 *
 * Run: php examples/v4/queue-worker-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated queue state
$jobs = [];
$jobCounter = 0;

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/jobs', static function () use (&$jobs, &$jobCounter) {
    $jobCounter++;
    $job = [
        'id' => 'job-' . $jobCounter,
        'type' => 'email.send',
        'payload' => ['to' => 'user@example.com', 'subject' => 'Welcome'],
        'status' => 'queued',
        'created_at' => date('c'),
    ];

    $jobs[] = $job;

    // Simulate dispatch to queue
    return [
        'status' => 'dispatched',
        'job_id' => $job['id'],
        'queue' => 'default',
    ];
});

$app->get('/jobs', static function () use (&$jobs) {
    return [
        'jobs' => $jobs,
        'total' => count($jobs),
        'queued' => count(array_filter($jobs, static fn ($j) => $j['status'] === 'queued')),
    ];
});

$app->post('/jobs/{id}/process', static function ($id) use (&$jobs) {
    foreach ($jobs as &$job) {
        if ($job['id'] === $id) {
            $job['status'] = 'processed';
            $job['processed_at'] = date('c');

            return [
                'status' => 'processed',
                'job_id' => $id,
            ];
        }
    }

    return ['error' => 'Job not found', 'id' => $id];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
