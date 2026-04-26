<?php

declare(strict_types=1);

namespace components\Container\DI\Flows\BootProviders;

use components\Container\DI\Capabilities\Declaration\Providers\DeferredProviderInterface;
use components\Container\DI\Capabilities\Declaration\Providers\ProviderBootPlan;
use components\Container\DI\Capabilities\Declaration\Providers\ServiceProviderInterface;
use components\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use components\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;
use components\Container\DI\Capabilities\Resolution\ServiceResolver;
use components\Container\DI\ContainerInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Deterministic provider lifecycle flow: register first, then boot.
 */
final readonly class BootProviders
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    public function boot(array $providers) : void
    {
        $instances      = $this->resolveProviders(providers: $providers);
        $plan           = ProviderBootPlan::build(instances: $instances);
        $ordered        = $plan->orderedInstances(instances: $instances);
        $eagerProviders = $this->eagerProviderClasses(plan: $plan, instances: $instances);
        $metrics        = $this->metrics();
        $resolver       = $this->resolver();

        $metrics?->increment(name: 'container_provider_plan_total');
        $metrics?->increment(name: 'container_provider_plan_entries_total', by: count(value: $plan->order));

        foreach ($ordered as $provider) {
            if (! in_array(needle: $provider::class, haystack: $eagerProviders, strict: true)) {
                if (! $resolver instanceof ServiceResolver) {
                    throw new InvalidArgumentException(message: 'Deferred providers require an available ServiceResolver.');
                }

                $resolver->registerDeferredProvider(
                    provider  : $provider,
                    serviceIds: $this->providedServices(provider: $provider)
                );
                continue;
            }

            $provider->register();
            $metrics?->increment(name: 'container_provider_register_total');
        }

        foreach ($ordered as $provider) {
            if (! in_array(needle: $provider::class, haystack: $eagerProviders, strict: true)) {
                continue;
            }

            $provider->boot();
            $metrics?->increment(name: 'container_provider_boot_total');
        }
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     *
     * @return array<class-string<ServiceProviderInterface>, ServiceProviderInterface>
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    private function resolveProviders(array $providers) : array
    {
        $instances = [];

        foreach ($providers as $provider) {
            $instance                    = $this->instanceFor(provider: $provider);
            $instances[$instance::class] = $instance;
        }

        $queue = array_values(array: $instances);
        while ( $queue !== [] ) {
            $provider = array_shift(array: $queue);
            foreach ($provider->dependsOn() as $dependencyClass) {
                if (isset($instances[$dependencyClass])) {
                    continue;
                }

                $dependency                    = $this->instanceFor(provider: $dependencyClass);
                $instances[$dependency::class] = $dependency;
                $queue[]                       = $dependency;
            }
        }

        return $instances;
    }

    /**
     * @param array<int, string|ServiceProviderInterface> $providers
     *
     * @return ServiceProviderInterface
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    private function instanceFor(string|ServiceProviderInterface $provider) : ServiceProviderInterface
    {
        if (is_string(value: $provider)) {
            if (! class_exists(class: $provider)) {
                throw new InvalidArgumentException(message: "Provider class [{$provider}] does not exist.");
            }

            $instance = $this->container->make(abstract: $provider);
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
     *
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

        $classes = array_keys(array: $eager);
        sort(array: $classes);

        return $classes;
    }

    private function isDeferredProvider(ServiceProviderInterface $provider) : bool
    {
        return $provider instanceof DeferredProviderInterface
            && $provider->deferred();
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, list<class-string<ServiceProviderInterface>>> $dependencies
     * @param array<class-string<ServiceProviderInterface>, true>                                         $eager
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

    /**
     * Returns the metrics system service when available.
     */
    private function metrics() : ResolutionMetrics|null
    {
        try {
            $metrics = $this->container->get(id: ResolutionMetrics::class);
        } catch (Throwable) {
            return null;
        }

        return $metrics instanceof ResolutionMetrics ? $metrics : null;
    }

    /**
     * Returns the resolver system service when available.
     */
    private function resolver() : ServiceResolver|null
    {
        try {
            $resolver = $this->container->get(id: ServiceResolver::class);
        } catch (Throwable) {
            return null;
        }

        return $resolver instanceof ServiceResolver ? $resolver : null;
    }

    /**
     * Returns the service ids owned by one deferred provider.
     *
     * @return list<string>
     */
    private function providedServices(ServiceProviderInterface $provider) : array
    {
        if (! $provider instanceof DeferredProviderInterface) {
            return [];
        }

        $services = $provider->provides();
        sort(array: $services);

        return array_values(array: array_unique(array: $services));
    }
}
