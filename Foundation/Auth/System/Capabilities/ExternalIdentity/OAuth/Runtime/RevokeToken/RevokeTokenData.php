<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\OAuth\RevokeToken;

use SensitiveParameter;

final readonly class RevokeTokenData
{
    public string|null $userAgent;
    public string|null $ipAddress;
    public string|null $tokenTypeHint;
    public string|null $clientSecret;
    public string      $token;
    public string      $clientId;

    public function __construct(
        string                            $clientId,
        #[SensitiveParameter] string      $token,
        #[SensitiveParameter] string|null $clientSecret = null,
        #[SensitiveParameter] string|null $tokenTypeHint = null,
        #[SensitiveParameter] string|null $ipAddress = null,
        string|null                       $userAgent = null
    )
    {
        $this->clientId      = $clientId;
        $this->token         = $token;
        $this->clientSecret  = $clientSecret;
        $this->tokenTypeHint = $tokenTypeHint;
        $this->ipAddress     = $ipAddress;
        $this->userAgent     = $userAgent;
    }
}
