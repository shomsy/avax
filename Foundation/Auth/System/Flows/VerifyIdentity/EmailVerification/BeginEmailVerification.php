<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\VerifyIdentity\EmailVerification;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Issues email verification challenges without leaking account presence.
 */
final readonly class BeginEmailVerification
{
    private int                             $expiresAfterSeconds;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private EmailVerificationStoreInterface $emailVerificationStore;
    private UserSourceInterface             $userSource;

    public function __construct(
        UserSourceInterface                                   $userSource,
        #[SensitiveParameter] EmailVerificationStoreInterface $emailVerificationStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        int                                                   $expiresAfterSeconds = 86400
    )
    {
        $this->userSource             = $userSource;
        $this->emailVerificationStore = $emailVerificationStore;
        $this->auditLog               = $auditLog;
        $this->clock                  = $clock;
        $this->expiresAfterSeconds    = $expiresAfterSeconds;
    }

    /**
     * @throws DateMalformedStringException
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
