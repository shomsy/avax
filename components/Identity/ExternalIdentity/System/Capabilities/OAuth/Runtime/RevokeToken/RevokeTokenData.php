<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\RevokeToken;

use SensitiveParameter;

final readonly class RevokeTokenData
{
    public function __construct(
        public string  $clientId,
        #[SensitiveParameter]
        public string  $token,
        #[SensitiveParameter]
        public ?string $clientSecret = null,
        #[SensitiveParameter]
        public ?string $tokenTypeHint = null,
        #[SensitiveParameter]
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}
}
