<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\ExchangeAuthorizationCode;

use SensitiveParameter;

final readonly class ExchangeAuthorizationCodeData
{
    public function __construct(
        public string $clientId,
        #[SensitiveParameter] public string $code,
        public string $redirectUri,
        #[SensitiveParameter] public string|null $clientSecret = null,
        #[SensitiveParameter] public string|null $codeVerifier = null,
        public string|null $ipAddress = null,
        public string|null $userAgent = null
    ) {}
}
