<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Blueprints;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveDependencies;
use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\Attributes\Singleton;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

final readonly class CreateServiceBlueprint
{
    private BlueprintCache $cache;

    public function __construct(
        BlueprintCache|null $cache = null,
        private ResolveDependencies|null $dependencies = null
    )
    {
        $this->cache = $cache ?? new BlueprintCache;
        $this->dependencies ??= new ResolveDependencies;
    }

    public function createFor(string $class) : ServiceBlueprint
    {
        $fingerprint = $this->cache->shouldValidateSource()
            ? $this->cacheFingerprintFor(class: $class)
            : '';
        $cached = $this->cache->get(class: $class, fingerprint: $fingerprint);
        if ($cached !== null) {
            return $cached;
        }

        $reflection = new ReflectionClass($class);
        $fingerprint = $fingerprint !== '' ? $fingerprint : $this->cacheFingerprintForReflection(reflection: $reflection);

        $properties = array_values(array_filter(
            $reflection->getProperties(),
            static fn($property) => $property->getAttributes(Inject::class) !== [] && ! $property->isStatic()
        ));
        $methods = array_values(array_filter(
            $reflection->getMethods(),
            static fn($method) => $method->getAttributes(Inject::class) !== [] && ! $method->isStatic()
        ));

        return $this->cache->put(new ServiceBlueprint(
            class               : $class,
            instantiable        : $reflection->isInstantiable(),
            constructor         : $reflection->getConstructor() !== null
                ? $this->dependencies->createPlan(parameters: $reflection->getConstructor()->getParameters())
                : null,
            injectableProperties: array_map(
                fn(ReflectionProperty $property) => [
                    'name' => $property->getName(),
                    'serviceId' => $this->serviceIdFor(property: $property),
                    'readonly' => $property->isReadOnly(),
                ],
                $properties
            ),
            injectableMethods   : array_map(
                fn($method) => [
                    'name' => $method->getName(),
                    'plan' => $this->dependencies->createPlan(parameters: $method->getParameters()),
                ],
                $methods
            ),
            shared              : $reflection->getAttributes(Singleton::class) !== [],
            fingerprint         : $fingerprint
        ));
    }

    /**
     * @param list<string> $classes
     */
    public function warm(array $classes) : void
    {
        foreach (array_values(array_unique($classes)) as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $this->createFor(class: $class);
        }
    }

    public function forget(string $class) : void
    {
        $this->cache->forget(class: $class);
    }

    public function flush() : void
    {
        $this->cache->flush();
    }

    private function serviceIdFor(ReflectionProperty $property) : string|null
    {
        $attributes = $property->getAttributes(Inject::class);
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string($inject->abstract) && $inject->abstract !== '') {
                return $inject->abstract;
            }
        }

        $type = $property->getType();
        if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
            return $type->getName();
        }
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType instanceof ReflectionNamedType && ! $namedType->isBuiltin()) {
                    return $namedType->getName();
                }
            }
        }

        return null;
    }

    private function cacheFingerprintFor(string $class) : string
    {
        return $this->cacheFingerprintForReflection(reflection: new ReflectionClass($class));
    }

    private function cacheFingerprintForReflection(ReflectionClass $reflection) : string
    {
        $files = $this->filesFor(reflection: $reflection);
        $parts = [];

        foreach ($files as $file) {
            $timestamp = is_file($file) ? (string) filemtime($file) : 'missing';
            $parts[] = $file . ':' . $timestamp;
        }

        sort($parts);

        return sha1($reflection->getName() . '|' . implode('|', $parts));
    }

    /**
     * @return list<string>
     */
    private function filesFor(ReflectionClass $reflection) : array
    {
        $files = [];

        $classFile = $reflection->getFileName();
        if (is_string($classFile) && $classFile !== '') {
            $files[] = $classFile;
        }

        foreach (class_parents($reflection->getName()) ?: [] as $parent) {
            $parentReflection = new ReflectionClass($parent);
            $parentFile = $parentReflection->getFileName();
            if (is_string($parentFile) && $parentFile !== '') {
                $files[] = $parentFile;
            }
            $files = array_merge($files, $this->traitFilesFor(reflection: $parentReflection));
        }

        foreach (class_implements($reflection->getName()) ?: [] as $interface) {
            $interfaceReflection = new ReflectionClass($interface);
            $interfaceFile = $interfaceReflection->getFileName();
            if (is_string($interfaceFile) && $interfaceFile !== '') {
                $files[] = $interfaceFile;
            }
        }

        $files = array_merge($files, $this->traitFilesFor(reflection: $reflection));

        return array_values(array_unique($files));
    }

    /**
     * @return list<string>
     */
    private function traitFilesFor(ReflectionClass $reflection) : array
    {
        $files = [];

        foreach ($reflection->getTraits() as $trait) {
            $traitFile = $trait->getFileName();
            if (is_string($traitFile) && $traitFile !== '') {
                $files[] = $traitFile;
            }

            $files = array_merge($files, $this->traitFilesFor(reflection: $trait));
        }

        return array_values(array_unique($files));
    }
}
