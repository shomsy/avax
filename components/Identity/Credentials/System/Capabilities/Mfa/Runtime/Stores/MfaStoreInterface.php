<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaStatus;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaEnrollmentRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaRecoveryRecord;

/**
 * Stores active MFA methods, pending enrollments, and recovery tokens.
 */
interface MfaStoreInterface
{
    public function status(UserId $userId) : MfaStatus;

    public function isEnabled(UserId $userId) : bool;

    public function findMethod(UserId $userId) : ?MfaMethodRecord;

    public function saveMethod(MfaMethodRecord $mfaMethodRecord) : void;

    public function disable(UserId $userId) : void;

    public function findPendingEnrollment(UserId $userId) : ?MfaEnrollmentRecord;

    public function startEnrollment(MfaEnrollmentRecord $mfaEnrollmentRecord) : void;

    public function cancelEnrollment(UserId $userId) : void;

    public function saveRecovery(MfaRecoveryRecord $mfaRecoveryRecord) : void;

    public function findRecovery(string $tokenHash) : ?MfaRecoveryRecord;

    public function forgetRecovery(string $tokenHash) : void;

    public function forgetRecoveryForUser(UserId $userId) : void;
}
