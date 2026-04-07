<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public invocation flow for executing callables through the container.
 */
final readonly class CallFunction
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function call(callable|string $target, array $parameters = []) : mixed
    {
        return $this->resolver->call(callable: $target, parameters: $parameters);
    }
}
