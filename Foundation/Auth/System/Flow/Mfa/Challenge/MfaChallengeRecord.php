<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge;

use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Mfa\MfaVerificationAttempt;
use DateTimeImmutable;

/**
 * Stored MFA challenge lifecycle state.
 *
 * @param list<MfaVerificationAttempt> $attempts
 */
final readonly class MfaChallengeRecord
{
    public array               $attempts;
    public int                 $maxAttempts;
    public DateTimeImmutable   $expiresAt;
    public DateTimeImmutable   $createdAt;
    public MfaChallengePurpose $purpose;
    public UserId              $userId;
    public string              $challengeId;

    /**
     * @param list<MfaVerificationAttempt> $attempts
     */
    public function __construct(
        string              $challengeId,
        UserId              $userId,
        MfaChallengePurpose $purpose,
        DateTimeImmutable   $createdAt,
        DateTimeImmutable   $expiresAt,
        int|null            $maxAttempts = null,
        array               $attempts = []
    )
    {
        $maxAttempts       ??= 5;
        $this->challengeId = $challengeId;
        $this->userId      = $userId;
        $this->purpose     = $purpose;
        $this->createdAt   = $createdAt;
        $this->expiresAt   = $expiresAt;
        $this->maxAttempts = $maxAttempts;
        $this->attempts    = $attempts;
    }

    public function isExpiredAt(DateTimeImmutable $moment) : bool
    {
        return $this->expiresAt <= $moment;
    }

    public function isLocked() : bool
    {
        return count($this->attempts) >= $this->maxAttempts;
    }

    public function recordAttempt(MfaVerificationAttempt $attempt) : self
    {
        $attempts   = $this->attempts;
        $attempts[] = $attempt;

        return new self(
            challengeId: $this->challengeId,
            userId     : $this->userId,
            purpose    : $this->purpose,
            createdAt  : $this->createdAt,
            expiresAt  : $this->expiresAt,
            maxAttempts: $this->maxAttempts,
            attempts   : $attempts
        );
    }

    public function toBoundary() : MfaChallenge
    {
        return new MfaChallenge(
            challengeId      : $this->challengeId,
            purpose          : $this->purpose,
            expiresAt        : $this->expiresAt,
            remainingAttempts: $this->remainingAttempts()
        );
    }

    public function remainingAttempts() : int
    {
        return max(0, $this->maxAttempts - count($this->attempts));
    }
}
