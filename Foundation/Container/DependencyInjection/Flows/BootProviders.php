<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Providers\ProviderBootPlan;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Avax\Container\Observability\ResolutionMetrics;
use InvalidArgumentException;

/**
 * Deterministic provider lifecycle flow: register first, then boot.
 */
final readonly class BootProviders
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    /** @param array<int, string|ServiceProviderInterface> $providers */
    public function boot(array $providers) : void
    {
        $instances = $this->resolveProviders(providers: $providers);
        $plan = ProviderBootPlan::build(instances: $instances);
        $ordered = $plan->orderedInstances(instances: $instances);
        $eagerProviders = $this->eagerProviderClasses(plan: $plan, instances: $instances);
        $metrics = $this->metrics();
        $resolver = $this->resolver();

        $metrics?->increment(name: 'container_provider_plan_total');
        $metrics?->increment(name: 'container_provider_plan_entries_total', by: count($plan->order));

        foreach ($ordered as $provider) {
            if (! in_array($provider::class, $eagerProviders, true)) {
                if (! $resolver instanceof ServiceResolver) {
                    throw new InvalidArgumentException(message: 'Deferred providers require an available ServiceResolver.');
                }

                $resolver?->registerDeferredProvider(
                    provider  : $provider,
                    serviceIds: $this->providedServices(provider: $provider)
                );
                continue;
            }

            $provider->register();
            $metrics?->increment(name: 'container_provider_register_total');
        }

        foreach ($ordered as $provider) {
            if (! in_array($provider::class, $eagerProviders, true)) {
                continue;
            }

            $provider->boot();
            $metrics?->increment(name: 'container_provider_boot_total');
        }
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     * @return array<class-string<ServiceProviderInterface>, ServiceProviderInterface>
     */
    private function resolveProviders(array $providers) : array
    {
        $instances = [];

        foreach ($providers as $provider) {
            $instance = $this->instanceFor(provider: $provider);
            $instances[$instance::class] = $instance;
        }

        $queue = array_values($instances);
        while ($queue !== []) {
            $provider = array_shift($queue);
            foreach ($provider->dependsOn() as $dependencyClass) {
                if (isset($instances[$dependencyClass])) {
                    continue;
                }

                $dependency = $this->instanceFor(provider: $dependencyClass);
                $instances[$dependency::class] = $dependency;
                $queue[] = $dependency;
            }
        }

        return $instances;
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     * @return ServiceProviderInterface
     */
    private function instanceFor(string|ServiceProviderInterface $provider) : ServiceProviderInterface
    {
        if (is_string($provider)) {
            if (! class_exists($provider)) {
                throw new InvalidArgumentException(message: "Provider class [{$provider}] does not exist.");
            }

            $instance = new $provider($this->container);
        } else {
            $instance = $provider;
        }

        if (! $instance instanceof ServiceProviderInterface) {
            throw new InvalidArgumentException(message: 'Provider must implement ServiceProviderInterface.');
        }

        return $instance;
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, ServiceProviderInterface> $instances
     * @return list<class-string<ServiceProviderInterface>>
     */
    private function eagerProviderClasses(ProviderBootPlan $plan, array $instances) : array
    {
        $eager = [];

        foreach ($instances as $class => $provider) {
            if ($this->isDeferredProvider(provider: $provider)) {
                continue;
            }

            $eager[$class] = true;
            $this->markDependenciesAsEager(
                class       : $class,
                dependencies: $plan->dependencies,
                eager       : $eager
            );
        }

        $classes = array_keys($eager);
        sort($classes);

        return $classes;
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, list<class-string<ServiceProviderInterface>>> $dependencies
     * @param array<class-string<ServiceProviderInterface>, true> $eager
     */
    private function markDependenciesAsEager(string $class, array $dependencies, array &$eager) : void
    {
        foreach ($dependencies[$class] ?? [] as $dependencyClass) {
            if (isset($eager[$dependencyClass])) {
                continue;
            }

            $eager[$dependencyClass] = true;
            $this->markDependenciesAsEager(
                class       : $dependencyClass,
                dependencies: $dependencies,
                eager       : $eager
            );
        }
    }

    private function isDeferredProvider(ServiceProviderInterface $provider) : bool
    {
        if (! method_exists($provider, 'deferred')) {
            return false;
        }

        return (bool) $provider->deferred();
    }

    /**
     * @return list<string>
     */
    private function providedServices(ServiceProviderInterface $provider) : array
    {
        if (! method_exists($provider, 'provides')) {
            return [];
        }

        $services = array_values(array_unique(array_filter(
            array_map(
                static fn(mixed $serviceId) : string => is_string($serviceId) ? $serviceId : '',
                $provider->provides()
            ),
            static fn(string $serviceId) : bool => $serviceId !== ''
        )));

        sort($services);

        return $services;
    }

    private function resolver() : ServiceResolver|null
    {
        try {
            $resolver = $this->container->get(ServiceResolver::class);
        } catch (\Throwable) {
            return null;
        }

        return $resolver instanceof ServiceResolver ? $resolver : null;
    }

    private function metrics() : ResolutionMetrics|null
    {
        try {
            $metrics = $this->container->get(ResolutionMetrics::class);
        } catch (\Throwable) {
            return null;
        }

        return $metrics instanceof ResolutionMetrics ? $metrics : null;
    }
}
