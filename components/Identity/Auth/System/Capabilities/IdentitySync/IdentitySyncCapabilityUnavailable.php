<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync;

use LogicException;

final class IdentitySyncCapabilityUnavailable extends LogicException
{
    public static function scim(string $operation) : self
    {
        return new self(message: "SCIM operation [{$operation}] is not configured.");
    }

    public static function provisioning(string $operation) : self
    {
        return new self(message: "Provisioning operation [{$operation}] is not configured.");
    }
}
