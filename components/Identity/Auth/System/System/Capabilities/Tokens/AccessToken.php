<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Tokens;

use DateTimeImmutable;

/**
 * AccessToken - Value object for an access token.
 * 1:1 alignment with refactor.md.
 */
final readonly class AccessToken
{
    public function __construct(
        public string            $value,
        public DateTimeImmutable $expiresAt,
    ) {}
}
