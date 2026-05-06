<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Resolution;

use Avax\Components\Application\Container\System\Capabilities\Bindings\BindingRegistry;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;

/**
 * Resolver for services using reflection-based autowiring.
 */
final class ServiceResolver
{
    /** @var array<string, bool> Track resolution path to detect circular dependencies */
    private array $resolving = [];

    public function __construct(
        private readonly BindingRegistry $bindings
    ) {
    }

    public function call(callable $callback, array $parameters = []): mixed
    {
        if (is_string($callback) && str_contains($callback, '::')) {
            $callback = explode('::', $callback);
        }

        if (is_array($callback) && is_string($callback[0])) {
            $callback[0] = $this->resolve($callback[0]);
        }

        $reflector = $this->getCallReflector($callback);
        $dependencies = $this->resolveDependencies($reflector->getParameters(), $parameters);

        return call_user_func_array($callback, $dependencies);
    }

    public function resolve(string $abstract, array $parameters = []): mixed
    {
        $abstract = $this->bindings->resolveAlias($abstract);

        // 1. Check if already resolved (singleton/instance)
        if ($instance = $this->bindings->getInstance($abstract)) {
            return $instance;
        }

        // 2. Detect circular dependencies
        if (isset($this->resolving[$abstract])) {
            throw new RuntimeException("Circular dependency detected for service: {$abstract}");
        }

        $this->resolving[$abstract] = true;

        try {
            $binding = $this->bindings->getBinding($abstract);
            $concrete = $binding['concrete'] ?? $abstract;

            // 3. Resolve concrete
            if ($concrete instanceof Closure) {
                $object = $concrete($this);
            } elseif (is_object($concrete)) {
                $object = $concrete;
            } else {
                $object = $this->build($concrete, $parameters);
            }

            // 4. Handle shared/scoped storage
            if ($binding && ($binding['shared'] || $binding['scoped'])) {
                $this->bindings->instance($abstract, $object);
            }

            return $object;
        } finally {
            unset($this->resolving[$abstract]);
        }
    }

    private function build(string $concrete, array $parameters): object
    {
        if (! class_exists($concrete)) {
            throw new RuntimeException("Target class [{$concrete}] does not exist.");
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new RuntimeException("Target class [{$concrete}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $concrete();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters(), $parameters);

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveDependencies(array $parameters, array $overrides): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $overrides)) {
                $dependencies[] = $overrides[$name];

                continue;
            }

            $type = $parameter->getType();

            if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();

                    continue;
                }

                throw new RuntimeException("Cannot resolve parameter [\${$name}] of type ".($type ? $type->getName() : 'unknown'));
            }

            $dependencies[] = $this->resolve($type->getName());
        }

        return $dependencies;
    }

    private function getCallReflector(callable $callback): ReflectionFunctionAbstract
    {
        if (is_array($callback)) {
            return new ReflectionMethod($callback[0], $callback[1]);
        }

        return new ReflectionFunction($callback);
    }
}
