<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use DateTimeImmutable;

/**
 * One-time authorization code returned to the client redirect layer.
 */
final readonly class IssuedAuthorizationCode
{
    public function __construct(
        public string            $code,
        public string            $codeId,
        public DateTimeImmutable $expiresAt,
        public string|null       $state = null
    ) {}
}
