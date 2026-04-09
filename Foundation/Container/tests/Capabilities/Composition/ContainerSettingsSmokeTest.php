<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\ContainerSettings;

$settings = new ContainerSettings(['app' => ['name' => 'Container']]);
$envKey = 'AVAX_CONTAINER_SETTINGS_ENV_' . uniqid();
putenv($envKey);

assertSame('Container', $settings->get('app.name'), 'Container settings should read dot-notation keys.');
assertTrue($settings->has('app.name'), 'Container settings should report existing keys.');

$settings->set('app.env', 'test');
$settings->set('env.' . $envKey, 'from-settings');

assertSame('test', $settings->get('app.env'), 'Container settings should write dot-notation keys.');
assertTrue(isset($settings->all()['app']['env']), 'Container settings should expose the written structure.');
assertSame('from-settings', $settings->env($envKey), 'Container settings should expose env-backed lookups.');

echo basename(__FILE__) . " ok\n";
