<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Runtime\ExchangeRefreshToken;

use Avax\Auth\System\Capabilities\ExternalIdentity\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeRefreshTokenData
{
    public function __construct(
        public string                            $clientId,
        #[SensitiveParameter] public string      $refreshToken,
        #[SensitiveParameter] public string|null $clientSecret = null,
        #[SensitiveParameter] public string|null $ipAddress = null,
        public string|null                       $userAgent = null,
        public OAuthSenderConstraint|null        $senderConstraint = null
    ) {}
}
