<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Tokens;

use Avax\Components\Identity\Foundation\Values\SignedToken;

interface SignToken
{
    public function sign(TokenClaims $claims): SignedToken;
}
