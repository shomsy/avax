<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Tokens;

use DateTimeImmutable;

/**
 * RefreshToken - Value object for a refresh token.
 * 1:1 alignment with refactor.md.
 */
final readonly class RefreshToken
{
    public function __construct(
        public string            $value,
        public DateTimeImmutable $expiresAt,
    ) {}
}
