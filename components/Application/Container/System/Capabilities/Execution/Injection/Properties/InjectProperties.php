<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Properties;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\DependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Closure;
use Throwable;

/**
 * Applies property injection from one compiled or reflected service blueprint.
 */
final class InjectProperties
{
    /** @var array<string, Closure(object, mixed) : void> */
    private array $writers = [];

    /**
     * @param  array<string, mixed>  $overrides
     *
     * @throws Throwable
     */
    public function inject(
        object $target,
        DependencyBlueprint $dependencyBlueprint,
        array $overrides,
        ResolveDependency $resolveDependency,
        ResolveRequest $resolveRequest,
    ): void {
        foreach ($dependencyBlueprint->injectableProperties as $property) {
            $name = $property['name'];

            if ($property['readonly']) {
                throw new ContainerException(message: sprintf('Cannot inject readonly property [%s] on [%s].', $name, $dependencyBlueprint->class));
            }

            if (array_key_exists(key: $name, array: $overrides)) {
                ($this->writerFor(class: $dependencyBlueprint->class, property: $name))($target, $overrides[$name]);

                continue;
            }

            $serviceId = $property['serviceId'];
            if ($serviceId === null) {
                continue;
            }

            ($this->writerFor(class: $dependencyBlueprint->class, property: $name))(
                $target,
                $resolveDependency->resolveRequest(request: $resolveRequest->child(serviceId: $serviceId))
            );
        }
    }

    /**
     * @return Closure(object, mixed) : void
     */
    private function writerFor(string $class, string $property): Closure
    {
        $key = $class.'::$'.$property;

        return $this->writers[$key] ?? ($this->writers[$key] = Closure::bind(
            closure : static function (object $target, mixed $value) use ($property): void {
                $target->{$property} = $value;
            },
            newThis : null,
            newScope: $class,
        ));
    }
}
