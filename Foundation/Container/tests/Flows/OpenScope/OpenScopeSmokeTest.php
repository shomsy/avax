<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class OpenScopedService {}

$container = makeTestContainer();
$container->scoped(abstract: OpenScopedService::class, concrete: OpenScopedService::class);

$container->openScope();
$first  = $container->get(id: OpenScopedService::class);
$second = $container->get(id: OpenScopedService::class);
$container->closeScope();

assertSame(expected: $first, actual: $second, message: 'OpenScope should create a reusable active scope.');

echo basename(__FILE__) . " ok\n";
