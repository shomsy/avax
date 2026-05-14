<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Providers;

use Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Bindings\DependencyRegistry;

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

    public function ownerOf(string $serviceId) : string|null
    {
        return $this->serviceOwners[$serviceId] ?? null;
    }

    /**
     * @param  list<string>  $serviceIds
     */
    public function bootFor(array $serviceIds, DependencyRegistry $dependencyRegistry, ResolutionMetrics|null $resolutionMetrics = null) : void
    {
        foreach (array_values(array: array_unique(array: $serviceIds)) as $serviceId) {
            $this->bootIfNeeded(
                serviceId: $dependencyRegistry->resolveAlias(abstract: $serviceId),
                metrics  : $resolutionMetrics,
            );
        }
    }

    public function bootIfNeeded(string $serviceId, ResolutionMetrics|null $resolutionMetrics = null) : void
    {
        $providerClass = $this->serviceOwners[$serviceId] ?? null;
        if ($providerClass === null || isset($this->bootedProviders[$providerClass])) {
            return;
        }

        $this->bootProvider(providerClass: $providerClass, metrics: $resolutionMetrics);
    }

    /**
     * @param  class-string<RegisterDependency>  $providerClass
     */
    private function bootProvider(string $providerClass, ResolutionMetrics|null $resolutionMetrics = null) : void
    {
        $provider = $this->providers[$providerClass] ?? null;
        if (! $provider instanceof RegisterDependency) {
            throw new ContainerException(message: sprintf('Deferred provider [%s] is not registered.', $providerClass));
        }

        foreach ($provider->dependsOn() as $dependencyClass) {
            if (isset($this->providers[$dependencyClass]) && ! isset($this->bootedProviders[$dependencyClass])) {
                $this->bootProvider(providerClass: $dependencyClass, metrics: $resolutionMetrics);
            }
        }

        $provider->register();
        $resolutionMetrics?->increment(name: 'container_provider_register_total');
        $provider->boot();
        $resolutionMetrics?->increment(name: 'container_provider_boot_total');
        $resolutionMetrics?->increment(name: 'container_provider_deferred_boot_total');
        $this->bootedProviders[$providerClass] = true;
    }

    /**
 * @throws ContainerException
 */
public function register(RegisterDependency $registerDependency, array $serviceIds, DependencyRegistry $dependencyRegistry, ResolutionMetrics|null $resolutionMetrics = null) : void
    {
        $providerClass = $registerDependency::class;
        $ids = array_map(
            callback: static fn (string $serviceId): string => $dependencyRegistry->resolveAlias(abstract: $serviceId),
            array   : $serviceIds,
        )
                |> (static fn ($x): array => array_filter(array: $x, callback: static fn (string $serviceId): bool => $serviceId !== ''))
                |> array_unique(...)
                |> array_values(...);

        sort(array: $ids);

        if ($ids === []) {
            throw new ContainerException(message: sprintf('Deferred provider [%s] must declare at least one provided service.', $providerClass));
        }

        $this->providers[$providerClass] = $registerDependency;

        foreach ($ids as $id) {
            $existing = $this->serviceOwners[$id] ?? null;
            if ($existing !== null && $existing !== $providerClass) {
                throw new ContainerException(
                    message: sprintf('Deferred provider conflict for service [%s] between [%s] and [%s].', $id, $existing, $providerClass),
                );
            }

            $this->serviceOwners[$id] = $providerClass;
        }

        $resolutionMetrics?->increment(name: 'container_provider_deferred_total');
        $resolutionMetrics?->increment(name: 'container_provider_deferred_services_total', by: count(value: $ids));
    }
}
