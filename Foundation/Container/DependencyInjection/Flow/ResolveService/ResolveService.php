<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flow\ResolveService;

use Avax\Container\DependencyInjection\Capability\Prototypes\Model\ServicePrototype;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;

/**
 * Public resolution flow for retrieving or building services.
 */
final readonly class ResolveService
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function get(string $id) : mixed
    {
        return $this->kernel->get(id: $id);
    }

    public function make(string $abstract, array $parameters = []) : object
    {
        return $this->kernel->make(id: $abstract, parameters: $parameters);
    }

    public function resolve(ServicePrototype $prototype) : mixed
    {
        return $this->kernel->resolve(prototype: $prototype);
    }

    public function resolveContext(KernelContext $context) : mixed
    {
        return $this->kernel->resolveContext(context: $context);
    }
}
