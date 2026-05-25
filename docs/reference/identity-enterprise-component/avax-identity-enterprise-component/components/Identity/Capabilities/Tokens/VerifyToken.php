<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\Values\SignedToken;

interface VerifyToken
{
    public function verify(SignedToken $token): TokenClaims;
}
