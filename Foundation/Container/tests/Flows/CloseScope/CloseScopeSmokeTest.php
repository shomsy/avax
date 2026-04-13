<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class CloseScopedService {}

$container = makeTestContainer();
$container->scoped(abstract: CloseScopedService::class, concrete: CloseScopedService::class);

$container->openScope();
$first = $container->get(id: CloseScopedService::class);
$container->closeScope();

$container->openScope();
$second = $container->get(id: CloseScopedService::class);
$container->closeScope();

assertNotSame(expected: $first, actual: $second, message: 'CloseScope should discard scoped instances from the previous scope.');

echo basename(__FILE__) . " ok\n";
