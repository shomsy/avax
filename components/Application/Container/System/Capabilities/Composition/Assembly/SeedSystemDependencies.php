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
        RuntimeAssembly     $runtime,
        ObservabilityAssembly $observability,
        Container           $container,
        CreateContainerConfig $config,
        ResolutionTelemetry $telemetry,
    ) : void
    {
        $settings      = new ContainerSettings(items: $config->settings);
        $registrations = $runtime->registrations;

        $registrations->bootstrapInstance(abstract: PsrContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: Container::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ResolveDependency::class, instance: $runtime->resolver);
        $registrations->bootstrapInstance(abstract: DependencyRegistryContract::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: ScopeInterface::class, instance: $runtime->scopes);
        $registrations->bootstrapInstance(abstract: ManageScopes::class, instance: $runtime->scopes);
        $registrations->bootstrapInstance(abstract: ScopeStore::class, instance: $runtime->scopeStore);
        $registrations->bootstrapInstance(abstract: DependencyPool::class, instance: $runtime->servicePool);
        $registrations->bootstrapInstance(abstract: DependencyRegistry::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: CreateContainerConfig::class, instance: $config);
        $registrations->bootstrapInstance(abstract: ContainerSettings::class, instance: $settings);
        $registrations->bootstrapInstance(abstract: ResolutionPolicy::class, instance: $runtime->policy);
        $registrations->bootstrapInstance(abstract: FunctionCaller::class, instance: $runtime->caller);
        $registrations->bootstrapInstance(abstract: ResolutionMetrics::class, instance: $observability->metrics);
        $registrations->bootstrapInstance(abstract: ResolutionTimeline::class, instance: $observability->timeline);
        $registrations->bootstrapInstance(abstract: ResolutionTelemetry::class, instance: $telemetry);
        $registrations->bootstrapInstance(abstract: Clock::class, instance: $observability->clock);
    }
}
