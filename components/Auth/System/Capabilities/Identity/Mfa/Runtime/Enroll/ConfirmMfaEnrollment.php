<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll;

use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\BackupCodeSet;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Backup\GenerateBackupCodes;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaEnrollmentFailed;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Records\MfaMethodRecord;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\TotpInterface;
use components\Auth\System\Capabilities\Identity\User\UserId;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use components\Auth\System\Foundation\Clock;
use Random\RandomException;
use SensitiveParameter;

/**
 * Completes MFA enrollment only after a valid first TOTP proof.
 */
final readonly class ConfirmMfaEnrollment
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface                           $mfaStore,
        private TotpInterface                               $totp,
        #[SensitiveParameter] private GenerateBackupCodes   $generateBackupCodes,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock
    ) {}

    /**
     * @param ConfirmMfaEnrollmentData $data
     *
     * @return BackupCodeSet
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function execute(ConfirmMfaEnrollmentData $data) : BackupCodeSet
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $userId = new UserId(value: $user->id);
        $record = $this->mfaStore->findPendingEnrollment(userId: $userId);

        if ($record === null) {
            throw MfaEnrollmentFailed::missingEnrollment();
        }

        $now = $this->clock->now();

        if ($record->isExpiredAt(moment: $now)) {
            $this->mfaStore->cancelEnrollment(userId: $userId);
            throw MfaEnrollmentFailed::expiredEnrollment();
        }

        $verification = $this->totp->verify(secret: $record->secret, code: $data->code, moment: $now);

        if (! $verification->accepted || $verification->timeStep === null) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.mfa.enrollment.failed',
                                               occurredAt: $now,
                                               context   : [
                                                               'user_id' => $user->id,
                                                               'reason'  => $verification->reason,
                                                           ]
                                           ));

            throw MfaEnrollmentFailed::invalidCode();
        }

        $generated = $this->generateBackupCodes->execute();
        $this->mfaStore->saveMethod(record: new MfaMethodRecord(
                                                userId              : $userId,
                                                method              : $record->method,
                                                secret              : $record->secret,
                                                enabledAt           : $now,
                                                backupCodes         : $generated->records,
                                                lastAcceptedTimeStep: $verification->timeStep
                                            ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.enrollment.completed',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id' => $user->id,
                                                           'method'  => $record->method->value,
                                                       ]
                                       ));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.enabled',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id' => $user->id,
                                                           'method'  => $record->method->value,
                                                       ]
                                       ));
        $context = $this->currentAuthentication->read();
        $this->currentAuthentication->store(context: AuthenticationContext::authenticated(
            user                : new AuthenticatedUser(
                                      id           : $user->id,
                                      email        : $user->email,
                                      username     : $user->username,
                                      roles        : $user->roles,
                                      permissions  : $user->permissions,
                                      emailVerified: $user->emailVerified,
                                      mfaEnabled   : true
                                  ),
            mode                : $context->mode(),
            sessionId           : $context->sessionId(),
            accessTokenId       : $context->accessTokenId(),
            accessTokenExpiresAt: $context->accessTokenExpiresAt(),
            refreshTokenId      : $context->refreshTokenId(),
            mfaVerifiedAt       : $now
        ));

        return $generated->backupCodeSet;
    }
}
