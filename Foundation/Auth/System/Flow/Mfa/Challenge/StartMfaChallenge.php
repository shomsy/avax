<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Challenge;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaChallengeFailed;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Starts MFA challenges for login completion and step-up actions.
 */
final readonly class StartMfaChallenge
{
    public function __construct(
        private CurrentAuthentication      $currentAuthentication,
        private MfaStoreInterface          $mfaStore,
        private MfaChallengeStoreInterface $challengeStore,
        private AuditLogInterface          $auditLog,
        private Clock                      $clock,
        private int                        $expiresAfterSeconds = 300,
        private int                        $maxAttempts = 5
    ) {}

    /**
     * @throws Unauthenticated
     * @throws MfaChallengeFailed
     */
    public function execute(string|null $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        return $this->issueForUserId(
            userId   : new UserId($user->id),
            purpose  : MfaChallengePurpose::STEP_UP,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );
    }

    /**
     * @throws MfaChallengeFailed
     */
    private function issueForUserId(
        UserId              $userId,
        MfaChallengePurpose $purpose,
        string|null         $ipAddress,
        string|null         $userAgent
    ) : MfaChallenge
    {
        if (! $this->mfaStore->isEnabled($userId)) {
            throw MfaChallengeFailed::notEnabled();
        }

        $this->challengeStore->forgetForUser($userId);

        $now    = $this->clock->now();
        $record = new MfaChallengeRecord(
            challengeId: bin2hex(random_bytes(16)),
            userId     : $userId,
            purpose    : $purpose,
            createdAt  : $now,
            expiresAt  : $now->modify("+{$this->expiresAfterSeconds} seconds"),
            maxAttempts: $this->maxAttempts
        );

        $this->challengeStore->issue($record);
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.challenge.requested',
                                    occurredAt: $now,
                                    context   : [
                                                    'user_id'      => $userId->value,
                                                    'challenge_id' => $record->challengeId,
                                                    'purpose'      => $purpose->value,
                                                    'ip_address'   => $ipAddress,
                                                    'user_agent'   => $userAgent,
                                                ]
                                ));

        return $record->toBoundary();
    }

    /**
     * @throws MfaChallengeFailed
     */
    public function issueForLogin(User $user, string|null $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        return $this->issueForUserId(
            userId   : $user->getId(),
            purpose  : MfaChallengePurpose::LOGIN,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );
    }
}
