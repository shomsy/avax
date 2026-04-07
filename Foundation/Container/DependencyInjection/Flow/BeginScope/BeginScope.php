<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flow\BeginScope;

use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;

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
