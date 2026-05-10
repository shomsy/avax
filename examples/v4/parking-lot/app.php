<?php

declare(strict_types=1);

/**
 * Parking Lot reference application.
 *
 * Proves: SystemDesignKit, policy, database concepts.
 *
 * Run: php examples/v4/parking-lot/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// Simulated parking lot state (database)
$lots = [
    'lot-1' => ['id' => 'lot-1', 'name' => 'Main Garage', 'capacity' => 100, 'occupied' => 42],
    'lot-2' => ['id' => 'lot-2', 'name' => 'Street Parking', 'capacity' => 50, 'occupied' => 50],
    'lot-3' => ['id' => 'lot-3', 'name' => 'Mall Deck', 'capacity' => 200, 'occupied' => 15],
];

$app->get('/health', static fn () => ['status' => 'ok']);

$app->get('/lots', static function () use (&$lots) {
    return array_map(static function ($lot) {
        return [
            'id' => $lot['id'],
            'name' => $lot['name'],
            'available' => $lot['capacity'] - $lot['occupied'],
            'capacity' => $lot['capacity'],
            'occupied' => $lot['occupied'],
        ];
    }, $lots);
});

$app->post('/lots/{id}/park', static function ($id) use (&$lots) {
    if (!isset($lots[$id])) {
        return ['error' => 'Lot not found', 'id' => $id];
    }

    $lot = &$lots[$id];

    // Policy: check capacity
    if ($lot['occupied'] >= $lot['capacity']) {
        return ['error' => 'Lot is full', 'id' => $id, 'name' => $lot['name']];
    }

    $lot['occupied']++;

    return [
        'status' => 'parked',
        'lot' => $lot['name'],
        'available' => $lot['capacity'] - $lot['occupied'],
    ];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
