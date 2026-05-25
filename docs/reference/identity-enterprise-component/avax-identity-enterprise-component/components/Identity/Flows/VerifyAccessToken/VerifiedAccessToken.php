<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\VerifyAccessToken;

use Avax\Components\Identity\Capabilities\Tokens\TokenClaims;

final readonly class VerifiedAccessToken
{
    public function __construct(private TokenClaims $claims) {}

    public function claims(): TokenClaims
    {
        return $this->claims;
    }
}
