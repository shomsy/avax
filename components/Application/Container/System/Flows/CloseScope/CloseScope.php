<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\CloseScope;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;

/**
 * Public scope-exit flow.
 */
final readonly class CloseScope
{
    public function __construct(private ServiceResolver $serviceResolver)
    {
    }

    /**
     * Closes the current scope frame.
     */
    public function close(?string $kind = null) : void
    {
        $this->serviceResolver->closeScope(kind: $kind);
    }
}
