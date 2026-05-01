<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface DiagnosticsContract
{
    public function label() : string;
}

final class DiagnosticsService implements DiagnosticsContract
{
    #[Override]
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
}

$envKey = 'AVAX_CONTAINER_ENV_' . uniqid();
putenv(assignment: $envKey);

$config = CreateContainerConfig::create(settings: ['env' => [$envKey => 'test']]);
$container = makeTestContainer(config: $config);

$container->singleton(abstract: DiagnosticsContract::class, concrete: DiagnosticsService::class);
$container->alias(alias: 'diagnostics.service', abstract: DiagnosticsContract::class);
$container->tag(abstracts: DiagnosticsContract::class, tags: 'diagnostics');
$container->scoped(abstract: DiagnosticsScopedService::class, concrete: DiagnosticsScopedService::class);
$container->openScope();
$container->get(id: DiagnosticsScopedService::class);
$container->compileContainer(serviceIds: [DiagnosticsContract::class, ContextualNameConsumer::class, DiagnosticsScopedService::class]);

$description   = $container->describeService(id: DiagnosticsContract::class);
$aliasDescription = $container->describeService(id: 'diagnostics.service');
$debugService  = $container->debugService(id: DiagnosticsContract::class);
$plan          = $container->debugPlan(id: DiagnosticsContract::class);
$tags          = $container->debugTags(tag: 'diagnostics');
$selection     = $container->debugSelection(id: DiagnosticsContract::class);
$governance    = $container->debugGovernance();
$architecture  = $container->debugArchitecture();
$aliases       = $container->debugAliases();
$scope         = $container->debugScope();
$compileReport = $container->compileReport(serviceIds: [DiagnosticsContract::class]);
$runtimeReport = $container->runtimeReport();
$validated     = $container->validate(serviceIds: [DiagnosticsContract::class, ContextualNameConsumer::class]);
$contextual    = $container->forContext(context: ['name' => 'from-context'])->make(abstract: ContextualNameConsumer::class);
$called        = $container->forContext(context: ['name' => 'from-call'])->call(
    callable: static fn (string $name) : string => $name,
);
$injected      = $container->forContext(context: ['name' => 'from-injection'])->injectInto(target: new ContextualInjectionTarget);

assertSame(expected: 'test', actual: $container->env(key: $envKey), message: 'Environment access should prefer configured env values.');
assertSame(expected: [], actual: $validated, message: 'Validation should pass for explicitly checked services.');
assertSame(expected: DiagnosticsService::class, actual: $description['concrete'], message: 'Service descriptions should expose the registered service class.');
assertSame(expected: $description['resolvedId'], actual: $debugService['resolvedId'], message: 'debugService() should mirror the canonical service description.');
assertSame(expected: 'diagnostics', actual: $container->get(id: DiagnosticsContract::class)->label(), message: 'Resolved services should still behave normally.');
assertTrue(condition: $description['compiled'], message: 'Compiled services should be marked as such in diagnostics.');
assertTrue(condition: $description['warmedUp'], message: 'Compiled services should report a warmed-up compiled artifact.');
assertTrue(condition: $container->hasAlias(alias: 'diagnostics.service'), message: 'Public alias status helpers should expose registered aliases.');
assertTrue(condition: ! $container->isDeferred(id: DiagnosticsContract::class), message: 'Non-deferred services should report deferred status correctly.');
assertTrue(condition: $container->isCompiled(id: DiagnosticsContract::class), message: 'Public compiled status helpers should reflect compiled entries.');
assertTrue(condition: $container->isWarmedUp(), message: 'Public warmup status helpers should reflect the compiled artifact state.');
assertTrue(condition: $compileReport !== null && $compileReport->available, message: 'Compile report should expose the active compiled artifact.');
assertTrue(condition: $compileReport?->compatible ?? false, message: 'Compile report should expose artifact compatibility.');
assertSame(expected: 'fresh', actual: $compileReport?->freshnessState, message: 'Compile reports should expose artifact freshness.');
assertSame(expected: 2, actual: $compileReport?->toArray()['schemaVersion'] ?? null, message: 'Compile reports should expose a stable JSON schema version.');
assertSame(expected: CreateContainerConfig::EXECUTION_MODE_COMPILED, actual: $compileReport?->executionMode, message: 'Compile reports should expose execution mode.');
assertTrue(condition: str_contains(haystack: $compileReport?->toJson() ?? '', needle: '"available": true'), message: 'Compile report should be JSON serializable.');
assertSame(expected: $compileReport?->fingerprint, actual: $runtimeReport->compiled?->fingerprint, message: 'Runtime report should point to the same compiled artifact report.');
assertTrue(condition: $runtimeReport->compiledAttached || $runtimeReport->warmedUp, message: 'Runtime report should expose compiled runtime state.');
assertSame(expected: 4, actual: $runtimeReport->toArray()['schemaVersion'] ?? null, message: 'Runtime reports should expose a stable JSON schema version.');
assertSame(expected: CreateContainerConfig::EXECUTION_MODE_COMPILED, actual: $runtimeReport->executionMode, message: 'Runtime reports should expose execution mode.');
assertSame(expected: CreateContainerConfig::ASYNC_TARGET_FPM, actual: $runtimeReport->asyncTarget, message: 'Runtime reports should expose async target posture.');
assertSame(expected: CreateContainerConfig::SLICE_BOUNDARY_MODE_STRICT, actual: $runtimeReport->sliceBoundaryMode, message: 'Runtime reports should expose slice boundary posture.');
assertTrue(condition: $runtimeReport->timelineEnabled === false, message: 'Minimal diagnostics mode should disable timeline recording.');
assertSame(expected: 0, actual: $runtimeReport->sharedServiceCount, message: 'Runtime report should summarize shared service counts without inventing unresolved shared instances.');
assertSame(expected: 1, actual: $runtimeReport->scopedServiceCount, message: 'Runtime report should summarize scoped service counts.');
assertSame(expected: 'fresh', actual: $runtimeReport->hotPath['freshnessState'], message: 'Runtime report should expose hot-path artifact freshness.');
assertTrue(condition: str_contains(haystack: (string) $runtimeReport->toJson(), needle: '"metrics"'), message: 'Runtime report should be JSON serializable.');
assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, actual: $runtimeReport->diagnosticsMode, message: 'Runtime report should expose the active diagnostics mode.');
assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL, actual: $description['diagnosticsMode'], message: 'Service diagnostics should expose the active diagnostics mode.');
assertSame(expected: ['diagnostics.service', DiagnosticsContract::class], actual: $aliasDescription['aliasChain'], message: 'Alias diagnostics should expose the alias expansion chain.');
assertSame(expected: [], actual: $description['decorationChain'], message: 'Undecorated services should expose an empty decoration chain.');
assertTrue(condition: isset($description['cacheState']['cached']), message: 'Service diagnostics should expose cache state.');
assertTrue(condition: isset($description['compiledState']['artifactAvailable']), message: 'Service diagnostics should expose compiled state.');
assertSame(expected: 'compiled', actual: $description['compiledState']['decision'], message: 'Compiled diagnostics should explain when the compiled path is usable.');
assertSame(
    expected: ['diagnostics.service', DiagnosticsContract::class],
    actual  : $aliasDescription['explain']['aliasExpansion']['chain'],
    message : 'Explain diagnostics should expose alias expansion chains.',
);
assertTrue(
    condition: isset($description['explain']['dependencyChain']['dependencies']),
    message  : 'Explain diagnostics should expose dependency chain output.',
);
assertSame(
    expected: 'no contextual override is registered for this service',
    actual  : $description['explain']['contextualWinner']['reason'],
    message : 'Explain diagnostics should expose contextual winner output even when there is no active contextual override.',
);
assertSame(
    expected: 'compiled hot path is attached and usable',
    actual  : $description['explain']['compiled']['reason'],
    message : 'Explain diagnostics should expose why the compiled path was selected.',
);
assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_COMPILED,
    actual  : $description['compiledState']['executionMode'],
    message : 'Service diagnostics should expose compiled execution mode.',
);
assertSame(
    expected: 'service will resolve through a fresh build path and then enter lifetime storage if needed',
    actual  : $description['explain']['cache']['reason'],
    message : 'Explain diagnostics should expose cache hit and miss reasoning.',
);
assertSame(expected: [], actual: $description['explain']['failureChain'], message: 'Healthy diagnostics should expose an empty failure chain.');
assertTrue(condition: $tags['ordered'], message: 'Debug tags should declare deterministic ordering.');
assertSame(expected: 1, actual: $scope['depth'], message: 'Debug scope should expose the active scope depth.');
assertSame(expected: [], actual: $plan['methods'], message: 'Debug plans should expose a simple constructor-only service.');
assertTrue(condition: isset($aliases['diagnostics.service']), message: 'Debug aliases should expose alias mappings.');
assertSame(expected: 1, actual: count(value: $tags['ids']), message: 'Debug tags should expose tagged service ids.');
assertSame(expected: DiagnosticsContract::class, actual: $selection['service'] ?? null, message: 'Selection diagnostics should expose the resolved service id.');
assertSame(expected: 'balanced', actual: $governance['profile'] ?? null, message: 'Governance diagnostics should expose the active policy profile.');
assertTrue(condition: isset($architecture['structuralDrift']), message: 'Architecture diagnostics should expose structural drift output.');
assertTrue(condition: $scope['scoped'] !== [], message: 'Debug scope should expose active scoped instances.');
assertSame(expected: 'from-context', actual: $contextual->name, message: 'Context views should feed scalar constructor values into compiled services.');
assertSame(expected: 'from-call', actual: $called, message: 'Context views should feed scalar callable arguments.');
assertSame(expected: 'from-injection', actual: $injected->name, message: 'Context views should feed scalar injection arguments.');
$container->closeScope();
assertTrue(condition: str_contains(haystack: (string) $container->exportMetrics(), needle: 'container_scope_open_total'), message: 'Scope lifecycle metrics should be exported.');
assertTrue(condition: str_contains(haystack: (string) $container->exportMetrics(), needle: 'container_scope_close_total'), message: 'Scope lifecycle metrics should be exported.');

$lazyProxy = $container->lazy(abstract: DiagnosticsContract::class);
assertSame(expected: 'diagnostics', actual: $lazyProxy->label(), message: 'Lazy proxies should still resolve the target service.');
assertTrue(condition: $container->isLazy(id: DiagnosticsContract::class), message: 'Public lazy status helpers should reflect lazy proxy usage.');

$detailedContainer = makeTestContainer(config: CreateContainerConfig::create(
    debug          : true,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED,
));
$detailedContainer->get(id: DiagnosticsService::class);
$detailedReport = $detailedContainer->runtimeReport();

assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED, actual: $detailedReport->diagnosticsMode, message: 'Detailed runtime reports should expose detailed diagnostics mode.');
assertTrue(condition: $detailedReport->timeline !== [], message: 'Detailed diagnostics mode should keep timeline events enabled.');
assertTrue(condition: $detailedReport->timelineEnabled, message: 'Detailed diagnostics mode should report timeline state as enabled.');

$ciContainer = makeTestContainer(config: CreateContainerConfig::create(
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI,
));
$ciContainer->get(id: DiagnosticsService::class);
$ciReport = $ciContainer->runtimeReport();

assertSame(expected: CreateContainerConfig::DIAGNOSTICS_MODE_CI, actual: $ciReport->diagnosticsMode, message: 'CI diagnostics mode should be preserved in runtime reports.');
assertTrue(condition: $ciReport->timelineEnabled, message: 'CI diagnostics mode should keep timeline recording enabled.');

echo basename(path: __FILE__) . " ok\n";
