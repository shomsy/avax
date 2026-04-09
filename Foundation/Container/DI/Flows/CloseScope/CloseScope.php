<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\CloseScope;

use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;

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
