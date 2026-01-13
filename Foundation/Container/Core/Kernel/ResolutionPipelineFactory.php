<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Steps\AnalyzePrototypeStep;
use Avax\Container\Core\Kernel\Steps\ApplyExtendersStep;
use Avax\Container\Core\Kernel\Steps\CircularDependencyStep;
use Avax\Container\Core\Kernel\Steps\CollectDiagnosticsStep;
use Avax\Container\Core\Kernel\Steps\DepthGuardStep;
use Avax\Container\Core\Kernel\Steps\EnsureDefinitionExistsStep;
use Avax\Container\Core\Kernel\Steps\GuardPolicyStep;
use Avax\Container\Core\Kernel\Steps\InjectDependenciesStep;
use Avax\Container\Core\Kernel\Steps\InvokePostConstructStep;
use Avax\Container\Core\Kernel\Steps\ResolveInstanceStep;
use Avax\Container\Core\Kernel\Steps\RetrieveFromScopeStep;
use Avax\Container\Core\Kernel\Steps\StoreLifecycleStep;
use Avax\Container\Core\Kernel\Strategies\ScopedLifecycleStrategy;
use Avax\Container\Core\Kernel\Strategies\SingletonLifecycleStrategy;
use Avax\Container\Core\Kernel\Strategies\TransientLifecycleStrategy;
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Guard\Enforce\CompositeResolutionPolicy;
use Avax\Container\Guard\Enforce\GuardResolution;
use Avax\Container\Guard\Enforce\StrictResolutionPolicy;
use Avax\Container\Guard\Rules\ContainerPolicy;

/**
 * Resolution Pipeline Builder - Pipeline Assembly
 *
 * Orchestrates the creation and configuration of resolution pipelines from KernelConfig, ensuring proper component
 * initialization and sequencing.
 *
 * @see docs/Core/Kernel/ResolutionPipelineFactory.md#quick-summary
 */
final class ResolutionPipelineFactory
{
    /**
     * Create the default resolution pipeline from KernelConfig.
     *
     * Assembles a complete resolution pipeline with all necessary steps in the correct order,
     * configured according to the provided KernelConfig and DefinitionStore.
     *
     * @param KernelConfig    $config      The kernel configuration containing all collaborators
     * @param DefinitionStore $definitions The service definition repository
     *
     * @return ResolutionPipeline A fully configured resolution pipeline ready for execution
     *
     * @see docs/Core/Kernel/ResolutionPipelineFactory.md#method-defaultFromConfig
     */
    public static function defaultFromConfig(KernelConfig $config, DefinitionStore $definitions) : ResolutionPipeline
    {
        $basePolicy = new StrictResolutionPolicy(
            policy: $config->policy ?? new ContainerPolicy
        );

        // Uses Composite pattern for future extensibility
        $compositePolicy = new CompositeResolutionPolicy(policies: [$basePolicy]);

        $policy = new GuardResolution(policy: $compositePolicy);

        $telemetryCollector = new StepTelemetryRecorder;

        $lifecycleRegistry = new LifecycleStrategyRegistry(defaultStrategies: [
            'singleton' => new SingletonLifecycleStrategy(scopeManager: $config->scopes),
            'scoped'    => new ScopedLifecycleStrategy(scopeManager: $config->scopes),
            'transient' => new TransientLifecycleStrategy,
        ]);

        // Core pipeline steps in prioritized order
        $steps = [
            new RetrieveFromScopeStep(scopeManager: $config->scopes),
            new DepthGuardStep(maxDepth: 64),
            new CircularDependencyStep,
            new GuardPolicyStep(guard: $policy),
            new EnsureDefinitionExistsStep(definitions: $definitions, autoDefine: $config->autoDefine ?? false, strictMode: $config->strictMode),
            new AnalyzePrototypeStep(prototypeFactory: $config->prototypeFactory, strictMode: $config->strictMode),
            new ResolveInstanceStep(engine: $config->engine),
            new InjectDependenciesStep(injector: $config->injector),
            new ApplyExtendersStep(definitions: $definitions, scopes: $config->scopes),
            new InvokePostConstructStep(invoker: $config->invoker),
            new StoreLifecycleStep(lifecycleResolver: new LifecycleResolver(registry: $lifecycleRegistry)),
        ];

        // Conditional diagnostics
        if ($config->devMode) {
            $steps[] = new CollectDiagnosticsStep(telemetry: $telemetryCollector);
        }

        return new ResolutionPipeline(steps: $steps, telemetry: $telemetryCollector);
    }
}
