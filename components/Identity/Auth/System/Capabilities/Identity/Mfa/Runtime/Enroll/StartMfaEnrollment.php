<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enroll;

use Avax\Components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaStatus;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaEnrollmentFailed;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Records\MfaEnrollmentRecord;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Starts TOTP MFA enrollment for the current user.
 */
final readonly class StartMfaEnrollment
{
    private string $issuer;

    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface                           $mfaStore,
        private TotpInterface                               $totp,
        private AuditLogInterface                           $auditLog,
        private Clock                                       $clock,
        string|null                                         $issuer = null,
        private int                                         $expiresAfterSeconds = 900
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
