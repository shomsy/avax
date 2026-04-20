<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Verify;

use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Completes email verification by consuming a one-time token.
 */
final readonly class VerifyEmail
{
    private Clock                                $clock;
    private AuditLogInterface                    $auditLog;
    private EmailVerificationStateStoreInterface $emailVerificationState;
    private EmailVerificationStoreInterface      $emailVerificationStore;

    public function __construct(
        #[SensitiveParameter] EmailVerificationStoreInterface      $emailVerificationStore,
        #[SensitiveParameter] EmailVerificationStateStoreInterface $emailVerificationState,
        AuditLogInterface                                          $auditLog,
        Clock                                                      $clock
    )
    {
        $this->emailVerificationStore = $emailVerificationStore;
        $this->emailVerificationState = $emailVerificationState;
        $this->auditLog               = $auditLog;
        $this->clock                  = $clock;
    }

    public function execute(VerifyEmailData $data) : bool
    {
        $userId = $this->emailVerificationStore->consume(token: $data->token, now: $this->clock->now());

        if ($userId === null) {
            $this->auditLog->record(event: new AuditEvent(
                                               name      : 'auth.email_verification.failed',
                                               occurredAt: $this->clock->now(),
                                               context   : [
                                                               'reason'     => 'invalid_token',
                                                               'ip_address' => $data->ipAddress,
                                                               'user_agent' => $data->userAgent,
                                                           ]
                                           ));

            return false;
        }

        $this->emailVerificationState->markVerified(userId: $userId);
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.email_verification.completed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id'    => $userId->value,
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));

        return true;
    }
}
