<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities;

use LogicException;

final class ExternalIdentityCapabilityUnavailable extends LogicException
{
    public static function oauth(string $operation) : self
    {
        return new self(message: "OAuth operation [{$operation}] is not configured.");
    }

    public static function oidc(string $operation) : self
    {
        return new self(message: "OpenID Connect operation [{$operation}] is not configured.");
    }

    public static function sso(string $operation) : self
    {
        return new self(message: "Federation operation [{$operation}] is not configured.");
    }
}
