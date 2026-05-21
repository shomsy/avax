<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Tenancy\System\Configuration\Assembly;

use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContext;
use Avax\Components\Identity\Tenancy\System\Capabilities\Context\TenantContextInterface;

/**
 * Configures the Tenancy static facades with injectable context implementations.
 */
final class TenancyGraph
{
    /**
     * Set the tenant context implementation that backs the static facade.
     */
    public static function useContext(TenantContextInterface $context) : void
    {
        TenantContext::setContext($context);
    }
}
