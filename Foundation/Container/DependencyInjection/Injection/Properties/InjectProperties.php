<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection\Properties;

use Avax\Container\Errors\ContainerException;
use Avax\Container\DependencyInjection\Injection\Attributes\Inject;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

final class InjectProperties
{
    /**
     * @param array<string, mixed> $overrides
     */
    public function inject(
        object $target,
        ServiceBlueprint $blueprint,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest $request
    ) : void {
        foreach ($blueprint->injectableProperties as $property) {
            $name = $property->getName();
            $property->setAccessible(true);

            if ($property->isReadOnly()) {
                throw new ContainerException(message: "Cannot inject readonly property [{$name}] on [{$blueprint->class}].");
            }

            if (array_key_exists($name, $overrides)) {
                $property->setValue($target, $overrides[$name]);
                continue;
            }

            $serviceId = $this->serviceIdFor(property: $property);
            if ($serviceId === null) {
                continue;
            }

            $property->setValue(
                $target,
                $resolver->resolveRequest(request: $request->child(serviceId: $serviceId))
            );
        }
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
}
