<?php

declare(strict_types=1);

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;

require_once dirname(__DIR__, 3) . '/bootstrap.php';

final class ScopedService {}

$container = makeTestContainer();
$container->scoped(ScopedService::class, ScopedService::class);

assertThrows(
/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */ ContainerException::class,
    static fn () => $container->get(ScopedService::class),
    'Scoped services must fail closed without an active scope.'
);

$fromWithinScope = $container->scopes()->withinScope(
    static function () use ($container) : object {
        $first  = $container->get(ScopedService::class);
        $second = $container->get(ScopedService::class);

        assertSame($first, $second, 'Scoped services should be reused inside one scope.');

        return $first;
    }
);

$container->openScope();
$firstScope = $container->get(ScopedService::class);
$container->closeScope();

$container->openScope();
$secondScope = $container->get(ScopedService::class);
$container->closeScope();

assertInstanceOf(ScopedService::class, $fromWithinScope, 'Scope callback should return the resolved service.');
assertNotSame($firstScope, $secondScope, 'Scoped services should not leak across scopes.');

echo basename(__FILE__) . " ok\n";
