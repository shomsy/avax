<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Verify;

use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Issues email verification challenges without leaking account presence.
 */
final readonly class BeginEmailVerification
{
    public function __construct(
        private UserSourceInterface                                   $userSource,
        #[SensitiveParameter] private EmailVerificationStoreInterface $emailVerificationStore,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        private int                                                   $expiresAfterSeconds = 86400
    ) {}

    /**
     * @throws \DateMalformedStringException
     */
    public function execute(BeginEmailVerificationData $data) : EmailVerificationChallenge
    {
        $user = $this->userSource->findByEmail(email: $data->email);

        if ($user === null || ! $user->isActive()) {
            return EmailVerificationChallenge::hidden();
        }

        $challenge = $this->emailVerificationStore->issue(
            userId   : $user->getId(),
            expiresAt: $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds")
        );

        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.email_verification.requested',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'    => $user->getId()->value,
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));

        return $challenge;
    }
}
