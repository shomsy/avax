<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use SensitiveParameter;

/**
 * Refresh token boundary input.
 */
final readonly class RefreshAuthenticationRequest
{
    public function __construct(
        #[SensitiveParameter] public string $refreshToken,
        public string|null                  $ipAddress = null,
        public string|null                  $userAgent = null
    ) {}
}
