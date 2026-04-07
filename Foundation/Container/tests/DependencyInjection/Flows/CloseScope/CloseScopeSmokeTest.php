<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

final class CloseScopedService
{
}

$container = makeTestContainer();
$container->scoped(CloseScopedService::class, CloseScopedService::class);

$container->openScope();
$first = $container->get(CloseScopedService::class);
$container->closeScope();

$container->openScope();
$second = $container->get(CloseScopedService::class);
$container->closeScope();

assertNotSame($first, $second, 'CloseScope should discard scoped instances from the previous scope.');

echo basename(__FILE__) . " ok\n";
