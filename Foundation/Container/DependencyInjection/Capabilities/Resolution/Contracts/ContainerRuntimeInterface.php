<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capabilities\Resolution\Contracts;

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Capabilities\Resolution\Kernel\KernelContext;

/**
 * Runtime-capable container facade used by nested resolution chains.
 */
interface ContainerRuntimeInterface extends ContainerInterface
{
    public function resolveContext(KernelContext $context) : mixed;
}
