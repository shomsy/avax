<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Scopes;

/**
 * Explicit reset contract for pooled runtime reuse.
 */
interface ResettableInterface
{
    public function reset() : void;
}
