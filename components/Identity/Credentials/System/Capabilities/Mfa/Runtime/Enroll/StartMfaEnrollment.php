<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enroll;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaMethod;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Enums\MfaStatus;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaEnrollmentFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaEnrollmentRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Starts TOTP MFA enrollment for the current user.
 */
final readonly class StartMfaEnrollment
{
    private string $issuer;

    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface     $mfaStore,
        private TotpInterface         $totp,
        private AuditLogInterface     $auditLog,
        private Clock                 $clock,
        ?string                       $issuer = null,
        private int                   $expiresAfterSeconds = 900,
    )
    {
        $issuer       ??= 'Avax Auth';
        $this->issuer = $issuer;
    }

    /**
     * @throws Unauthenticated
     * @throws MfaEnrollmentFailed
     * @throws DateMalformedStringException
     */
    public function execute() : MfaEnrollment
    {
        $user = $this->currentAuthentication->read()->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        $userId = new UserId(value: $user->id);

        if ($this->mfaStore->isEnabled(userId: $userId)) {
            throw MfaEnrollmentFailed::alreadyEnabled();
        }

        $startedAt           = $this->clock->now();
        $mfaEnrollmentRecord = new MfaEnrollmentRecord(
            userId      : $userId,
            method      : MfaMethod::TOTP,
            accountLabel: $user->email,
            issuer      : $this->issuer,
            secret      : $this->totp->generateSecret(),
            startedAt   : $startedAt,
            expiresAt   : $startedAt->modify(modifier: sprintf('+%d seconds', $this->expiresAfterSeconds)),
        );
        $this->mfaStore->startEnrollment(record: $mfaEnrollmentRecord);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.enrollment.started',
                                           occurredAt: $startedAt,
                                           context   : [
                                                           'user_id' => $user->id,
                                                           'method'  => $mfaEnrollmentRecord->method->value,
                                                       ],
                                       ));

        return new MfaEnrollment(
            method      : $mfaEnrollmentRecord->method,
            status      : MfaStatus::ENROLLMENT_PENDING,
            accountLabel: $mfaEnrollmentRecord->accountLabel,
            issuer      : $mfaEnrollmentRecord->issuer,
            secret      : $mfaEnrollmentRecord->secret,
            otpauthUri  : $this->totp->provisioningUri(issuer: $mfaEnrollmentRecord->issuer, accountLabel: $mfaEnrollmentRecord->accountLabel, secret: $mfaEnrollmentRecord->secret),
            startedAt   : $mfaEnrollmentRecord->startedAt,
            expiresAt   : $mfaEnrollmentRecord->expiresAt,
        );
    }
}
