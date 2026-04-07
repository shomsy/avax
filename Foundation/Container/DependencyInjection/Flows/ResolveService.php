<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public resolution flow for retrieving or building services.
 */
final readonly class ResolveService
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function get(string $id) : mixed
    {
        return $this->resolver->get(id: $id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolver->make(id: $abstract, parameters: $parameters);
    }
}
