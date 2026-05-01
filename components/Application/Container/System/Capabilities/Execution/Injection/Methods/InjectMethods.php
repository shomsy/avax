<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Methods;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\DependencyBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Closure;

/**
 * Invokes blueprint-marked method injections on resolved instances.
 */
final class InjectMethods
{
    /** @var array<string, Closure(object, array) : mixed> */
    private array $invokers = [];

    public function __construct(private readonly ResolveCallArguments $resolveCallArguments) {}

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function inject(
        object         $target,
        DependencyBlueprint $dependencyBlueprint,
        array          $overrides,
        ResolveDependency $resolveDependency,
        ResolveRequest $resolveRequest,
    ) : void
    {
        foreach ($dependencyBlueprint->injectableMethods as $method) {
            $arguments = $this->resolveCallArguments->resolvePlan(
                plan     : $method['plan'],
                overrides: $overrides,
                resolver : $resolveDependency,
                request  : $resolveRequest,
            );

            ($this->invokerFor(class: $dependencyBlueprint->class, method: $method['name']))($target, $arguments);
        }
    }

    /**
     * Returns or creates the bound method invoker.
     *
     * @return Closure(object, array) : mixed
     */
    private function invokerFor(string $class, string $method) : Closure
    {
        $key = $class . '::' . $method;

        return $this->invokers[$key] ?? ($this->invokers[$key] = Closure::bind(
            closure : static fn (object $target, array $arguments) : mixed => $target->{$method}(...$arguments),
            newThis : null,
            newScope: $class,
        ));
    }
}
