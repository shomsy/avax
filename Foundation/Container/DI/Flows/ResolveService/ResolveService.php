<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\ResolveService;

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ServiceNotFoundException;
use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

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
