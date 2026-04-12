<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeRefreshToken;

use Avax\Auth\System\Capability\OAuth\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeRefreshTokenData
{
    public function __construct(
        public string $clientId,
        #[SensitiveParameter] public string $refreshToken,
        #[SensitiveParameter] public string|null $clientSecret = null,
        public string|null $ipAddress = null,
        public string|null $userAgent = null,
        public OAuthSenderConstraint|null $senderConstraint = null
    ) {}
}
