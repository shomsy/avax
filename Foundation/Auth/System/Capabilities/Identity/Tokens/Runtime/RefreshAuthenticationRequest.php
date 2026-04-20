<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use SensitiveParameter;

/**
 * Refresh token boundary input.
 */
final readonly class RefreshAuthenticationRequest
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string      $refreshToken;

    public function __construct(
        #[SensitiveParameter] string      $refreshToken,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->refreshToken = $refreshToken;
        $this->ipAddress    = $ipAddress;
        $this->userAgent    = $userAgent;
    }
}
