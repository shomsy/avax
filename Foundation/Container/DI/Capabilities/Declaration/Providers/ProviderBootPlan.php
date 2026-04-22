<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Providers;

use InvalidArgumentException;
use LogicException;

/**
 * Deterministic provider register/boot order.
 */
final readonly class ProviderBootPlan
{
    public array $dependencies;
    public array $order;

    /**
     * @param list<class-string<ServiceProviderInterface>>                                                $order
     * @param array<class-string<ServiceProviderInterface>, list<class-string<ServiceProviderInterface>>> $dependencies
     */
    private function __construct(
        array $order,
        array $dependencies
    )
    {
        $this->order        = $order;
        $this->dependencies = $dependencies;
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, ServiceProviderInterface> $instances
     */
    public static function build(array $instances) : self
    {
        ksort($instances);

        $dependencies = [];
        foreach ($instances as $class => $provider) {
            $dependencyClasses = $provider->dependsOn()
                    |> array_unique(...)
                    |> array_values(...);
            sort($dependencyClasses);
            $dependencies[$class] = $dependencyClasses;
        }

        $ordered = [];
        $state   = [];

        foreach (array_keys($instances) as $class) {
            self::visit(
                class       : $class,
                dependencies: $dependencies,
                ordered     : $ordered,
                state       : $state,
                stack       : []
            );
        }

        return new self(
            order       : $ordered,
            dependencies: $dependencies
        );
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, list<class-string<ServiceProviderInterface>>> $dependencies
     * @param list<class-string<ServiceProviderInterface>>                                                $ordered
     * @param array<class-string<ServiceProviderInterface>, string>                                       $state
     * @param list<class-string<ServiceProviderInterface>>                                                $stack
     */
    private static function visit(
        string $class,
        array  $dependencies,
        array  &$ordered,
        array  &$state,
        array  $stack
    ) : void
    {
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
        $stack[]       = $class;

        foreach ($dependencies[$class] ?? [] as $dependencyClass) {
            if (! isset($dependencies[$dependencyClass])) {
                throw new InvalidArgumentException(message: "Provider dependency [{$dependencyClass}] does not exist.");
            }

            self::visit(
                class       : $dependencyClass,
                dependencies: $dependencies,
                ordered     : $ordered,
                state       : $state,
                stack       : $stack
            );
        }

        $state[$class] = 'done';
        $ordered[]     = $class;
    }

    /**
     * @param array<class-string<ServiceProviderInterface>, ServiceProviderInterface> $instances
     *
     * @return list<ServiceProviderInterface>
     */
    public function orderedInstances(array $instances) : array
    {
        $ordered = [];

        foreach ($this->order as $class) {
            if (! isset($instances[$class])) {
                throw new InvalidArgumentException(message: "Provider [{$class}] is missing from the resolved provider set.");
            }

            $ordered[] = $instances[$class];
        }

        return $ordered;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return [
            'order'         => $this->order,
            'dependencies'  => $this->dependencies,
            'providerCount' => count($this->order),
        ];
    }
}
