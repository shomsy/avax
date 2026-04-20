<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Tokens\Runtime;

use DateTimeImmutable;
use SensitiveParameter;

/**
 * Access token issued by the token backend.
 */
final readonly class IssuedToken
{
    public DateTimeImmutable $expiresAt;
    public string            $tokenId;
    public string            $token;

    public function __construct(
        #[SensitiveParameter] string $token,
        #[SensitiveParameter] string $tokenId,
        DateTimeImmutable            $expiresAt
    )
    {
        $this->token     = $token;
        $this->tokenId   = $tokenId;
        $this->expiresAt = $expiresAt;
    }
}
