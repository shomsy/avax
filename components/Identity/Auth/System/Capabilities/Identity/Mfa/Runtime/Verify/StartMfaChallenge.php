<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface as GeneralMfaStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

/**
 * Starts MFA challenges for login completion and step-up actions.
 */
final readonly class StartMfaChallenge
{
    private int $expiresAfterSeconds;

    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private GeneralMfaStoreInterface                    $mfaStore,
        private MfaChallengeStoreInterface                  $challengeStore,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock,
        int|null                                            $expiresAfterSeconds = null,
        private int                                         $maxAttempts = 5
    )
    {
        $expiresAfterSeconds       ??= 300;
        $this->expiresAfterSeconds = $expiresAfterSeconds;
    }

    /**
     * @param string|null $ipAddress
     * @param string|null $userAgent
     *
     * @return MfaChallenge
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
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
     * @throws DateMalformedStringException
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
            challengeId: bin2hex(string: random_bytes(length: 16)),
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
     * @param User        $user
     * @param string|null $ipAddress
     * @param string|null $userAgent
     *
     * @return MfaChallenge
     * @throws DateMalformedStringException
     * @throws RandomException
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
