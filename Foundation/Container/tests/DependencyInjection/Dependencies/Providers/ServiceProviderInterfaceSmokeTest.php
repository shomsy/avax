<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;

$reflection = new ReflectionClass(ServiceProviderInterface::class);
$constructor = $reflection->getMethod('__construct');
$register = $reflection->getMethod('register');
$boot = $reflection->getMethod('boot');

assertSame(1, $constructor->getNumberOfParameters(), 'Provider contract should require the container boundary.');
assertSame(ContainerInterface::class, $constructor->getParameters()[0]->getType()?->getName(), 'Provider contract should depend on ContainerInterface.');
assertSame('register', $register->getName(), 'Provider contract should expose register().');
assertSame('boot', $boot->getName(), 'Provider contract should expose boot().');

echo basename(__FILE__) . " ok\n";
