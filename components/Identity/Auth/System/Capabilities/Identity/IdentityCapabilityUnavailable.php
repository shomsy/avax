<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity;

use LogicException;

final class IdentityCapabilityUnavailable extends LogicException
{
    public static function coordinator(string $capability): self
    {
        return new self(message: sprintf('Identity capability [%s] is not configured.', $capability));
    }
}
