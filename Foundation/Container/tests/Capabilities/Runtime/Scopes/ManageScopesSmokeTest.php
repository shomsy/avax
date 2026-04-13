<?php

declare(strict_types=1);

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

require_once dirname(__DIR__, 3) . '/bootstrap.php';

final class ScopedService {}

$container = makeTestContainer();
$container->scoped(abstract: ScopedService::class, concrete: ScopedService::class);

assertThrows(
/**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ expectedClass: ContainerException::class,
    callback     : static fn () => $container->get(id: ScopedService::class),
    message      : 'Scoped services must fail closed without an active scope.'
);

$fromWithinScope = $container->scopes()->withinScope(
    callback: static function () use ($container) : object {
        $first  = $container->get(id: ScopedService::class);
        $second = $container->get(id: ScopedService::class);

        assertSame(expected: $first, actual: $second, message: 'Scoped services should be reused inside one scope.');

        return $first;
    }
);

$container->openScope();
$firstScope = $container->get(id: ScopedService::class);
$container->closeScope();

$container->openScope();
$secondScope = $container->get(id: ScopedService::class);
$container->closeScope();

assertInstanceOf(expectedClass: ScopedService::class, value: $fromWithinScope, message: 'Scope callback should return the resolved service.');
assertNotSame(expected: $firstScope, actual: $secondScope, message: 'Scoped services should not leak across scopes.');

echo basename(__FILE__) . " ok\n";
