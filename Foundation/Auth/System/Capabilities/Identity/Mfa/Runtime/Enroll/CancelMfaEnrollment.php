<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Mfa\Enroll;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Mfa\MfaStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Cancels a pending MFA enrollment safely.
 */
final readonly class CancelMfaEnrollment
{
    private Clock                 $clock;
    private AuditLogInterface     $auditLog;
    private MfaStoreInterface     $mfaStore;
    private CurrentAuthentication $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication $currentAuthentication,
        MfaStoreInterface                           $mfaStore,
        AuditLogInterface                           $auditLog,
        Clock                                       $clock
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->mfaStore              = $mfaStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
    }

    /**
     * @throws Unauthenticated
     */
    public function execute() : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $this->mfaStore->cancelEnrollment(userId: new UserId(value: $user->id));
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.enrollment.cancelled',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id' => $user->id,
                                                       ]
                                       ));
    }
}
