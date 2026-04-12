<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\IntrospectToken;

use SensitiveParameter;

final readonly class IntrospectTokenData
{
    public function __construct(
        public string $clientId,
        #[SensitiveParameter] public string $token,
        #[SensitiveParameter] public string|null $clientSecret = null
    ) {}
}
