<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Support\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeAuthorizationCodeData
{
    public function __construct(
        public string                     $clientId,
        #[SensitiveParameter]
        public string                     $code,
        public string                     $redirectUri,
        #[SensitiveParameter]
        public string|null                $clientSecret = null,
        #[SensitiveParameter]
        public string|null                $codeVerifier = null,
        #[SensitiveParameter]
        public string|null                $ipAddress = null,
        public string|null                $userAgent = null,
        public OAuthSenderConstraint|null $senderConstraint = null,
    ) {}
}
