<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class OpenScopedService
{
}

$container = makeTestContainer();
$container->scoped(OpenScopedService::class, OpenScopedService::class);

$container->beginScope();
$first = $container->get(OpenScopedService::class);
$second = $container->get(OpenScopedService::class);
$container->endScope();

assertSame($first, $second, 'OpenScope should create a reusable active scope.');

echo basename(__FILE__) . " ok\n";
