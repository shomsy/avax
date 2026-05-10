<?php

declare(strict_types=1);

/**
 * URL Shortener reference application.
 *
 * Proves: Database component concept, shorten/redirect routes, JSON responses.
 *
 * Run: php examples/v4/url-shortener/app.php
 */

require __DIR__ . '/../../../vendor/autoload.php';

use Avax\Framework\System\PublicSurface\Avax;

$app = Avax::create();

// In-memory URL store (simulates database)
$urls = [];
$base = 'http://localhost:8080/';
$counter = 0;

$app->get('/health', static fn () => ['status' => 'ok']);

$app->post('/shorten', static function () use (&$urls, &$counter, $base) {
    $counter++;
    $code = base_convert($counter, 10, 36);
    $urls[$code] = 'https://example.com/' . $counter;

    return [
        'shortUrl' => $base . $code,
        'originalUrl' => $urls[$code],
        'code' => $code,
    ];
});

$app->get('/{code}', static function ($code) use (&$urls) {
    if (!isset($urls[$code])) {
        return ['error' => 'URL not found', 'code' => $code];
    }

    return ['redirect' => $urls[$code], 'code' => $code];
});

$GLOBALS['_APP_RUN'] = false;

if (php_sapi_name() === 'cli' && $GLOBALS['_APP_RUN'] ?? true) {
    $app->run();
}
