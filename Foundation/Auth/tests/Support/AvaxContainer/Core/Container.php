<?php

declare(strict_types=1);

namespace Avax\Container\Core;

use Closure;
use ReflectionClass;
use RuntimeException;

/**
 * Minimal test-only container used to verify the optional Avax adapter seam.
 */
final class Container
{
    /** @var array<string, array{implementation: mixed, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    public function has(string $id) : bool
    {
        return array_key_exists($id, $this->instances)
            || array_key_exists($id, $this->bindings);
    }

    public function get(string $id) : mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            $binding = $this->bindings[$id];
            $resolved = $this->resolve($binding['implementation']);

            if ($binding['shared']) {
                $this->instances[$id] = $resolved;
            }

            return $resolved;
        }

        if (class_exists($id)) {
            return $this->build($id);
        }

        throw new RuntimeException("Container binding [$id] is missing.");
    }

    public function singleton(string $id, mixed $implementation = null) : void
    {
        $this->bindings[$id] = [
            'implementation' => $implementation ?? $id,
            'shared'         => true,
        ];
    }

    public function instance(string $id, mixed $implementation) : void
    {
        $this->instances[$id] = $implementation;
    }

    private function resolve(mixed $implementation) : mixed
    {
        return match (true) {
            $implementation instanceof Closure => $implementation(),
            is_string($implementation) => $this->build($implementation),
            default => $implementation,
        };
    }

    private function build(string $class) : object
    {
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class [$class] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type !== null && ! $type->isBuiltin()) {
                $arguments[] = $this->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException("Container cannot resolve parameter [{$parameter->getName()}] for [$class].");
        }

        return $reflection->newInstanceArgs($arguments);
    }
}
