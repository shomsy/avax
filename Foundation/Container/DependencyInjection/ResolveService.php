<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection;

use Avax\Container\DependencyInjection\Resolution\ResolveRequest;
use Avax\Container\DependencyInjection\Resolution\ServiceBlueprint;
use Avax\Container\DependencyInjection\Resolution\ServiceResolver;

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

    public function resolve(ServiceBlueprint $blueprint) : mixed
    {
        return $this->resolver->resolve(blueprint: $blueprint);
    }

    public function resolveRequest(ResolveRequest $request) : mixed
    {
        return $this->resolver->resolveRequest(request: $request);
    }
}
