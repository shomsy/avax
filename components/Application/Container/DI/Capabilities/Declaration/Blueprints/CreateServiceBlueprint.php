<?php

declare(strict_types=1);

namespace Avax\Components\Container\DI\Capabilities\Declaration\Blueprints;

use Avax\Components\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Container\DI\Capabilities\Resolution\ResolveDependencies;
use Avax\Components\Container\DI\Capabilities\Runtime\Scopes\Lifetimes\Attributes\Singleton;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * Creates and caches reflection-derived service blueprints.
 */
final readonly class CreateServiceBlueprint
{
    private BlueprintCache           $cache;
    private ResolveDependencies|null $dependencies;

    public function __construct(
        BlueprintCache|null      $cache = null,
        ResolveDependencies|null $dependencies = null
    )
    {
        $this->dependencies = $dependencies;
        $this->cache        = $cache ?? new BlueprintCache;
        $this->dependencies ??= new ResolveDependencies;
    }

    /**
     * @param list<string> $classes
     *
     * @throws ReflectionException
     */
    public function warm(array $classes) : void
    {
        foreach (array_values(array: array_unique(array: $classes)) as $class) {
            if (! class_exists(class: $class)) {
                continue;
            }

            $this->createFor(class: $class);
        }
    }

    /**
     * @throws ReflectionException
     */
    public function createFor(string $class) : ServiceBlueprint
    {
        $fingerprint = $this->cache->shouldValidateSource()
            ? $this->cacheFingerprintFor(class: $class)
            : '';
        $cached      = $this->cache->get(class: $class, fingerprint: $fingerprint);
        if ($cached !== null) {
            return $cached;
        }

        $reflection  = new ReflectionClass(objectOrClass: $class);
        $fingerprint = $fingerprint !== '' ? $fingerprint : $this->cacheFingerprintForReflection(reflection: $reflection);

        $properties = array_values(array: array_filter(
                                              array   : $reflection->getProperties(),
                                              callback: static fn ($property) => $property->getAttributes(name: Inject::class) !== [] && ! $property->isStatic()
                                          ));
        $methods    = array_values(array: array_filter(
                                              array   : $reflection->getMethods(),
                                              callback: static fn ($method) => $method->getAttributes(name: Inject::class) !== [] && ! $method->isStatic()
                                          ));

        return $this->cache->put(blueprint: new ServiceBlueprint(
                                                class               : $class,
                                                instantiable        : $reflection->isInstantiable(),
                                                constructor         : $reflection->getConstructor() !== null
                                                                          ? $this->dependencies->createPlan(parameters: $reflection->getConstructor()->getParameters())
                                                                          : null,
                                                injectableProperties: array_map(
                                                                          callback: fn (ReflectionProperty $property) => [
                                                                              'name'      => $property->getName(),
                                                                              'serviceId' => $this->serviceIdFor(property: $property),
                                                                              'readonly'  => $property->isReadOnly(),
                                                                          ],
                                                                          array   : $properties
                                                                      ),
                                                injectableMethods   : array_map(
                                                                          callback: fn (ReflectionMethod $method) => [
                                                                              'name' => $method->getName(),
                                                                              'plan' => $this->dependencies->createPlan(parameters: $method->getParameters()),
                                                                          ],
                                                                          array   : $methods
                                                                      ),
                                                shared              : $reflection->getAttributes(name: Singleton::class) !== [],
                                                fingerprint         : $fingerprint
                                            ));
    }

    /**
     * @throws ReflectionException
     */
    private function cacheFingerprintFor(string $class) : string
    {
        return $this->cacheFingerprintForReflection(reflection: new ReflectionClass(objectOrClass: $class));
    }

    /**
     * @throws ReflectionException
     */
    private function cacheFingerprintForReflection(ReflectionClass $reflection) : string
    {
        $files = $this->filesFor(reflection: $reflection);
        $parts = [];

        foreach ($files as $file) {
            $timestamp = is_file(filename: $file) ? (string) filemtime(filename: $file) : 'missing';
            $parts[]   = $file . ':' . $timestamp;
        }

        sort(array: $parts);

        return sha1(string: $reflection->getName() . '|' . implode(separator: '|', array: $parts));
    }

    /**
     * @return list<string>
     * @throws ReflectionException
     * @throws ReflectionException
     */
    private function filesFor(ReflectionClass $reflection) : array
    {
        $files = [];

        $classFile = $reflection->getFileName();
        if (is_string(value: $classFile) && $classFile !== '') {
            $files[] = $classFile;
        }

        foreach (class_parents(object_or_class: $reflection->getName()) ?: [] as $parent) {
            $parentReflection = new ReflectionClass(objectOrClass: $parent);
            $parentFile       = $parentReflection->getFileName();
            if (is_string(value: $parentFile) && $parentFile !== '') {
                $files[] = $parentFile;
            }
            $files = array_merge($files, $this->traitFilesFor(reflection: $parentReflection));
        }

        foreach (class_implements(object_or_class: $reflection->getName()) ?: [] as $interface) {
            $interfaceReflection = new ReflectionClass(objectOrClass: $interface);
            $interfaceFile       = $interfaceReflection->getFileName();
            if (is_string(value: $interfaceFile) && $interfaceFile !== '') {
                $files[] = $interfaceFile;
            }
        }

        $files = array_merge($files, $this->traitFilesFor(reflection: $reflection));

        return array_values(array: array_unique(array: $files));
    }

    /**
     * @return list<string>
     */
    private function traitFilesFor(ReflectionClass $reflection) : array
    {
        $files = [];

        foreach ($reflection->getTraits() as $trait) {
            $traitFile = $trait->getFileName();
            if (is_string(value: $traitFile) && $traitFile !== '') {
                $files[] = $traitFile;
            }

            $files = array_merge($files, $this->traitFilesFor(reflection: $trait));
        }

        return array_values(array: array_unique(array: $files));
    }

    /**
     * Infers one service id from the property attribute or object type.
     */
    private function serviceIdFor(ReflectionProperty $property) : string|null
    {
        $attributes = $property->getAttributes(name: Inject::class);
        if ($attributes !== []) {
            $inject = $attributes[0]->newInstance();
            if (is_string(value: $inject->abstract) && $inject->abstract !== '') {
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

    /**
     * Removes one cached blueprint.
     */
    public function forget(string $class) : void
    {
        $this->cache->forget(class: $class);
    }

    /**
     * Clears all cached blueprints.
     */
    public function flush() : void
    {
        $this->cache->flush();
    }
}
