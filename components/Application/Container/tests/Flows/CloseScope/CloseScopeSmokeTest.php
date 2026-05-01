<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

final class CloseScopeSmokeTest
{
}

$container = makeTestContainer();
$container->scoped(abstract: CloseScopedService::class, concrete: CloseScopedService::class);

$container->openScope();
$first = $container->get(id: CloseScopedService::class);
$container->closeScope();

$container->openScope();
$second = $container->get(id: CloseScopedService::class);
$container->closeScope();

assertNotSame(expected: $first, actual: $second, message: 'CloseScope should discard scoped instances from the previous scope.');

echo basename(path: __FILE__) . " ok\n";
