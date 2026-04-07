<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class ObservableService
{
}

final class ObservableAction
{
    public function __invoke() : string
    {
        return 'ok';
    }
}

$container = makeTestContainer();

$container->get(ObservableService::class);
$container->call(ObservableAction::class);
$container->injectInto(new class {
});

$metrics = $container->exportMetrics();

assertTrue(str_contains($metrics, 'container_resolve_total'), 'Resolution metrics must be exported.');
assertTrue(str_contains($metrics, 'container_calls_total'), 'Call metrics must be exported.');
assertTrue(str_contains($metrics, 'container_injections_total'), 'Injection metrics must be exported.');

echo basename(__FILE__) . " ok\n";
