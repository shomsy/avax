<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Properties;

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Blueprints\ServiceBlueprint;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Components\Application\Container\DI\Capabilities\Resolution\ServiceResolver;
use Closure;
use Throwable;

/**
 * Applies property injection from one compiled or reflected service blueprint.
 */
final class InjectProperties
{
    /** @var array<string, Closure(object, mixed): void> */
    private array $writers = [];

    /**
     * @param object               $target
     * @param ServiceBlueprint     $blueprint
     * @param array<string, mixed> $overrides
     * @param ServiceResolver      $resolver
     * @param ResolveRequest       $request
     *
     * @throws Throwable
     */
    public function inject(
        object           $target,
        ServiceBlueprint $blueprint,
        array            $overrides,
        ServiceResolver  $resolver,
        ResolveRequest   $request
    ) : void
    {
        foreach ($blueprint->injectableProperties as $property) {
            $name = $property['name'];

            if ($property['readonly']) {
                throw new ContainerException(message: "Cannot inject readonly property [{$name}] on [{$blueprint->class}].");
            }

            if (array_key_exists(key: $name, array: $overrides)) {
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
            closure : static function (object $target, mixed $value) use ($property) : void {
                $target->{$property} = $value;
            },
            newThis : null,
            newScope: $class
        );
    }
}
