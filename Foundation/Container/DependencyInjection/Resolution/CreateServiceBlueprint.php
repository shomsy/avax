<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Resolution;

use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use Avax\Container\DependencyInjection\Scopes\Lifetimes\Attributes\Singleton;
use ReflectionClass;

final readonly class CreateServiceBlueprint
{
    private BlueprintCache $cache;

    public function __construct(BlueprintCache|null $cache = null)
    {
        $this->cache = $cache ?? new BlueprintCache;
    }

    public function createFor(string $class) : ServiceBlueprint
    {
        $cached = $this->cache->get(class: $class);
        if ($cached !== null) {
            return $cached;
        }

        $reflection = new ReflectionClass($class);

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
            constructor         : $reflection->getConstructor(),
            injectableProperties: $properties,
            injectableMethods   : $methods,
            shared              : $reflection->getAttributes(Singleton::class) !== []
        ));
    }
}
