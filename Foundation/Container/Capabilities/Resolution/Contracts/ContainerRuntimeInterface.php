<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Resolution\Contracts;

use Avax\Container\Capabilities\Resolution\Kernel\KernelContext;
use Avax\Container\ContainerInterface;

/**
 * Runtime-capable container facade used by nested resolution chains.
 */
interface ContainerRuntimeInterface extends ContainerInterface
{
    public function resolveContext(KernelContext $context) : mixed;
}
