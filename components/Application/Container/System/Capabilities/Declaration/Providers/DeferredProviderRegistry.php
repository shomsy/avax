<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Providers;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;

/**
 * Owns deferred provider ownership and lazy boot lifecycle.
 */
final class DeferredProviderRegistry
{
    /** @var array<class-string<RegisterDependency>, RegisterDependency> */
    private array $providers = [];

    /** @var array<string, class-string<RegisterDependency>> */
    private array $serviceOwners = [];

    /** @var array<class-string<RegisterDependency>, true> */
    private array $bootedProviders = [];

    public function isDeferred(string $serviceId): bool
    {
        return isset($this->serviceOwners[$serviceId]);
    }

    /**
     * @return array<string, string>
     */
    public function services(): array
    {
        $services = $this->serviceOwners;
        ksort(array: $services);

        return $services;
    }

    public function ownerOf(string $serviceId): ?string
    {
        return $this->serviceOwners[$serviceId] ?? null;
    }

    /**
     * @param list<string> $serviceIds
     */
    public function bootFor(array $serviceIds, DependencyRegistry $registrations, ResolutionMetrics $metrics = null): void
    {
        foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
            $this->bootIfNeeded(
                serviceId: $registrations->resolveAlias(abstract: $serviceId),
                metrics  : $metrics,
            );
        }
    }

    public function bootIfNeeded(string $serviceId, ResolutionMetrics $metrics = null): void
    {
        $providerClass = $this->serviceOwners[$serviceId] ?? null;
        if ($providerClass === null || isset($this->bootedProviders[$providerClass])) {
            return;
        }

        $this->bootProvider(providerClass: $providerClass, metrics: $metrics);
    }

    /**
     * @param class-string<RegisterDependency> $providerClass
     */
    private function bootProvider(string $providerClass, ResolutionMetrics $metrics = null): void
    {
        $provider = $this->providers[$providerClass] ?? null;
        if (! $provider instanceof RegisterDependency) {
            throw new ContainerException(message: "Deferred provider [{$providerClass}] is not registered.");
        }

        foreach ($provider->dependsOn() as $dependencyClass) {
            if (isset($this->providers[$dependencyClass]) && ! isset($this->bootedProviders[$dependencyClass])) {
                $this->bootProvider(providerClass: $dependencyClass, metrics: $metrics);
            }
        }

        $provider->register();
        $metrics?->increment(name: 'container_provider_register_total');
        $provider->boot();
        $metrics?->increment(name: 'container_provider_boot_total');
        $metrics?->increment(name: 'container_provider_deferred_boot_total');
        $this->bootedProviders[$providerClass] = true;
    }

    public function register(RegisterDependency $provider, array $serviceIds, DependencyRegistry $registrations, ResolutionMetrics $metrics = null): void
    {
        $providerClass = $provider::class;
        $ids           = array_map(
            callback: static fn (string $serviceId): string => $registrations->resolveAlias(abstract: $serviceId),
            array   : $serviceIds,
        )
                |> (static fn ($x) => array_filter(array: $x, callback: static fn (string $serviceId): bool => $serviceId !== ''))
                |> array_unique(...)
                |> array_values(...);

        sort(array: $ids);

        if ($ids === []) {
            throw new ContainerException(message: "Deferred provider [{$providerClass}] must declare at least one provided service.");
        }

        $this->providers[$providerClass] = $provider;

        foreach ($ids as $serviceId) {
            $existing = $this->serviceOwners[$serviceId] ?? null;
            if ($existing !== null && $existing !== $providerClass) {
                throw new ContainerException(
                    message: "Deferred provider conflict for service [{$serviceId}] between [{$existing}] and [{$providerClass}].",
                );
            }

            $this->serviceOwners[$serviceId] = $providerClass;
        }

        $metrics?->increment(name: 'container_provider_deferred_total');
        $metrics?->increment(name: 'container_provider_deferred_services_total', by: count(value: $ids));
    }
}
