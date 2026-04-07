<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline;

use Avax\Container\DependencyInjection\Capabilities\Definitions\Store\DefinitionStore;
use Avax\Container\DependencyInjection\Capabilities\Policies\CheckResolutionPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\CompositeResolutionPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\ContainerPolicy;
use Avax\Container\DependencyInjection\Capabilities\Policies\StrictResolutionPolicy;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\AnalyzePrototypeStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\ApplyExtendersStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\CircularDependencyStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\CollectDiagnosticsStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\DepthGuardStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\EnforcePolicyStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\EnsureDefinitionExistsStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\InjectDependenciesStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\InvokePostConstructStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\ResolveInstanceStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\RetrieveFromScopeStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Steps\StoreLifecycleStep;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Pipeline\Telemetry\StepTelemetryRecorder;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\LifecycleResolver;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\LifecycleStrategyRegistry;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\Strategies\ScopedLifecycleStrategy;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\Strategies\SingletonLifecycleStrategy;
use Avax\Container\DependencyInjection\Capabilities\Scopes\Lifetimes\Strategies\TransientLifecycleStrategy;
use Avax\Container\DependencyInjection\Configuration\KernelConfig;

/**
 * Assembles the ordered resolution pipeline from runtime configuration.
 */
final class ResolutionPipelineFactory
{
    public static function defaultFromConfig(KernelConfig $config, DefinitionStore $definitions) : ResolutionPipeline
    {
        $basePolicy  = new StrictResolutionPolicy(policy: $config->policy ?? new ContainerPolicy);
        $policyCheck = new CheckResolutionPolicy(
            policy: new CompositeResolutionPolicy(policies: [$basePolicy])
        );

        $telemetryCollector = new StepTelemetryRecorder;
        $lifecycleRegistry  = new LifecycleStrategyRegistry(defaultStrategies: [
            'singleton' => new SingletonLifecycleStrategy(scopeManager: $config->scopes),
            'scoped'    => new ScopedLifecycleStrategy(scopeManager: $config->scopes),
            'transient' => new TransientLifecycleStrategy,
        ]);

        $steps = [
            new RetrieveFromScopeStep(scopeManager: $config->scopes),
            new DepthGuardStep(maxDepth: 64),
            new CircularDependencyStep,
            new EnforcePolicyStep(check: $policyCheck),
            new EnsureDefinitionExistsStep(
                definitions: $definitions,
                autoDefine : $config->autoDefine,
                strictMode : $config->strictMode
            ),
            new AnalyzePrototypeStep(prototypeFactory: $config->prototypeFactory, strictMode: $config->strictMode),
            new ResolveInstanceStep(engine: $config->engine),
            new InjectDependenciesStep(injector: $config->injector),
            new ApplyExtendersStep(definitions: $definitions, scopes: $config->scopes),
            new InvokePostConstructStep(invoker: $config->invoker),
            new StoreLifecycleStep(lifecycleResolver: new LifecycleResolver(registry: $lifecycleRegistry)),
        ];

        if ($config->devMode) {
            $steps[] = new CollectDiagnosticsStep(telemetry: $telemetryCollector);
        }

        return new ResolutionPipeline(steps: $steps, telemetry: $telemetryCollector);
    }
}
