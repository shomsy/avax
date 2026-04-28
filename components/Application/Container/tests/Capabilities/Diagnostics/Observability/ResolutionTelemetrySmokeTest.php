<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;

final class ObservableService {}

final class ObservableAction
{
    public function __invoke() : string
    {
        return 'ok';
    }
}

$container = makeTestContainer(config: CreateContainerConfig::create(
    debug          : true,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED
));

$container->get(id: ObservableService::class);
$container->call(callable: ObservableAction::class);
$container->injectInto(target: new class {});

$metrics  = $container->exportMetrics();
$timeline = $container->get(id: ResolutionTimeline::class)->all();

assertTrue(condition: str_contains(haystack: $metrics, needle: 'container_resolve_total'), message: 'Resolution metrics must be exported.');
assertTrue(condition: str_contains(haystack: $metrics, needle: 'container_calls_total'), message: 'Call metrics must be exported.');
assertTrue(condition: str_contains(haystack: $metrics, needle: 'container_injections_total'), message: 'Injection metrics must be exported.');
assertTrue(condition: $timeline !== [], message: 'Resolution timeline should record runtime events.');
assertTrue(condition: isset($timeline[0]['time']) && is_float(value: $timeline[0]['time']), message: 'Timeline entries should carry timestamps.');

echo basename(path: __FILE__) . " ok\n";
