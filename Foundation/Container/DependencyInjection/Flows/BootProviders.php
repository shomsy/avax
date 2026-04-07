<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;
use InvalidArgumentException;
use LogicException;

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
        $ordered = $this->orderProviders(instances: $instances);

        foreach ($ordered as $provider) {
            $provider->register();
        }

        foreach ($ordered as $provider) {
            $provider->boot();
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
     * @return list<ServiceProviderInterface>
     */
    private function orderProviders(array $instances) : array
    {
        $ordered = [];
        $state = [];

        foreach (array_keys($instances) as $class) {
            $this->visitProvider(
                class   : $class,
                instances: $instances,
                ordered : $ordered,
                state   : $state,
                stack   : []
            );
        }

        return $ordered;
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, ServiceProviderInterface> $instances
     * @param list<ServiceProviderInterface> $ordered
     * @param array<class-string<ServiceProviderInterface>, string> $state
     * @param list<class-string<ServiceProviderInterface>> $stack
     */
    private function visitProvider(
        string $class,
        array $instances,
        array &$ordered,
        array &$state,
        array $stack
    ) : void {
        $currentState = $state[$class] ?? 'new';
        if ($currentState === 'done') {
            return;
        }

        if ($currentState === 'visiting') {
            $stack[] = $class;
            throw new LogicException(
                message: 'Provider dependency cycle detected: ' . implode(' -> ', $stack)
            );
        }

        $state[$class] = 'visiting';
        $stack[] = $class;

        foreach ($instances[$class]->dependsOn() as $dependencyClass) {
            if (! isset($instances[$dependencyClass])) {
                throw new InvalidArgumentException(message: "Provider dependency [{$dependencyClass}] does not exist.");
            }

            $this->visitProvider(
                class    : $dependencyClass,
                instances: $instances,
                ordered  : $ordered,
                state    : $state,
                stack    : $stack
            );
        }

        $state[$class] = 'done';
        $ordered[] = $instances[$class];
    }
}
