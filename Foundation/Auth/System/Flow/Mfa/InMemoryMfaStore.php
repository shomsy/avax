<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;

/**
 * In-memory MFA state for tests and demos.
 */
final class InMemoryMfaStore implements MfaStoreInterface
{
    /** @var array<int, MfaMethodRecord> */
    private array $methods = [];

    /** @var array<int, MfaEnrollmentRecord> */
    private array $pendingEnrollments = [];

    /** @var array<string, MfaRecoveryRecord> */
    private array $recoveryTokens = [];

    public function status(UserId $userId) : MfaStatus
    {
        if (isset($this->methods[$userId->value])) {
            return MfaStatus::ENABLED;
        }

        if (isset($this->pendingEnrollments[$userId->value])) {
            return MfaStatus::ENROLLMENT_PENDING;
        }

        return MfaStatus::DISABLED;
    }

    public function isEnabled(UserId $userId) : bool
    {
        return isset($this->methods[$userId->value]);
    }

    public function findMethod(UserId $userId) : MfaMethodRecord|null
    {
        return $this->methods[$userId->value] ?? null;
    }

    public function saveMethod(MfaMethodRecord $record) : void
    {
        $this->methods[$record->userId->value] = $record;
        unset($this->pendingEnrollments[$record->userId->value]);
    }

    public function disable(UserId $userId) : void
    {
        unset($this->methods[$userId->value], $this->pendingEnrollments[$userId->value]);
        $this->forgetRecoveryForUser(userId: $userId);
    }

    public function forgetRecoveryForUser(UserId $userId) : void
    {
        foreach ($this->recoveryTokens as $tokenHash => $record) {
            if ($record->userId->equals(other: $userId)) {
                unset($this->recoveryTokens[$tokenHash]);
            }
        }
    }

    public function findPendingEnrollment(UserId $userId) : MfaEnrollmentRecord|null
    {
        return $this->pendingEnrollments[$userId->value] ?? null;
    }

    public function startEnrollment(MfaEnrollmentRecord $record) : void
    {
        $this->pendingEnrollments[$record->userId->value] = $record;
    }

    public function cancelEnrollment(UserId $userId) : void
    {
        unset($this->pendingEnrollments[$userId->value]);
    }

    public function saveRecovery(MfaRecoveryRecord $record) : void
    {
        $this->forgetRecoveryForUser(userId: $record->userId);
        $this->recoveryTokens[$record->tokenHash] = $record;
    }

    public function findRecovery(#[\SensitiveParameter] string $tokenHash) : MfaRecoveryRecord|null
    {
        return $this->recoveryTokens[$tokenHash] ?? null;
    }

    public function forgetRecovery(#[\SensitiveParameter] string $tokenHash) : void
    {
        unset($this->recoveryTokens[$tokenHash]);
    }
}
