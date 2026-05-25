<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\IssueAccessToken;

use Avax\Components\Identity\Auth\System\Foundation\Values\SignedToken;

/**
 * IssuedAccessToken — value object wrapping a newly issued signed token.
 *
 * Adapted from the enterprise reference package.
 */
final readonly class IssuedAccessToken
{
    public function __construct(private SignedToken $token) {}

    public function token(): SignedToken
    {
        return $this->token;
    }
}
