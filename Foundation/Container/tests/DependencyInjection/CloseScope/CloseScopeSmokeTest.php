<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class CloseScopedService
{
}

$container = makeTestContainer();
$container->scoped(CloseScopedService::class, CloseScopedService::class);

$container->beginScope();
$first = $container->get(CloseScopedService::class);
$container->endScope();

$container->beginScope();
$second = $container->get(CloseScopedService::class);
$container->endScope();

assertNotSame($first, $second, 'CloseScope should discard scoped instances from the previous scope.');

echo basename(__FILE__) . " ok\n";
