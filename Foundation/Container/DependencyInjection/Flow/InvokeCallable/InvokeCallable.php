<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flow\InvokeCallable;

use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;

/**
 * Public invocation flow for executing callables through the container.
 */
final readonly class InvokeCallable
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function call(callable|string $target, array $parameters = []) : mixed
    {
        return $this->kernel->call(callable: $target, parameters: $parameters);
    }
}
