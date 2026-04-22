<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Runtime\Scopes;

/**
 * Explicit runtime disposal contract for scoped or shared services.
 */
interface DisposableInterface
{
    public function dispose() : void;
}
