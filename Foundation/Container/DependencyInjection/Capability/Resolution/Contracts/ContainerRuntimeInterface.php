<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Resolution\Contracts;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\KernelContext;

/**
 * Runtime-capable container facade used by nested resolution chains.
 */
interface ContainerRuntimeInterface extends ContainerInterface
{
    public function resolveContext(KernelContext $context) : mixed;
}
