<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Opaque refresh token pair returned to clients.
 */
final readonly class IssuedRefreshToken
{
    public function __construct(
        public string                 $token,
        public string                 $tokenId,
        public string                 $familyId,
        public UserId                 $userId,
        public DateTimeImmutable      $expiresAt,
        public DateTimeImmutable|null $mfaVerifiedAt = null
    ) {}
}
