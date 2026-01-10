<?php

declare(strict_types=1);

namespace Avax\Container\Core\Kernel;

use Avax\Container\Core\Kernel\Steps\{
    AnalyzePrototypeStep,
    CircularDependencyStep,
    CollectDiagnosticsStep,
    DepthGuardStep,
    EnsureDefinitionExistsStep,
    GuardPolicyStep,
    InjectDependenciesStep,
    InvokePostConstructStep,
    ResolveInstanceStep,
    RetrieveFromScopeStep,
    StoreLifecycleStep,
    ApplyExtendersStep
};
use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Guard\Enforce\CompositeResolutionPolicy;
use Avax\Container\Guard\Enforce\GuardResolution;
use Avax\Container\Guard\Enforce\StrictResolutionPolicy;
use Avax\Container\Guard\Rules\ContainerPolicy;

/**
 * Resolution Pipeline Builder - Pipeline Assembly
 *
 * Orchestrates the creation and configuration of resolution pipelines from KernelConfig, ensuring proper component initialization and sequencing.
 *
 * @see docs/Core/Kernel/ResolutionPipelineBuilder.md#quick-summary
 */
final class ResolutionPipelineBuilder
{
    /**
     * Create the default resolution pipeline from KernelConfig.
     *
     * Assembles a complete resolution pipeline with all necessary steps in the correct order,
     * configured according to the provided KernelConfig and DefinitionStore.
     *
     * @param KernelConfig $config The kernel configuration containing all collaborators
     * @param DefinitionStore $definitions The service definition repository
     * @return ResolutionPipeline A fully configured resolution pipeline ready for execution
     * @see docs/Core/Kernel/ResolutionPipelineBuilder.md#method-defaultFromConfig
     */
    public static function defaultFromConfig(KernelConfig $config, DefinitionStore $definitions): ResolutionPipeline
    {
        $basePolicy = new StrictResolutionPolicy(
            policy: $config->policy ?? new ContainerPolicy()
        );

        // Uses Composite pattern for future extensibility
        $compositePolicy = new CompositeResolutionPolicy([$basePolicy]);

        $policy = new GuardResolution(policy: $compositePolicy);

        $telemetryCollector = new StepTelemetryCollector();

        $lifecycleRegistry = new LifecycleStrategyRegistry([
            'singleton' => new \Avax\Container\Core\Kernel\Strategies\SingletonLifecycleStrategy($config->scopes),
            'scoped'    => new \Avax\Container\Core\Kernel\Strategies\ScopedLifecycleStrategy($config->scopes),
            'transient' => new \Avax\Container\Core\Kernel\Strategies\TransientLifecycleStrategy(),
        ]);

        // Core pipeline steps in prioritized order
        $steps = [
            new RetrieveFromScopeStep($config->scopes),
            new DepthGuardStep(maxDepth: 64),
            new CircularDependencyStep(),
            new GuardPolicyStep($policy),
            new EnsureDefinitionExistsStep($definitions, $config->autoDefine ?? false, $config->strictMode),
            new AnalyzePrototypeStep($config->prototypeFactory, $config->strictMode),
            new ResolveInstanceStep($config->engine),
            new InjectDependenciesStep($config->injector),
            new ApplyExtendersStep($definitions, $config->scopes),
            new InvokePostConstructStep($config->invoker),
            new StoreLifecycleStep(new LifecycleResolver($lifecycleRegistry)),
        ];

        // Conditional diagnostics
        if ($config->devMode) {
            $steps[] = new CollectDiagnosticsStep($telemetryCollector);
        }

        return new ResolutionPipeline($steps, $telemetryCollector);
    }
}
