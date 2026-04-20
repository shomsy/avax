<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Enroll;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Mfa\MfaEnrollment;
use Avax\Auth\System\Flows\Mfa\MfaEnrollmentFailed;
use Avax\Auth\System\Flows\Mfa\MfaEnrollmentRecord;
use Avax\Auth\System\Flows\Mfa\MfaMethod;
use Avax\Auth\System\Flows\Mfa\MfaStatus;
use Avax\Auth\System\Flows\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flows\Mfa\TotpInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Starts TOTP MFA enrollment for the current user.
 */
final readonly class StartMfaEnrollment
{
    private int                   $expiresAfterSeconds;
    private string                $issuer;
    private Clock                 $clock;
    private AuditLogInterface     $auditLog;
    private TotpInterface         $totp;
    private MfaStoreInterface     $mfaStore;
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        MfaStoreInterface                           $mfaStore,
        TotpInterface                               $totp,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock,
        string|null                                 $issuer = null,
        int                                         $expiresAfterSeconds = 900
    )
    {
        $issuer                      ??= 'Avax Auth';
        $this->currentAuthentication = $currentAuthentication;
        $this->mfaStore              = $mfaStore;
        $this->totp                  = $totp;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->issuer                = $issuer;
        $this->expiresAfterSeconds   = $expiresAfterSeconds;
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
