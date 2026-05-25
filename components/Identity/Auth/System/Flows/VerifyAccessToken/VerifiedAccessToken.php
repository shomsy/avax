<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyAccessToken;

use Avax\Components\Identity\Auth\System\Foundation\Values\TokenClaims;

/**
 * VerifiedAccessToken — value object wrapping verified token claims.
 *
 * Adapted from the enterprise reference package.
 * Returned by VerifyAccessToken flow when a token passes all checks.
 */
final readonly class VerifiedAccessToken
{
    public function __construct(private TokenClaims $claims) {}

    public function claims(): TokenClaims
    {
        return $this->claims;
    }
}
