<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\RegenerateBackupCodes;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\BackupCodeSet;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\StartMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\VerifyMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Disable\DisableMfa;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\CancelMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\ConfirmMfaEnrollmentData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll\StartMfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaEnrollment;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaRecoveryChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\VerifyMfaChallengeData;
use Avax\Auth\System\Flows\Login\AuthenticationResult;
use DateMalformedStringException;
use Random\RandomException;

final readonly class Mfa
{
    public function __construct(
        private StartMfaEnrollment    $startMfaEnrollment,
        private ConfirmMfaEnrollment  $confirmMfaEnrollment,
        private CancelMfaEnrollment   $cancelMfaEnrollment,
        private StartMfaChallenge     $startMfaChallenge,
        private VerifyMfaChallenge    $verifyMfaChallenge,
        private RegenerateBackupCodes $regenerateBackupCodes,
        private DisableMfa            $disableMfa,
        private StartMfaRecovery      $startMfaRecovery,
        private ConfirmMfaRecovery    $confirmMfaRecovery
    ) {}

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function startMfaEnrollment() : MfaEnrollment
    {
        return $this->startMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function confirmMfaEnrollment(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        return $this->confirmMfaEnrollment->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     */
    public function cancelMfaEnrollment() : void
    {
        $this->cancelMfaEnrollment->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function beginMfaChallenge() : MfaChallenge
    {
        return $this->startMfaChallenge->execute();
    }

    public function verifyMfaChallenge(VerifyMfaChallengeData $data) : AuthenticationResult
    {
        return $this->verifyMfaChallenge->execute(data: $data);
    }

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function regenerateBackupCodes() : BackupCodeSet
    {
        return $this->regenerateBackupCodes->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function disableMfa() : void
    {
        $this->disableMfa->execute();
    }

    public function beginMfaRecovery(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        return $this->startMfaRecovery->execute(data: $data);
    }

    public function confirmMfaRecovery(ConfirmMfaRecoveryData $data) : void
    {
        $this->confirmMfaRecovery->execute(data: $data);
    }
}
