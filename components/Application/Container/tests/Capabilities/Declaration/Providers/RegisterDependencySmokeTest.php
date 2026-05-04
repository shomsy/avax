<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDeferredDependency;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\System\ContainerInterface;

$reflection = new ReflectionClass(objectOrClass: RegisterDependency::class);
$constructor = $reflection->getMethod(name: '__construct');
$dependsOn  = $reflection->getMethod(name: 'dependsOn');
$register   = $reflection->getMethod(name: 'register');
$boot       = $reflection->getMethod(name: 'boot');

assertSame(expected: 1, actual: $constructor->getNumberOfParameters(), message: 'Provider contract should require the container boundary.');
assertSame(expected: ContainerInterface::class, actual: $constructor->getParameters()[0]->getType()?->getName(), message: 'Provider contract should depend on ContainerInterface.');
assertSame(expected: 'dependsOn', actual: $dependsOn->getName(), message: 'Provider contract should expose dependsOn().');
assertSame(expected: 'array', actual: $dependsOn->getReturnType()?->getName(), message: 'Provider dependencies should be returned as an array.');
assertSame(expected: 'register', actual: $register->getName(), message: 'Provider contract should expose register().');
assertSame(expected: 'boot', actual: $boot->getName(), message: 'Provider contract should expose boot().');

$deferredReflection = new ReflectionClass(objectOrClass: RegisterDeferredDependency::class);

assertTrue(condition: $deferredReflection->implementsInterface(interface: RegisterDependency::class), message: 'Deferred provider contract should extend the base provider contract.');
assertSame(expected: 'deferred', actual: $deferredReflection->getMethod(name: 'deferred')->getName(), message: 'Deferred provider contract should expose deferred().');
assertSame(expected: 'provides', actual: $deferredReflection->getMethod(name: 'provides')->getName(), message: 'Deferred provider contract should expose provides().');
assertSame(expected: 'array', actual: $deferredReflection->getMethod(name: 'provides')->getReturnType()?->getName(), message: 'Deferred provider ownership should be returned as an array.');

echo basename(path: __FILE__) . " ok\n";
