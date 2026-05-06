<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
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
    ) {
    }

    /**
     * @throws DateMalformedStringException
     */
    public function execute(BeginEmailVerificationData $beginEmailVerificationData): EmailVerificationChallenge
    {
        $user = $this->userSource->findByEmail(email: $beginEmailVerificationData->email);

        if (! $user instanceof User || ! $user->isActive()) {
            return EmailVerificationChallenge::hidden();
        }

        $emailVerificationChallenge = $this->emailVerificationStore->issue(
            userId   : $user->getId(),
            expiresAt: $this->clock->now()->modify(modifier: sprintf('+%d seconds', $this->expiresAfterSeconds)),
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.email_verification.requested',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->getId()->value,
                'ip_address' => $beginEmailVerificationData->ipAddress,
                'user_agent' => $beginEmailVerificationData->userAgent,
            ],
        ));

        return $emailVerificationChallenge;
    }
}
