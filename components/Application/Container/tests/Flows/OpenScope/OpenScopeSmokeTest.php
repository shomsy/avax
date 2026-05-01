<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

final class OpenScopeSmokeTest {}

$container = makeTestContainer();
$container->scoped(abstract: OpenScopedService::class, concrete: OpenScopedService::class);

$container->openScope();
$first = $container->get(id: OpenScopedService::class);
$second = $container->get(id: OpenScopedService::class);
$container->closeScope();

assertSame(expected: $first, actual: $second, message: 'OpenScope should create a reusable active scope.');

echo basename(path: __FILE__) . " ok\n";
