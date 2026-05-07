<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaStatus;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaEnrollmentRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaRecoveryRecord;
use SensitiveParameter;

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

    public function findMethod(UserId $userId) : ?MfaMethodRecord
    {
        return $this->methods[$userId->value] ?? null;
    }

    public function saveMethod(MfaMethodRecord $mfaMethodRecord) : void
    {
        $this->methods[$mfaMethodRecord->userId->value] = $mfaMethodRecord;
        unset($this->pendingEnrollments[$mfaMethodRecord->userId->value]);
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

    public function findPendingEnrollment(UserId $userId) : ?MfaEnrollmentRecord
    {
        return $this->pendingEnrollments[$userId->value] ?? null;
    }

    public function startEnrollment(MfaEnrollmentRecord $mfaEnrollmentRecord) : void
    {
        $this->pendingEnrollments[$mfaEnrollmentRecord->userId->value] = $mfaEnrollmentRecord;
    }

    public function cancelEnrollment(UserId $userId) : void
    {
        unset($this->pendingEnrollments[$userId->value]);
    }

    public function saveRecovery(MfaRecoveryRecord $mfaRecoveryRecord) : void
    {
        $this->forgetRecoveryForUser(userId: $mfaRecoveryRecord->userId);
        $this->recoveryTokens[$mfaRecoveryRecord->tokenHash] = $mfaRecoveryRecord;
    }

    public function findRecovery(#[SensitiveParameter] string $tokenHash) : ?MfaRecoveryRecord
    {
        return $this->recoveryTokens[$tokenHash] ?? null;
    }

    public function forgetRecovery(#[SensitiveParameter] string $tokenHash) : void
    {
        unset($this->recoveryTokens[$tokenHash]);
    }
}
