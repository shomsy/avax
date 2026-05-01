<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Issues email verification challenges without leaking account presence.
 */
final readonly class BeginEmailVerification
{
    public function __construct(
        private UserSourceInterface $userSource,
        #[SensitiveParameter]
        private EmailVerificationStoreInterface $emailVerificationStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        private int $expiresAfterSeconds = 86400,
    ) {}

    /**
     * @throws DateMalformedStringException
     */
    public function execute(BeginEmailVerificationData $data): EmailVerificationChallenge
    {
        $user = $this->userSource->findByEmail(email: $data->email);

        if ($user === null || ! $user->isActive()) {
            return EmailVerificationChallenge::hidden();
        }

        $challenge = $this->emailVerificationStore->issue(
            userId   : $user->getId(),
            expiresAt: $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds"),
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.email_verification.requested',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->getId()->value,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));

        return $challenge;
    }
}
