<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;

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

$container = makeTestContainer(CreateContainerConfig::create(
    debug: true,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED
));

$container->get(ObservableService::class);
$container->call(ObservableAction::class);
$container->injectInto(new class {
});

$metrics = $container->exportMetrics();
$timeline = $container->get(ResolutionTimeline::class)->all();

assertTrue(str_contains($metrics, 'container_resolve_total'), 'Resolution metrics must be exported.');
assertTrue(str_contains($metrics, 'container_calls_total'), 'Call metrics must be exported.');
assertTrue(str_contains($metrics, 'container_injections_total'), 'Injection metrics must be exported.');
assertTrue($timeline !== [], 'Resolution timeline should record runtime events.');
assertTrue(isset($timeline[0]['time']) && is_float($timeline[0]['time']), 'Timeline entries should carry timestamps.');

echo basename(__FILE__) . " ok\n";
