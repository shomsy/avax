<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Data\VerifyMfaChallengeData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Disable\DisableMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\MfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\MfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\MfaRecoveryChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\VerifyMfaChallenge;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

final readonly class Mfa
{
    public function __construct(
        private StartMfaEnrollment $startMfaEnrollment,
        private ConfirmMfaEnrollment $confirmMfaEnrollment,
        private CancelMfaEnrollment $cancelMfaEnrollment,
        private StartMfaChallenge $startMfaChallenge,
        private VerifyMfaChallenge $verifyMfaChallenge,
        #[SensitiveParameter]
        private RegenerateBackupCodes $regenerateBackupCodes,
        private DisableMfa $disableMfa,
        private StartMfaRecovery $startMfaRecovery,
        private ConfirmMfaRecovery $confirmMfaRecovery,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function startMfaEnrollment(): MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data): BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment(): void
    {
        $this->cancelMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function beginMfaChallenge(): MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data): AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function regenerateBackupCodes(): BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa(): void
    {
        $this->disableMfa->execute();
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function beginMfaRecovery(BeginMfaRecoveryData $data): MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data): void
    {
        $this->confirmMfaRecovery->execute(data: $data);
    }
}
