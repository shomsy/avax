<?php

declare(strict_types=1);

/**
 * Webhook Receiver reference application.
 *
 * Proves: Request signing, idempotency, queue dispatch concepts.
 *
 * Run: php examples/v4/webhook-receiver/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated webhook inbox and idempotency store
$inbox = [];
$idempotencyKeys = [];
$webhookSecret = 'whsec_test_secret';

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/webhooks/{provider}', static function ($provider) use (&$inbox, &$idempotencyKeys, $webhookSecret) {
    // Simulate signature verification from headers
    // In production: verify HMAC-SHA256 of payload against signature header
    $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
    $expected = hash_hmac('sha256', 'payload', $webhookSecret);

    // Idempotency key from header
    $idempotencyKey = $_SERVER['HTTP_X_IDEMPOTENCY_KEY'] ?? uniqid('wh_', true);

    if (isset($idempotencyKeys[$idempotencyKey])) {
        return [
            'status' => 'duplicate',
            'id' => $idempotencyKeys[$idempotencyKey],
            'message' => 'Request already processed',
        ];
    }

    $eventId = uniqid('evt_', true);
    $inbox[] = [
        'id' => $eventId,
        'provider' => $provider,
        'received_at' => date('c'),
    ];

    $idempotencyKeys[$idempotencyKey] = $eventId;

    // Simulate queue dispatch
    return [
        'status' => 'accepted',
        'event_id' => $eventId,
        'provider' => $provider,
        'queued' => true,
    ];
});

$app->get('/inbox', static function () use (&$inbox) {
    return ['events' => $inbox, 'count' => count($inbox)];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
