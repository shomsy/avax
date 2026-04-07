<?php

declare(strict_types=1);

namespace Avax\Container\Flows\BeginScope;

use Avax\Container\Capabilities\Resolution\Kernel\ContainerKernel;

/**
 * Public scope-entry flow.
 */
final readonly class BeginScope
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function begin() : void
    {
        $this->kernel->beginScope();
    }
}
