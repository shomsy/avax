<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use Avax\Auth\System\Capability\User\UserId;
use DateTimeImmutable;

/**
 * Stored OAuth authorization code state.
 */
final readonly class AuthorizationCodeRecord
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        public string                 $codeId,
        public string                 $clientId,
        public UserId                 $userId,
        public string                 $redirectUri,
        public array                  $scopes,
        public DateTimeImmutable      $expiresAt,
        public string|null            $nonce = null,
        public string|null            $codeChallenge = null,
        public PkceMethod|null        $codeChallengeMethod = null,
        public DateTimeImmutable|null $usedAt = null,
        public DateTimeImmutable|null $mfaVerifiedAt = null,
        public bool                   $phishingResistant = false
    ) {}

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function wasUsed() : bool
    {
        return $this->usedAt !== null;
    }
}
