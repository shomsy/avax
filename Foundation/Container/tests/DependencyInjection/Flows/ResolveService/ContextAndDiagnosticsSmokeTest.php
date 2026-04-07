<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\DependencyInjection\Injection\Attributes\Inject;

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
    public function __construct(public string $name)
    {
    }
}

final class DiagnosticsScopedService
{
    public function __construct(public string $id = 'scoped')
    {
    }
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

$config = CreateContainerConfig::create(settings: ['env' => [$envKey => 'test']]);
$container = makeTestContainer($config);

$container->singleton(DiagnosticsContract::class, DiagnosticsService::class);
$container->alias('diagnostics.service', DiagnosticsContract::class);
$container->tag(DiagnosticsContract::class, 'diagnostics');
$container->scoped(DiagnosticsScopedService::class, DiagnosticsScopedService::class);
$container->openScope();
$container->get(DiagnosticsScopedService::class);
$container->compileContainer([DiagnosticsContract::class, ContextualNameConsumer::class, DiagnosticsScopedService::class]);

$description = $container->describeService(DiagnosticsContract::class);
$plan = $container->debugPlan(DiagnosticsContract::class);
$tags = $container->debugTags('diagnostics');
$aliases = $container->debugAliases();
$scope = $container->debugScope();
$validated = $container->validate([DiagnosticsContract::class, ContextualNameConsumer::class]);
$contextual = $container->forContext(['name' => 'from-context'])->make(ContextualNameConsumer::class);
$called = $container->forContext(['name' => 'from-call'])->call(
    static fn(string $name) : string => $name
);
$injected = $container->forContext(['name' => 'from-injection'])->injectInto(new ContextualInjectionTarget());

assertSame('test', $container->env($envKey), 'Environment access should prefer configured env values.');
assertSame([], $validated, 'Validation should pass for explicitly checked services.');
assertSame(DiagnosticsService::class, $description['concrete'], 'Service descriptions should expose the registered service class.');
assertSame('diagnostics', $container->get(DiagnosticsContract::class)->label(), 'Resolved services should still behave normally.');
assertTrue($description['compiled'], 'Compiled services should be marked as such in diagnostics.');
assertSame([], $plan['methods'], 'Debug plans should expose a simple constructor-only service.');
assertTrue(isset($aliases['diagnostics.service']), 'Debug aliases should expose alias mappings.');
assertSame(1, count($tags['ids']), 'Debug tags should expose tagged service ids.');
assertTrue($scope['scoped'] !== [], 'Debug scope should expose active scoped instances.');
assertSame('from-context', $contextual->name, 'Context views should feed scalar constructor values into compiled services.');
assertSame('from-call', $called, 'Context views should feed scalar callable arguments.');
assertSame('from-injection', $injected->name, 'Context views should feed scalar injection arguments.');

echo basename(__FILE__) . " ok\n";
