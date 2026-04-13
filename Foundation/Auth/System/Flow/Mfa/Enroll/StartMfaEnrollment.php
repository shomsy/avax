<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Enroll;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\MfaEnrollment;
use Avax\Auth\System\Flow\Mfa\MfaEnrollmentFailed;
use Avax\Auth\System\Flow\Mfa\MfaEnrollmentRecord;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaStatus;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\TotpInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Starts TOTP MFA enrollment for the current user.
 */
final readonly class StartMfaEnrollment
{
    public function __construct(
        #[\SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface                            $mfaStore,
        private TotpInterface                                $totp,
        private AuditLogInterface                            $auditLog,
        private Clock                                        $clock,
        private string                                       $issuer = 'Avax Auth',
        private int                                          $expiresAfterSeconds = 900
    ) {}

    /**
     * @throws Unauthenticated
     * @throws MfaEnrollmentFailed
     */
    public function execute() : MfaEnrollment
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $userId = new UserId(value: $user->id);

        if ($this->mfaStore->isEnabled(userId: $userId)) {
            throw MfaEnrollmentFailed::alreadyEnabled();
        }

        $startedAt = $this->clock->now();
        $record    = new MfaEnrollmentRecord(
            userId      : $userId,
            method      : MfaMethod::TOTP,
            accountLabel: $user->email,
            issuer      : $this->issuer,
            secret      : $this->totp->generateSecret(),
            startedAt   : $startedAt,
            expiresAt   : $startedAt->modify(modifier: "+{$this->expiresAfterSeconds} seconds")
        );
        $this->mfaStore->startEnrollment(record: $record);
        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.mfa.enrollment.started',
                                    occurredAt: $startedAt,
                                    context   : [
                                                    'user_id' => $user->id,
                                                    'method'  => $record->method->value,
                                                ]
                                ));

        return new MfaEnrollment(
            method      : $record->method,
            status      : MfaStatus::ENROLLMENT_PENDING,
            accountLabel: $record->accountLabel,
            issuer      : $record->issuer,
            secret      : $record->secret,
            otpauthUri  : $this->totp->provisioningUri(issuer: $record->issuer, accountLabel: $record->accountLabel, secret: $record->secret),
            startedAt   : $record->startedAt,
            expiresAt   : $record->expiresAt
        );
    }
}
