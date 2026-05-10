<?php

declare(strict_types=1);

/**
 * Outbox Messaging Demo reference application.
 *
 * Proves: Database transaction, outbox pattern, event relay concepts.
 *
 * Run: php examples/v4/outbox-messaging-demo/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated database state
$orders = [];
$outbox = [];
$orderCounter = 0;
$eventCounter = 0;

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/orders', static function () use (&$orders, &$outbox, &$orderCounter, &$eventCounter) {
    $orderCounter++;

    // Simulate transaction: create order + write outbox event atomically
    $order = [
        'id' => 'ord-' . $orderCounter,
        'item' => 'Widget',
        'quantity' => 1,
        'status' => 'created',
        'created_at' => date('c'),
    ];

    $orders[] = $order;

    $eventCounter++;
    $outbox[] = [
        'id' => 'evt-' . $eventCounter,
        'aggregate_id' => $order['id'],
        'type' => 'OrderCreated',
        'payload' => $order,
        'status' => 'pending',
        'created_at' => date('c'),
    ];

    return [
        'status' => 'created',
        'order' => $order,
        'outbox_event' => $outbox[count($outbox) - 1]['id'],
    ];
});

$app->get('/outbox', static function () use (&$outbox) {
    $pending = array_filter($outbox, static fn ($e) => $e['status'] === 'pending');

    return [
        'pending_events' => array_values($pending),
        'total' => count($outbox),
        'pending_count' => count($pending),
    ];
});

$app->post('/outbox/{id}/relay', static function ($id) use (&$outbox) {
    foreach ($outbox as &$event) {
        if ($event['id'] === $id) {
            if ($event['status'] !== 'pending') {
                return ['status' => 'already_relayed', 'id' => $id];
            }

            $event['status'] = 'relayed';
            $event['relayed_at'] = date('c');

            return [
                'status' => 'relayed',
                'event_id' => $id,
                'type' => $event['type'],
            ];
        }
    }

    return ['error' => 'Event not found', 'id' => $id];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
