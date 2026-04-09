<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa;

use Avax\Auth\System\Capability\User\UserId;

/**
 * Stores active MFA methods, pending enrollments, and recovery tokens.
 */
interface MfaStoreInterface
{
    public function status(UserId $userId) : MfaStatus;

    public function isEnabled(UserId $userId) : bool;

    public function findMethod(UserId $userId) : MfaMethodRecord|null;

    public function saveMethod(MfaMethodRecord $record) : void;

    public function disable(UserId $userId) : void;

    public function findPendingEnrollment(UserId $userId) : MfaEnrollmentRecord|null;

    public function startEnrollment(MfaEnrollmentRecord $record) : void;

    public function cancelEnrollment(UserId $userId) : void;

    public function saveRecovery(MfaRecoveryRecord $record) : void;

    public function findRecovery(string $tokenHash) : MfaRecoveryRecord|null;

    public function forgetRecovery(string $tokenHash) : void;

    public function forgetRecoveryForUser(UserId $userId) : void;
}
