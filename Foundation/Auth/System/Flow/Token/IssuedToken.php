<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Access token issued by the token backend.
 */
final readonly class IssuedToken
{
    public function __construct(
        #[SensitiveParameter] public string $token,
        #[SensitiveParameter] public string $tokenId,
        public DateTimeImmutable            $expiresAt
    ) {}
}
