<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface as GeneralMfaStoreInterface;
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
        #[SensitiveParameter]
        private CurrentAuthentication      $currentAuthentication,
        private GeneralMfaStoreInterface   $generalMfaStore,
        private MfaChallengeStoreInterface $mfaChallengeStore,
        private AuditLogInterface          $auditLog,
        private Clock $clock, int|null $expiresAfterSeconds = null,
        private int                        $maxAttempts = 5,
    )
    {
        $expiresAfterSeconds       ??= 300;
        $this->expiresAfterSeconds = $expiresAfterSeconds;
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     * @throws Unauthenticated
     */
    public function execute(#[SensitiveParameter] ?string $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        return $this->issueForUserId(
            userId   : new UserId(value: $user->id),
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            purpose  : MfaChallengePurpose::STEP_UP,
        );
    }

    /**
     * @throws MfaChallengeFailed
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    private function issueForUserId(
        UserId              $userId,
        MfaChallengePurpose $mfaChallengePurpose,
        #[SensitiveParameter]
        ?string $ipAddress, string|null $userAgent,
    ) : MfaChallenge
    {
        if (! $this->generalMfaStore->isEnabled(userId: $userId)) {
            throw MfaChallengeFailed::notEnabled();
        }

        $this->mfaChallengeStore->forgetForUser(userId: $userId);

        $now                = $this->clock->now();
        $mfaChallengeRecord = new MfaChallengeRecord(
            challengeId: bin2hex(string: random_bytes(length: 16)),
            userId     : $userId,
            purpose    : $mfaChallengePurpose,
            createdAt  : $now,
            expiresAt  : $now->modify(modifier: sprintf('+%d seconds', $this->expiresAfterSeconds)),
            maxAttempts: $this->maxAttempts,
        );

        $this->mfaChallengeStore->issue(record: $mfaChallengeRecord);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.challenge.requested',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'      => $userId->value,
                                                           'challenge_id' => $mfaChallengeRecord->challengeId,
                                                           'purpose'      => $mfaChallengePurpose->value,
                                                           'ip_address'   => $ipAddress,
                                                           'user_agent'   => $userAgent,
                                                       ],
                                       ));

        return $mfaChallengeRecord->toBoundary();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function issueForLogin(User $user, #[SensitiveParameter] ?string $ipAddress = null, string|null $userAgent = null) : MfaChallenge
    {
        return $this->issueForUserId(
            userId   : $user->getId(),
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            purpose  : MfaChallengePurpose::LOGIN,
        );
    }
}
