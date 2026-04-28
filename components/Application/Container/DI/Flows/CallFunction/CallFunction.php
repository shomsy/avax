<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Flows\CallFunction;

use Avax\Components\Application\Container\DI\Capabilities\Resolution\ServiceResolver;
use ReflectionException;

/**
 * Public invocation flow for executing callables through the container.
 */
final readonly class CallFunction
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * @param callable|string      $target
     * @param array<string, mixed> $parameters
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function call(callable|string $target, array $parameters = []) : mixed
    {
        return $this->resolver->call(callable: $target, parameters: $parameters);
    }
}
