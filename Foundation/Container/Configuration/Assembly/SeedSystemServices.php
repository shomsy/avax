<?php

declare(strict_types=1);

namespace Avax\Container\Configuration\Assembly;

use Avax\Container\Configuration\ContainerSettings;
use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Container;
use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistryInterface;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolutionPolicy;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\DependencyInjection\Injection\Invocation\FunctionCaller;
use Avax\Container\DependencyInjection\Scopes\ManageScopes;
use Avax\Container\DependencyInjection\Scopes\ScopeInterface;
use Avax\Container\DependencyInjection\Scopes\ScopeStore;
use Avax\Container\Observability\ResolutionMetrics;
use Avax\Container\Observability\ResolutionTelemetry;
use Avax\Container\Observability\ResolutionTimeline;
use Avax\Container\Runtime\ServicePool;
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
        $registrations->bootstrapInstance(abstract: \Avax\Container\Foundation\Time\Clock::class, instance: $observability->clock);
    }
}
