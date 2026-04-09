<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Enroll;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Cancels a pending MFA enrollment safely.
 */
final readonly class CancelMfaEnrollment
{
    public function __construct(
        private CurrentAuthentication $currentAuthentication,
        private MfaStoreInterface     $mfaStore,
        private AuditLogInterface     $auditLog,
        private Clock                 $clock
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute() : void
    {
        $user = $this->currentAuthentication->read()->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $this->mfaStore->cancelEnrollment(new UserId($user->id));
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.enrollment.cancelled',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id' => $user->id,
                                                ]
                                ));
    }
}
