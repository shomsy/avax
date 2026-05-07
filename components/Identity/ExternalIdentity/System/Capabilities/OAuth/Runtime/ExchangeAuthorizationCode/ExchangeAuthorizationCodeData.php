<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\ExchangeAuthorizationCode;

use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements\SenderConstraint\OAuthSenderConstraint;
use SensitiveParameter;

final readonly class ExchangeAuthorizationCodeData
{
    public function __construct(
        public string                 $clientId,
        #[SensitiveParameter]
        public string                 $code,
        public string                 $redirectUri,
        #[SensitiveParameter]
        public ?string                $clientSecret = null,
        #[SensitiveParameter]
        public ?string                $codeVerifier = null,
        #[SensitiveParameter]
        public ?string                $ipAddress = null,
        public ?string                $userAgent = null,
        public ?OAuthSenderConstraint $senderConstraint = null,
    ) {}
}
