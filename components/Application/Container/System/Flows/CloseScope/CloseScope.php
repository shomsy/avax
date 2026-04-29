<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\CloseScope;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;

/**
 * Public scope-exit flow.
 */
final readonly class CloseScope
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * Closes the current scope frame.
     */
    public function close(string|null $kind = null) : void
    {
        $this->resolver->closeScope(kind: $kind);
    }
}
