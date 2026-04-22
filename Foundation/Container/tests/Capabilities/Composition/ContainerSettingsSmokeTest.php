<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\ContainerSettings;

$settings = new ContainerSettings(items: ['app' => ['name' => 'Container']]);
$envKey   = 'AVAX_CONTAINER_SETTINGS_ENV_' . uniqid();
putenv($envKey);

assertSame(expected: 'Container', actual: $settings->get(key: 'app.name'), message: 'Container settings should read dot-notation keys.');
assertTrue(condition: $settings->has(key: 'app.name'), message: 'Container settings should report existing keys.');

$settings->set(key: 'app.env', value: 'test');
$settings->set(key: 'env.' . $envKey, value: 'from-settings');

assertSame(expected: 'test', actual: $settings->get(key: 'app.env'), message: 'Container settings should write dot-notation keys.');
assertTrue(condition: isset($settings->all()['app']['env']), message: 'Container settings should expose the written structure.');
assertSame(expected: 'from-settings', actual: $settings->env(key: $envKey), message: 'Container settings should expose env-backed lookups.');

echo basename(__FILE__) . " ok\n";
