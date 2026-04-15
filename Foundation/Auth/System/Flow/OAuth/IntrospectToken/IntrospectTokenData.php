<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\IntrospectToken;

use SensitiveParameter;

final readonly class IntrospectTokenData
{
    public string|null $expectedIssuer;
    public string|null $expectedAudience;
    public string|null $clientSecret;
    public string      $token;
    public string      $clientId;

    public function __construct(
        string                            $clientId,
        #[SensitiveParameter] string      $token,
        #[SensitiveParameter] string|null $clientSecret = null,
        string|null                       $expectedAudience = null,
        string|null                       $expectedIssuer = null
    )
    {
        $this->clientId         = $clientId;
        $this->token            = $token;
        $this->clientSecret     = $clientSecret;
        $this->expectedAudience = $expectedAudience;
        $this->expectedIssuer   = $expectedIssuer;
    }
}
