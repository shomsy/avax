<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\Configuration\ContainerSettings;

$settings = new ContainerSettings(['app' => ['name' => 'Container']]);

assertSame('Container', $settings->get('app.name'), 'Container settings should read dot-notation keys.');
assertTrue($settings->has('app.name'), 'Container settings should report existing keys.');

$settings->set('app.env', 'test');

assertSame('test', $settings->get('app.env'), 'Container settings should write dot-notation keys.');
assertTrue(isset($settings->all()['app']['env']), 'Container settings should expose the written structure.');

echo basename(__FILE__) . " ok\n";
