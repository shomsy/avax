<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities;

use LogicException;

final class ExternalIdentityCapabilityUnavailable extends LogicException
{
    public static function oauth(string $operation) : self
    {
        return new self(message: sprintf('OAuth operation [%s] is not configured.', $operation));
    }

    public static function oidc(string $operation) : self
    {
        return new self(message: sprintf('OpenID Connect operation [%s] is not configured.', $operation));
    }

    public static function sso(string $operation) : self
    {
        return new self(message: sprintf('Federation operation [%s] is not configured.', $operation));
    }
}
