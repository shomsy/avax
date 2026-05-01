<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\BackupCodeSet;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Backup\GenerateBackupCodes;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaEnrollmentFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use Random\RandomException;
use SensitiveParameter;

/**
 * Completes MFA enrollment only after a valid first TOTP proof.
 */
final readonly class ConfirmMfaEnrollment
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface $mfaStore,
        private TotpInterface $totp,
        #[SensitiveParameter]
        private GenerateBackupCodes $generateBackupCodes,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws RandomException
     */
    public function execute(ConfirmMfaEnrollmentData $data): BackupCodeSet
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated;
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
                    'reason' => $verification->reason,
                ],
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
            lastAcceptedTimeStep: $verification->timeStep,
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.enrollment.completed',
            occurredAt: $now,
            context   : [
                'user_id' => $user->id,
                'method' => $record->method->value,
            ],
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.enabled',
            occurredAt: $now,
            context   : [
                'user_id' => $user->id,
                'method' => $record->method->value,
            ],
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
                mfaEnabled   : true,
            ),
            mode                : $context->mode(),
            sessionId           : $context->sessionId(),
            accessTokenId       : $context->accessTokenId(),
            accessTokenExpiresAt: $context->accessTokenExpiresAt(),
            refreshTokenId      : $context->refreshTokenId(),
            mfaVerifiedAt       : $now,
        ));

        return $generated->backupCodeSet;
    }
}
