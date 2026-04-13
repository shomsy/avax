<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Execution\Injection\Methods;

use Avax\Container\DI\Capabilities\Declaration\Blueprints\ServiceBlueprint;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Execution\Injection\Invocation\ResolveCallArguments;
use Avax\Container\DI\Capabilities\Resolution\ResolveRequest;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Closure;

/**
 * Invokes blueprint-marked method injections on resolved instances.
 */
final class InjectMethods
{
    /** @var array<string, Closure(object, array): mixed> */
    private array $invokers = [];

    public function __construct(
        private ResolveCallArguments $arguments
    ) {}

    /**
     * @param array<string, mixed> $overrides
     *
     * @throws ContainerException
     */
    public function inject(
        object           $target,
        ServiceBlueprint $blueprint,
        array            $overrides,
        ServiceResolver  $resolver,
        ResolveRequest   $request
    ) : void
    {
        foreach ($blueprint->injectableMethods as $method) {
            $arguments = $this->arguments->resolvePlan(
                plan     : $method['plan'],
                overrides: $overrides,
                resolver : $resolver,
                request  : $request
            );

            ($this->invokerFor(class: $blueprint->class, method: $method['name']))($target, $arguments);
        }
    }

    /**
     * Returns or creates the bound method invoker.
     *
     * @return Closure(object, array): mixed
     */
    private function invokerFor(string $class, string $method) : Closure
    {
        $key = $class . '::' . $method;

        if (isset($this->invokers[$key])) {
            return $this->invokers[$key];
        }

        return $this->invokers[$key] = Closure::bind(
            closure : static function (object $target, array $arguments) use ($method) : mixed {
                return $target->{$method}(...$arguments);
            },
            newThis : null,
            newScope: $class
        );
    }
}
