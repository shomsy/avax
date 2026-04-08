<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public scope-exit flow.
 */
final readonly class CloseScope
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * Closes the current scope frame.
     */
    public function close(string|null $kind = null) : void
    {
        $this->resolver->closeScope(kind: $kind);
    }
}
