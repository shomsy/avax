<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\CallFunction;

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

/**
 * Public invocation flow for executing callables through the container.
 */
final readonly class CallFunction
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function call(callable|string $target, array $parameters = []) : mixed
    {
        return $this->resolver->call(callable: $target, parameters: $parameters);
    }
}
