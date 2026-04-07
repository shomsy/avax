<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flow\EndScope;

use Avax\Container\DependencyInjection\Capability\Resolution\Kernel\ContainerKernel;

/**
 * Public scope-exit flow.
 */
final readonly class EndScope
{
    public function __construct(
        private ContainerKernel $kernel
    ) {}

    public function end() : void
    {
        $this->kernel->endScope();
    }
}
