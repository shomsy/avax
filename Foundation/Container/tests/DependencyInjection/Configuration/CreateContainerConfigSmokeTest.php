<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DependencyInjection\Configuration\CreateContainerConfig;

$config = CreateContainerConfig::create(
    cacheDir: '/tmp/container-cache',
    debug: true,
    settings: ['app' => ['name' => 'Container']],
    strict: true
);

$changed = $config
    ->withCacheDir('/tmp/other-cache')
    ->withDebug(false)
    ->withStrict(false)
    ->withSettings(['app' => ['name' => 'Other']]);

assertSame('/tmp/container-cache', $config->cacheDir, 'Original config must stay immutable.');
assertTrue($config->debug, 'Original debug flag must stay unchanged.');
assertTrue($config->strict, 'Original strict flag must stay unchanged.');
assertSame('Container', $config->settings['app']['name'], 'Original settings must stay unchanged.');
assertSame('/tmp/other-cache', $changed->cacheDir, 'Changed config should carry the new cache dir.');
assertTrue(! $changed->debug, 'Changed config should carry the new debug flag.');
assertTrue(! $changed->strict, 'Changed config should carry the new strict flag.');
assertSame('Other', $changed->settings['app']['name'], 'Changed config should carry the new settings.');

echo basename(__FILE__) . " ok\n";
