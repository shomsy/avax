<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use DateTimeImmutable;

/**
 * Access token issued by the token backend.
 */
final readonly class IssuedToken
{
    public function __construct(
        public string            $token,
        public string            $tokenId,
        public DateTimeImmutable $expiresAt
    ) {}
}
