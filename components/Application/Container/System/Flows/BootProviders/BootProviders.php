<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\BootProviders;

use Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\ContainerObservability\Observability\ResolutionMetrics;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\ProviderBootPlan;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDeferredDependency;
use Avax\Components\Application\Container\System\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\ContainerInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Deterministic provider lifecycle flow: register first, then boot.
 */
final readonly class BootProviders
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @param  array<int, string|RegisterDependency>  $providers
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    public function boot(array $providers): void
    {
        $instances = $this->resolveProviders(providers: $providers);
        $providerBootPlan = ProviderBootPlan::build(instances: $instances);
        $ordered = $providerBootPlan->orderedInstances(instances: $instances);
        $eagerProviders = $this->eagerProviderClasses(instances: $instances, plan: $providerBootPlan);
        $metrics = $this->metrics();
        $resolver = $this->resolver();

        $metrics?->increment(name: 'container_provider_plan_total');
        $metrics?->increment(name: 'container_provider_plan_entries_total', by: count(value: $providerBootPlan->order));

        foreach ($ordered as $provider) {
            if (! in_array(needle: $provider::class, haystack: $eagerProviders, strict: true)) {
                if (! $resolver instanceof ResolveDependency) {
                    throw new InvalidArgumentException(message: 'Deferred providers require an available ResolveDependency.');
                }

                $resolver->registerDeferredProvider(
                    provider  : $provider,
                    serviceIds: $this->providedServices(provider: $provider),
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
     * @param  array<int, string|RegisterDependency>  $providers
     * @return array<class-string<RegisterDependency>, RegisterDependency>
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    private function resolveProviders(array $providers): array
    {
        $instances = [];

        foreach ($providers as $provider) {
            $instance = $this->instanceFor(provider: $provider);
            $instances[$instance::class] = $instance;
        }

        $queue = array_values(array: $instances);
        while ($queue !== []) {
            $provider = array_shift(array: $queue);
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
     * @param  array<int, string|RegisterDependency>  $providers
     *
     * @throws InvalidArgumentException
     * @throws ContainerException
     */
    private function instanceFor(string|RegisterDependency $provider): RegisterDependency
    {
        if (is_string(value: $provider)) {
            if (! class_exists(class: $provider)) {
                throw new InvalidArgumentException(message: sprintf('Provider class [%s] does not exist.', $provider));
            }

            $instance = $this->container->make(abstract: $provider);
        } else {
            $instance = $provider;
        }

        if (! $instance instanceof RegisterDependency) {
            throw new InvalidArgumentException(message: 'Provider must implement RegisterDependency.');
        }

        return $instance;
    }

    /**
     * @param  array<class-string<RegisterDependency>, RegisterDependency>  $instances
     * @return list<class-string<RegisterDependency>>
     */
    private function eagerProviderClasses(ProviderBootPlan $providerBootPlan, array $instances): array
    {
        $eager = [];

        foreach ($instances as $class => $provider) {
            if ($this->isDeferredProvider(provider: $provider)) {
                continue;
            }

            $eager[$class] = true;
            $this->markDependenciesAsEager(
                class       : $class,
                dependencies: $providerBootPlan->dependencies,
                eager       : $eager,
            );
        }

        $classes = array_keys(array: $eager);
        sort(array: $classes);

        return $classes;
    }

    private function isDeferredProvider(RegisterDependency $registerDependency): bool
    {
        return $registerDependency instanceof RegisterDeferredDependency
            && $registerDependency->deferred();
    }

    /**
     * @param  array<class-string<RegisterDependency>, list<class-string<RegisterDependency>>>  $dependencies
     * @param  array<class-string<RegisterDependency>, true>  $eager
     */
    private function markDependenciesAsEager(string $class, array $dependencies, array &$eager): void
    {
        foreach ($dependencies[$class] ?? [] as $dependencyClass) {
            if (isset($eager[$dependencyClass])) {
                continue;
            }

            $eager[$dependencyClass] = true;
            $this->markDependenciesAsEager(
                class       : $dependencyClass,
                dependencies: $dependencies,
                eager       : $eager,
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
    private function resolver() : ResolveDependency|null
    {
        try {
            $resolver = $this->container->get(id: ResolveDependency::class);
        } catch (Throwable) {
            return null;
        }

        return $resolver instanceof ResolveDependency ? $resolver : null;
    }

    /**
     * Returns the service ids owned by one deferred provider.
     *
     * @return list<string>
     */
    private function providedServices(RegisterDependency $registerDependency): array
    {
        if (! $registerDependency instanceof RegisterDeferredDependency) {
            return [];
        }

        $services = $registerDependency->provides();
        sort(array: $services);

        return array_values(array: array_unique(array: $services));
    }
}
