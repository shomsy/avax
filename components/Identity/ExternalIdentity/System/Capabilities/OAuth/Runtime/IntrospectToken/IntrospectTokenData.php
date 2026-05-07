<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Runtime\IntrospectToken;

use SensitiveParameter;

final readonly class IntrospectTokenData
{
    public function __construct(
        public string  $clientId,
        #[SensitiveParameter]
        public string  $token,
        #[SensitiveParameter]
        public ?string $clientSecret = null,
        public ?string $expectedAudience = null,
        public ?string $expectedIssuer = null,
    ) {}
}
