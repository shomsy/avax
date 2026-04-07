<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class ScopedService
{
}

$container = makeTestContainer();
$container->scoped(ScopedService::class, ScopedService::class);

assertThrows(
    RuntimeException::class,
    static fn() => $container->get(ScopedService::class),
    'Scoped services must fail closed without an active scope.'
);

$fromWithinScope = $container->scopes()->withinScope(
    static function () use ($container) : object {
        $first = $container->get(ScopedService::class);
        $second = $container->get(ScopedService::class);

        assertSame($first, $second, 'Scoped services should be reused inside one scope.');

        return $first;
    }
);

$container->beginScope();
$firstScope = $container->get(ScopedService::class);
$container->endScope();

$container->beginScope();
$secondScope = $container->get(ScopedService::class);
$container->endScope();

assertInstanceOf(ScopedService::class, $fromWithinScope, 'Scope callback should return the resolved service.');
assertNotSame($firstScope, $secondScope, 'Scoped services should not leak across scopes.');

echo basename(__FILE__) . " ok\n";
