<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;

interface DiagnosticsContract
{
    public function label() : string;
}

final class DiagnosticsService implements DiagnosticsContract
{
    public function label() : string
    {
        return 'diagnostics';
    }
}

final class ContextualNameConsumer
{
    public function __construct(public string $name) {}
}

final class DiagnosticsScopedService
{
    public function __construct(public string $id = 'scoped') {}
}

final class ContextualInjectionTarget
{
    public string $name = 'unset';

    #[Inject]
    protected function wire(string $name) : void
    {
        $this->name = $name;
    }
}

$envKey = 'AVAX_CONTAINER_ENV_' . uniqid();
putenv($envKey);

$config    = CreateContainerConfig::create(settings: ['env' => [$envKey => 'test']]);
$container = makeTestContainer($config);

$container->singleton(DiagnosticsContract::class, DiagnosticsService::class);
$container->alias('diagnostics.service', DiagnosticsContract::class);
$container->tag(DiagnosticsContract::class, 'diagnostics');
$container->scoped(DiagnosticsScopedService::class, DiagnosticsScopedService::class);
$container->openScope();
$container->get(DiagnosticsScopedService::class);
$container->compileContainer([DiagnosticsContract::class, ContextualNameConsumer::class, DiagnosticsScopedService::class]);

$description      = $container->describeService(DiagnosticsContract::class);
$aliasDescription = $container->describeService('diagnostics.service');
$debugService     = $container->debugService(DiagnosticsContract::class);
$plan             = $container->debugPlan(DiagnosticsContract::class);
$tags             = $container->debugTags('diagnostics');
$selection        = $container->debugSelection(DiagnosticsContract::class);
$governance       = $container->debugGovernance();
$architecture     = $container->debugArchitecture();
$aliases          = $container->debugAliases();
$scope            = $container->debugScope();
$compileReport    = $container->compileReport([DiagnosticsContract::class]);
$runtimeReport    = $container->runtimeReport();
$validated        = $container->validate([DiagnosticsContract::class, ContextualNameConsumer::class]);
$contextual       = $container->forContext(['name' => 'from-context'])->make(ContextualNameConsumer::class);
$called           = $container->forContext(['name' => 'from-call'])->call(
    static fn (string $name) : string => $name
);
$injected         = $container->forContext(['name' => 'from-injection'])->injectInto(new ContextualInjectionTarget());

assertSame('test', $container->env($envKey), 'Environment access should prefer configured env values.');
assertSame([], $validated, 'Validation should pass for explicitly checked services.');
assertSame(DiagnosticsService::class, $description['concrete'], 'Service descriptions should expose the registered service class.');
assertSame($description['resolvedId'], $debugService['resolvedId'], 'debugService() should mirror the canonical service description.');
assertSame('diagnostics', $container->get(DiagnosticsContract::class)->label(), 'Resolved services should still behave normally.');
assertTrue($description['compiled'], 'Compiled services should be marked as such in diagnostics.');
assertTrue($description['warmedUp'], 'Compiled services should report a warmed-up compiled artifact.');
assertTrue($container->hasAlias('diagnostics.service'), 'Public alias status helpers should expose registered aliases.');
assertTrue(! $container->isDeferred(DiagnosticsContract::class), 'Non-deferred services should report deferred status correctly.');
assertTrue($container->isCompiled(DiagnosticsContract::class), 'Public compiled status helpers should reflect compiled entries.');
assertTrue($container->isWarmedUp(), 'Public warmup status helpers should reflect the compiled artifact state.');
assertTrue($compileReport !== null && $compileReport->available, 'Compile report should expose the active compiled artifact.');
assertTrue($compileReport?->compatible ?? false, 'Compile report should expose artifact compatibility.');
assertSame('fresh', $compileReport?->freshnessState, 'Compile reports should expose artifact freshness.');
assertSame(2, $compileReport?->toArray()['schemaVersion'] ?? null, 'Compile reports should expose a stable JSON schema version.');
assertSame(CreateContainerConfig::EXECUTION_MODE_COMPILED, $compileReport?->executionMode, 'Compile reports should expose execution mode.');
assertTrue(str_contains($compileReport?->toJson() ?? '', '"available": true'), 'Compile report should be JSON serializable.');
assertSame($compileReport?->fingerprint, $runtimeReport->compiled?->fingerprint, 'Runtime report should point to the same compiled artifact report.');
assertTrue($runtimeReport->compiledAttached || $runtimeReport->warmedUp, 'Runtime report should expose compiled runtime state.');
assertSame(4, $runtimeReport->toArray()['schemaVersion'] ?? null, 'Runtime reports should expose a stable JSON schema version.');
assertSame(CreateContainerConfig::EXECUTION_MODE_COMPILED, $runtimeReport->executionMode, 'Runtime reports should expose execution mode.');
assertSame(CreateContainerConfig::ASYNC_TARGET_FPM, $runtimeReport->asyncTarget, 'Runtime reports should expose async target posture.');
assertSame(CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT, $runtimeReport->sliceBoundaryMode, 'Runtime reports should expose slice boundary posture.');
assertTrue($runtimeReport->timelineEnabled === false, 'Minimal diagnostics mode should disable timeline recording.');
assertSame(0, $runtimeReport->sharedServiceCount, 'Runtime report should summarize shared service counts without inventing unresolved shared instances.');
assertSame(1, $runtimeReport->scopedServiceCount, 'Runtime report should summarize scoped service counts.');
assertSame('fresh', $runtimeReport->hotPath['freshnessState'], 'Runtime report should expose hot-path artifact freshness.');
assertTrue(str_contains($runtimeReport->toJson(), '"metrics"'), 'Runtime report should be JSON serializable.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, $runtimeReport->diagnosticsMode, 'Runtime report should expose the active diagnostics mode.');
assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, $description['diagnosticsMode'], 'Service diagnostics should expose the active diagnostics mode.');
assertSame(['diagnostics.service', DiagnosticsContract::class], $aliasDescription['aliasChain'], 'Alias diagnostics should expose the alias expansion chain.');
assertSame([], $description['decorationChain'], 'Undecorated services should expose an empty decoration chain.');
assertTrue(isset($description['cacheState']['cached']), 'Service diagnostics should expose cache state.');
assertTrue(isset($description['compiledState']['artifactAvailable']), 'Service diagnostics should expose compiled state.');
assertSame('compiled', $description['compiledState']['decision'], 'Compiled diagnostics should explain when the compiled path is usable.');
assertSame(
    ['diagnostics.service', DiagnosticsContract::class],
    $aliasDescription['explain']['aliasExpansion']['chain'],
    'Explain diagnostics should expose alias expansion chains.'
);
assertTrue(
    isset($description['explain']['dependencyChain']['dependencies']),
    'Explain diagnostics should expose dependency chain output.'
);
assertSame(
    'no contextual override is registered for this service',
    $description['explain']['contextualWinner']['reason'],
    'Explain diagnostics should expose contextual winner output even when there is no active contextual override.'
);
assertSame(
    'compiled hot path is attached and usable',
    $description['explain']['compiled']['reason'],
    'Explain diagnostics should expose why the compiled path was selected.'
);
assertSame(
    CreateContainerConfig::EXECUTION_MODE_COMPILED,
    $description['compiledState']['executionMode'],
    'Service diagnostics should expose compiled execution mode.'
);
assertSame(
    'service will resolve through a fresh build path and then enter lifetime storage if needed',
    $description['explain']['cache']['reason'],
    'Explain diagnostics should expose cache hit and miss reasoning.'
);
assertSame([], $description['explain']['failureChain'], 'Healthy diagnostics should expose an empty failure chain.');
assertTrue($tags['ordered'], 'Debug tags should declare deterministic ordering.');
assertSame(1, $scope['depth'], 'Debug scope should expose the active scope depth.');
assertSame([], $plan['methods'], 'Debug plans should expose a simple constructor-only service.');
assertTrue(isset($aliases['diagnostics.service']), 'Debug aliases should expose alias mappings.');
assertSame(1, count($tags['ids']), 'Debug tags should expose tagged service ids.');
assertSame(DiagnosticsContract::class, $selection['service'] ?? null, 'Selection diagnostics should expose the resolved service id.');
assertSame('balanced', $governance['profile'] ?? null, 'Governance diagnostics should expose the active policy profile.');
assertTrue(isset($architecture['structuralDrift']), 'Architecture diagnostics should expose structural drift output.');
assertTrue($scope['scoped'] !== [], 'Debug scope should expose active scoped instances.');
assertSame('from-context', $contextual->name, 'Context views should feed scalar constructor values into compiled services.');
assertSame('from-call', $called, 'Context views should feed scalar callable arguments.');
assertSame('from-injection', $injected->name, 'Context views should feed scalar injection arguments.');
$container->closeScope();
assertTrue(str_contains($container->exportMetrics(), 'container_scope_open_total'), 'Scope lifecycle metrics should be exported.');
assertTrue(str_contains($container->exportMetrics(), 'container_scope_close_total'), 'Scope lifecycle metrics should be exported.');

$lazyProxy = $container->lazy(DiagnosticsContract::class);
assertSame('diagnostics', $lazyProxy->label(), 'Lazy proxies should still resolve the target service.');
assertTrue($container->isLazy(DiagnosticsContract::class), 'Public lazy status helpers should reflect lazy proxy usage.');

$detailedContainer = makeTestContainer(CreateContainerConfig::create(
    debug          : true,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED
));
$detailedContainer->get(DiagnosticsService::class);
$detailedReport = $detailedContainer->runtimeReport();

assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED, $detailedReport->diagnosticsMode, 'Detailed runtime reports should expose detailed diagnostics mode.');
assertTrue($detailedReport->timeline !== [], 'Detailed diagnostics mode should keep timeline events enabled.');
assertTrue($detailedReport->timelineEnabled, 'Detailed diagnostics mode should report timeline state as enabled.');

$ciContainer = makeTestContainer(CreateContainerConfig::create(
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI
));
$ciContainer->get(DiagnosticsService::class);
$ciReport = $ciContainer->runtimeReport();

assertSame(CreateContainerConfig::DIAGNOSTICS_MODE_CI, $ciReport->diagnosticsMode, 'CI diagnostics mode should be preserved in runtime reports.');
assertTrue($ciReport->timelineEnabled, 'CI diagnostics mode should keep timeline recording enabled.');

echo basename(__FILE__) . " ok\n";
