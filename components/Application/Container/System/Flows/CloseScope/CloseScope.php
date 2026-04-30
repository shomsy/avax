<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\CloseScope;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ResolveDependency;

/**
 * Public scope-exit flow.
 */
final readonly class CloseScope
{
    public function __construct(private ResolveDependency $serviceResolver)
    {
    }

    /**
     * Closes the current scope frame.
     */
    public function close(string|null $kind = null) : void
    {
        $this->serviceResolver->closeScope(kind: $kind);
    }
}
