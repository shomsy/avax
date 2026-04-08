<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\Errors\ContainerException;
use Avax\Container\Errors\ServiceNotFoundException;
use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public resolution flow for retrieving or building services.
 */
final readonly class ResolveService
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function get(string $id) : mixed
    {
        return $this->resolver->get(id: $id);
    }

    /**
     * @param array<string, mixed> $parameters
     * @throws ContainerException
     * @throws ServiceNotFoundException
     */
    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->resolver->make(id: $abstract, parameters: $parameters);
    }
}
