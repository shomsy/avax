<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Providers;

use Avax\Container\DependencyInjection\Dependencies\Bindings\ServiceRegistry;
use Avax\Container\Errors\ContainerException;
use Avax\Container\Observability\ResolutionMetrics;

/**
 * Owns deferred provider ownership and lazy boot lifecycle.
 */
final class DeferredProviderRegistry
{
    /** @var array<class-string<ServiceProviderInterface>, ServiceProviderInterface> */
    private array $providers = [];

    /** @var array<string, class-string<ServiceProviderInterface>> */
    private array $serviceOwners = [];

    /** @var array<class-string<ServiceProviderInterface>, true> */
    private array $bootedProviders = [];

    public function register(ServiceProviderInterface $provider, array $serviceIds, ServiceRegistry $registrations, ResolutionMetrics|null $metrics = null) : void
    {
        $providerClass = $provider::class;
        $ids = array_values(array_unique(array_filter(
            array_map(
                fn(string $serviceId) : string => $registrations->resolveAlias(abstract: $serviceId),
                $serviceIds
            ),
            static fn(string $serviceId) : bool => $serviceId !== ''
        )));

        sort($ids);

        if ($ids === []) {
            throw new ContainerException(message: "Deferred provider [{$providerClass}] must declare at least one provided service.");
        }

        $this->providers[$providerClass] = $provider;

        foreach ($ids as $serviceId) {
            $existing = $this->serviceOwners[$serviceId] ?? null;
            if ($existing !== null && $existing !== $providerClass) {
                throw new ContainerException(
                    message: "Deferred provider conflict for service [{$serviceId}] between [{$existing}] and [{$providerClass}]."
                );
            }

            $this->serviceOwners[$serviceId] = $providerClass;
        }

        $metrics?->increment(name: 'container_provider_deferred_total');
        $metrics?->increment(name: 'container_provider_deferred_services_total', by: count($ids));
    }

    public function isDeferred(string $serviceId) : bool
    {
        return isset($this->serviceOwners[$serviceId]);
    }

    /**
     * @return array<string, string>
     */
    public function services() : array
    {
        $services = $this->serviceOwners;
        ksort($services);

        return $services;
    }

    public function ownerOf(string $serviceId) : string|null
    {
        return $this->serviceOwners[$serviceId] ?? null;
    }

    /**
     * @param list<string> $serviceIds
     */
    public function bootFor(array $serviceIds, ServiceRegistry $registrations, ResolutionMetrics|null $metrics = null) : void
    {
        foreach (array_values(array_unique($serviceIds)) as $serviceId) {
            $this->bootIfNeeded(
                serviceId     : $registrations->resolveAlias(abstract: $serviceId),
                metrics       : $metrics
            );
        }
    }

    public function bootIfNeeded(string $serviceId, ResolutionMetrics|null $metrics = null) : void
    {
        $providerClass = $this->serviceOwners[$serviceId] ?? null;
        if ($providerClass === null || isset($this->bootedProviders[$providerClass])) {
            return;
        }

        $this->bootProvider(providerClass: $providerClass, metrics: $metrics);
    }

    /**
     * @param class-string<ServiceProviderInterface> $providerClass
     */
    private function bootProvider(string $providerClass, ResolutionMetrics|null $metrics = null) : void
    {
        $provider = $this->providers[$providerClass] ?? null;
        if (! $provider instanceof ServiceProviderInterface) {
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
}
