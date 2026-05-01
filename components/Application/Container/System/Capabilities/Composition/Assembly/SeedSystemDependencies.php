<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Composition\Assembly;

use Avax\Components\Application\Container\System\Capabilities\Composition\ContainerSettings;
use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistryContract;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTelemetry;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolutionPolicy;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Runtime\DependencyPool;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeInterface;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Components\Application\Container\System\Container;
use Avax\Components\Application\Container\System\ContainerInterface;
use Avax\Components\Application\Container\System\Foundation\Time\Clock;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Seeds the container-owned system services into the registration store.
 */
final class SeedSystemDependencies
{
    public function seed(
        RuntimeAssembly       $runtimeAssembly,
        ObservabilityAssembly $observabilityAssembly,
        Container $container,
        CreateContainerConfig $createContainerConfig,
        ResolutionTelemetry   $resolutionTelemetry,
    ) : void
    {
        $containerSettings = new ContainerSettings(items: $createContainerConfig->settings);
        $registrations     = $runtimeAssembly->registrations;

        $registrations->bootstrapInstance(abstract: PsrContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: Container::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ResolveDependency::class, instance: $runtimeAssembly->resolver);
        $registrations->bootstrapInstance(abstract: DependencyRegistryContract::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: ScopeInterface::class, instance: $runtimeAssembly->scopes);
        $registrations->bootstrapInstance(abstract: ManageScopes::class, instance: $runtimeAssembly->scopes);
        $registrations->bootstrapInstance(abstract: ScopeStore::class, instance: $runtimeAssembly->scopeStore);
        $registrations->bootstrapInstance(abstract: DependencyPool::class, instance: $runtimeAssembly->servicePool);
        $registrations->bootstrapInstance(abstract: DependencyRegistry::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: CreateContainerConfig::class, instance: $createContainerConfig);
        $registrations->bootstrapInstance(abstract: ContainerSettings::class, instance: $containerSettings);
        $registrations->bootstrapInstance(abstract: ResolutionPolicy::class, instance: $runtimeAssembly->policy);
        $registrations->bootstrapInstance(abstract: FunctionCaller::class, instance: $runtimeAssembly->caller);
        $registrations->bootstrapInstance(abstract: ResolutionMetrics::class, instance: $observabilityAssembly->metrics);
        $registrations->bootstrapInstance(abstract: ResolutionTimeline::class, instance: $observabilityAssembly->timeline);
        $registrations->bootstrapInstance(abstract: ResolutionTelemetry::class, instance: $resolutionTelemetry);
        $registrations->bootstrapInstance(abstract: Clock::class, instance: $observabilityAssembly->clock);
    }
}
