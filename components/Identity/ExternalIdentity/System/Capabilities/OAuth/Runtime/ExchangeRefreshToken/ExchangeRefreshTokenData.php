<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeRefreshToken;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeRefreshTokenData
{
    public function __construct(
        public string $clientId,
        #[SensitiveParameter]
        public string $refreshToken,
        #[SensitiveParameter]
        public ?string $clientSecret = null,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
    ) {
    }
}
