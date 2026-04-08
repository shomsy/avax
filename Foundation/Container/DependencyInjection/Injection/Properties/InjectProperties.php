<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Injection\Properties;

use Avax\Container\Errors\ContainerException;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Dependencies\Blueprints\ServiceBlueprint;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;
use Closure;

/**
 * Applies property injection from one compiled or reflected service blueprint.
 */
final class InjectProperties
{
    /** @var array<string, Closure(object, mixed): void> */
    private array $writers = [];

    /**
     * @param array<string, mixed> $overrides
     * @throws ContainerException
     */
    public function inject(
        object $target,
        ServiceBlueprint $blueprint,
        array $overrides,
        ServiceResolver $resolver,
        ResolveRequest $request
    ) : void {
        foreach ($blueprint->injectableProperties as $property) {
            $name = $property['name'];

            if ($property['readonly']) {
                throw new ContainerException(message: "Cannot inject readonly property [{$name}] on [{$blueprint->class}].");
            }

            if (array_key_exists($name, $overrides)) {
                ($this->writerFor(class: $blueprint->class, property: $name))($target, $overrides[$name]);
                continue;
            }

            $serviceId = $property['serviceId'];
            if ($serviceId === null) {
                continue;
            }

            ($this->writerFor(class: $blueprint->class, property: $name))(
                $target,
                $resolver->resolveRequest(request: $request->child(serviceId: $serviceId))
            );
        }
    }

    /**
     * @return Closure(object, mixed): void
     */
    private function writerFor(string $class, string $property) : Closure
    {
        $key = $class . '::$' . $property;

        if (isset($this->writers[$key])) {
            return $this->writers[$key];
        }

        return $this->writers[$key] = Closure::bind(
            static function (object $target, mixed $value) use ($property) : void {
                $target->{$property} = $value;
            },
            null,
            $class
        );
    }
}
