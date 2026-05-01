<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Completes email verification by consuming a one-time token.
 */
final readonly class VerifyEmail
{
    public function __construct(
        #[SensitiveParameter]
        private EmailVerificationStoreInterface $emailVerificationStore,
        #[SensitiveParameter]
        private EmailVerificationStateStoreInterface $emailVerificationStateStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
    ) {}

    public function execute(VerifyEmailData $verifyEmailData) : bool
    {
        $userId = $this->emailVerificationStore->consume(token: $verifyEmailData->token, now: $this->clock->now());

        if (! $userId instanceof UserId) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.email_verification.failed',
                occurredAt: $this->clock->now(),
                context   : [
                                'reason' => 'invalid_token',
                                'ip_address' => $verifyEmailData->ipAddress,
                                'user_agent' => $verifyEmailData->userAgent,
                ],
            ));

            return false;
        }

        $this->emailVerificationStateStore->markVerified(userId: $userId);
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.email_verification.completed',
            occurredAt: $this->clock->now(),
            context   : [
                            'user_id' => $userId->value,
                            'ip_address' => $verifyEmailData->ipAddress,
                            'user_agent' => $verifyEmailData->userAgent,
            ],
        ));

        return true;
    }
}
