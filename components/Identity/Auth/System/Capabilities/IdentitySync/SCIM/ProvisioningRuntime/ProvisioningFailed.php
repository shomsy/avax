<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime;

use RuntimeException;

final class ProvisioningFailed extends RuntimeException
{
    public static function unsupported() : self
    {
        return new self(message: 'Provisioning lifecycle is not configured.');
    }
}
