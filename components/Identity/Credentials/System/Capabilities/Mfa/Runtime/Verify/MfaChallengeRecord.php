<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaVerificationAttempt;
use DateTimeImmutable;

/**
 * Stored MFA challenge lifecycle state.
 *
 * @param list<MfaVerificationAttempt> $attempts
 */
final readonly class MfaChallengeRecord
{
    public int $maxAttempts;

    /**
     * @param list<MfaVerificationAttempt> $attempts
     */
    public function __construct(
        public string              $challengeId,
        public UserId              $userId,
        public MfaChallengePurpose $purpose,
        public DateTimeImmutable   $createdAt,
        public DateTimeImmutable   $expiresAt,
        ?int                       $maxAttempts = null,
        public array               $attempts = [],
    )
    {
        $maxAttempts       ??= 5;
        $this->maxAttempts = $maxAttempts;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function isLocked() : bool
    {
        return count(value: $this->attempts) >= $this->maxAttempts;
    }

    public function recordAttempt(MfaVerificationAttempt $mfaVerificationAttempt) : self
    {
        $attempts   = $this->attempts;
        $attempts[] = $mfaVerificationAttempt;

        return new self(
            challengeId: $this->challengeId,
            userId     : $this->userId,
            purpose    : $this->purpose,
            createdAt  : $this->createdAt,
            expiresAt  : $this->expiresAt,
            maxAttempts: $this->maxAttempts,
            attempts   : $attempts,
        );
    }

    public function toBoundary() : MfaChallenge
    {
        return new MfaChallenge(
            challengeId      : $this->challengeId,
            purpose          : $this->purpose,
            expiresAt        : $this->expiresAt,
            remainingAttempts: $this->remainingAttempts(),
        );
    }

    public function remainingAttempts() : int
    {
        return max(0, $this->maxAttempts - count(value: $this->attempts));
    }
}
