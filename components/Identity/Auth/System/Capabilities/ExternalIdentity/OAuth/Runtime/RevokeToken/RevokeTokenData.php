<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\RevokeToken;

use SensitiveParameter;

final readonly class RevokeTokenData
{
    public function __construct(
        public string                            $clientId,
        #[SensitiveParameter] public string      $token,
        #[SensitiveParameter] public string|null $clientSecret = null,
        #[SensitiveParameter] public string|null $tokenTypeHint = null,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null
    ) {}
}
