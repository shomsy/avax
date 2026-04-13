<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Provisioning;

use RuntimeException;

final class ProvisioningFailed extends RuntimeException
{
    public static function unsupported() : self
    {
        return new self(message: 'Provisioning lifecycle is not configured.');
    }
}
