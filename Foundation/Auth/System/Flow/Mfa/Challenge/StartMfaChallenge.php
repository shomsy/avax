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
use Random\RandomException;
use SensitiveParameter;

/**
 * Starts MFA challenges for login completion and step-up actions.
 */
final readonly class StartMfaChallenge
{
    private int                        $maxAttempts;
    private int                        $expiresAfterSeconds;
    private Clock                      $clock;
    private AuditLogInterface          $auditLog;
    private MfaChallengeStoreInterface $challengeStore;
    private MfaStoreInterface          $mfaStore;
    private CurrentAuthentication      $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        MfaStoreInterface                           $mfaStore,
        MfaChallengeStoreInterface                  $challengeStore,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock,
        int|null                                    $expiresAfterSeconds = null,
        int                                         $maxAttempts = 5
    )
    {
        $expiresAfterSeconds         ??= 300;
        $this->currentAuthentication = $currentAuthentication;
        $this->mfaStore              = $mfaStore;
        $this->challengeStore        = $challengeStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->expiresAfterSeconds   = $expiresAfterSeconds;
        $this->maxAttempts           = $maxAttempts;
    }

    /**
     * @throws Unauthenticated
     * @throws MfaChallengeFailed
     */
    public function execute(#[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        return $this->issueForUserId(
            userId   : new UserId(value: $user->id),
            purpose  : MfaChallengePurpose::STEP_UP,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );
    }

    /**
     * @throws MfaChallengeFailed
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    private function issueForUserId(
        UserId                            $userId,
        MfaChallengePurpose               $purpose,
        #[SensitiveParameter] string|null $ipAddress,
        string|null                       $userAgent
    ) : MfaChallenge
    {
        if (! $this->mfaStore->isEnabled(userId: $userId)) {
            throw MfaChallengeFailed::notEnabled();
        }

        $this->challengeStore->forgetForUser(userId: $userId);

        $now    = $this->clock->now();
        $record = new MfaChallengeRecord(
            challengeId: bin2hex(random_bytes(16)),
            userId     : $userId,
            purpose    : $purpose,
            createdAt  : $now,
            expiresAt  : $now->modify(modifier: "+{$this->expiresAfterSeconds} seconds"),
            maxAttempts: $this->maxAttempts
        );

        $this->challengeStore->issue(record: $record);
        $this->auditLog->record(event: new AuditEvent(
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
    public function issueForLogin(User $user, #[SensitiveParameter] string|null $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        return $this->issueForUserId(
            userId   : $user->getId(),
            purpose  : MfaChallengePurpose::LOGIN,
            ipAddress: $ipAddress,
            userAgent: $userAgent
        );
    }
}
