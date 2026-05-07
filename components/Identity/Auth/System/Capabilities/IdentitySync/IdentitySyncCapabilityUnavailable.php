<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\IdentitySync;

use LogicException;

final class IdentitySyncCapabilityUnavailable extends LogicException
{
    public static function scim(string $operation) : self
    {
        return new self(message: sprintf('SCIM operation [%s] is not configured.', $operation));
    }

    public static function provisioning(string $operation) : self
    {
        return new self(message: sprintf('Provisioning operation [%s] is not configured.', $operation));
    }
}
