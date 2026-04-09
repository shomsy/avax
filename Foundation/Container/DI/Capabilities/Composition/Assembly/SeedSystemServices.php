<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Composition\Assembly;

use Avax\Container\DI\Capabilities\Composition\ContainerSettings;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Container;
use Avax\Container\DI\ContainerInterface;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistry;
use Avax\Container\DI\Capabilities\Declaration\Bindings\ServiceRegistryInterface;
use Avax\Container\DI\Capabilities\Resolution\ResolutionPolicy;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\FunctionCaller;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ManageScopes;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeInterface;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeStore;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTelemetry;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionTimeline;
use Avax\Container\DI\Capabilities\Runtime\ServicePool;
use Avax\Container\DI\Foundation\Time\Clock;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Seeds the container-owned system services into the registration store.
 */
final class SeedSystemServices
{
    public function seed(
        RuntimeAssembly $runtime,
        ObservabilityAssembly $observability,
        Container $container,
        CreateContainerConfig $config,
        ResolutionTelemetry $telemetry
    ) : void {
        $settings = new ContainerSettings(items: $config->settings);
        $registrations = $runtime->registrations;

        $registrations->bootstrapInstance(abstract: PsrContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ContainerInterface::class, instance: $container);
        $registrations->bootstrapInstance(abstract: Container::class, instance: $container);
        $registrations->bootstrapInstance(abstract: ServiceResolver::class, instance: $runtime->resolver);
        $registrations->bootstrapInstance(abstract: ServiceRegistryInterface::class, instance: $registrations);
        $registrations->bootstrapInstance(abstract: ScopeInterface::class, instance: $runtime->scopes);
        $registrations->bootstrapInstance(abstract: ManageScopes::class, instance: $runtime->scopes);
        $registrations->bootstrapInstance(abstract: ScopeStore::class, instance: $runtime->scopeStore);
        $registrations->bootstrapInstance(abstract: ServicePool::class, instance: $runtime->servicePool);
        $registrations->bootstrapInstance(abstract: ServiceRegistry::class, instance: $registrations);
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
