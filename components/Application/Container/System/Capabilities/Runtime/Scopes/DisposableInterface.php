<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes;

/**
 * Explicit runtime disposal contract for scoped or shared services.
 */
interface DisposableInterface
{
    public function dispose(): void;
}
