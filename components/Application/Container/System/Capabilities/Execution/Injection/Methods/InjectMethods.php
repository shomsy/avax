<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Methods;

use Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints\ServiceBlueprint;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveRequest;
use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use Closure;

/**
 * Invokes blueprint-marked method injections on resolved instances.
 */
final class InjectMethods
{
    /** @var array<string, Closure(object, array) : mixed> */
    private array $invokers = [];

    public function __construct(private readonly ResolveCallArguments $resolveCallArguments)
    {
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function inject(
        object           $target,
        ServiceBlueprint $serviceBlueprint,
        array            $overrides,
        ServiceResolver  $serviceResolver,
        ResolveRequest   $resolveRequest,
    ) : void
    {
        foreach ($serviceBlueprint->injectableMethods as $method) {
            $arguments = $this->resolveCallArguments->resolvePlan(
                plan     : $method['plan'],
                overrides: $overrides,
                resolver : $serviceResolver,
                request  : $resolveRequest,
            );

            ($this->invokerFor(class: $serviceBlueprint->class, method: $method['name']))($target, $arguments);
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
