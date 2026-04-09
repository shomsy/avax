<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DI\ContainerInterface;
use Avax\Container\DI\Capabilities\Declaration\Providers\DeferredProviderInterface;
use Avax\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;

$reflection = new ReflectionClass(ServiceProviderInterface::class);
$constructor = $reflection->getMethod('__construct');
$dependsOn = $reflection->getMethod('dependsOn');
$register = $reflection->getMethod('register');
$boot = $reflection->getMethod('boot');

assertSame(1, $constructor->getNumberOfParameters(), 'Provider contract should require the container boundary.');
assertSame(ContainerInterface::class, $constructor->getParameters()[0]->getType()?->getName(), 'Provider contract should depend on ContainerInterface.');
assertSame('dependsOn', $dependsOn->getName(), 'Provider contract should expose dependsOn().');
assertSame('array', $dependsOn->getReturnType()?->getName(), 'Provider dependencies should be returned as an array.');
assertSame('register', $register->getName(), 'Provider contract should expose register().');
assertSame('boot', $boot->getName(), 'Provider contract should expose boot().');

$deferredReflection = new ReflectionClass(DeferredProviderInterface::class);

assertTrue($deferredReflection->implementsInterface(ServiceProviderInterface::class), 'Deferred provider contract should extend the base provider contract.');
assertSame('deferred', $deferredReflection->getMethod('deferred')->getName(), 'Deferred provider contract should expose deferred().');
assertSame('provides', $deferredReflection->getMethod('provides')->getName(), 'Deferred provider contract should expose provides().');
assertSame('array', $deferredReflection->getMethod('provides')->getReturnType()?->getName(), 'Deferred provider ownership should be returned as an array.');

echo basename(__FILE__) . " ok\n";
