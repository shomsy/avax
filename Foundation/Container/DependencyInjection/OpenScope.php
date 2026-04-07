<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection;

use Avax\Container\DependencyInjection\Resolution\ServiceResolver;

/**
 * Public scope-entry flow.
 */
final readonly class OpenScope
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function open() : void
    {
        $this->resolver->openScope();
    }
}
