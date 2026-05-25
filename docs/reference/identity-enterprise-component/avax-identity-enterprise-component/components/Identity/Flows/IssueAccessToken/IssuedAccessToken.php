<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\IssueAccessToken;

use Avax\Components\Identity\Foundation\Values\SignedToken;

final readonly class IssuedAccessToken
{
    public function __construct(private SignedToken $token) {}

    public function token(): SignedToken
    {
        return $this->token;
    }
}
