<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Enroll;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\Backup\GenerateBackupCodes;
use Avax\Auth\System\Flow\Mfa\BackupCodeSet;
use Avax\Auth\System\Flow\Mfa\MfaEnrollmentFailed;
use Avax\Auth\System\Flow\Mfa\MfaMethodRecord;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\TotpInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Completes MFA enrollment only after a valid first TOTP proof.
 */
final readonly class ConfirmMfaEnrollment
{
    private Clock                 $clock;
    private AuditLogInterface     $auditLog;
    private GenerateBackupCodes   $generateBackupCodes;
    private TotpInterface         $totp;
    private MfaStoreInterface     $mfaStore;
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        MfaStoreInterface                           $mfaStore,
        TotpInterface                               $totp,
        #[SensitiveParameter] GenerateBackupCodes   $generateBackupCodes,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->mfaStore              = $mfaStore;
        $this->totp                  = $totp;
        $this->generateBackupCodes   = $generateBackupCodes;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
    }

    /**
     * @throws Unauthenticated
     * @throws MfaEnrollmentFailed
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
