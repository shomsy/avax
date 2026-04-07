<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection;

use Avax\Container\DependencyInjection\Resolution\ServiceResolver;

/**
 * Public scope-exit flow.
 */
final readonly class CloseScope
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    public function close() : void
    {
        $this->resolver->closeScope();
    }
}
