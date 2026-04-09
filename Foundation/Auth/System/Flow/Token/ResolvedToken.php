<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Token;

use Avax\Auth\System\Capability\User\User;
use DateTimeImmutable;

/**
 * Successfully verified access token state.
 */
final readonly class ResolvedToken
{
    public function __construct(
        public User                   $user,
        public string                 $tokenId,
        public DateTimeImmutable      $expiresAt,
        public DateTimeImmutable|null $mfaVerifiedAt = null
    ) {}
}
