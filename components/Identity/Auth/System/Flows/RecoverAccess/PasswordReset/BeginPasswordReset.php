<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use SensitiveParameter;

/**
 * Starts password reset without leaking user existence.
 */
final readonly class BeginPasswordReset
{
    private int $expiresAfterSeconds;

    public function __construct(
        private UserSourceInterface $userSource,
        #[SensitiveParameter]
        private PasswordResetStoreInterface $passwordResetStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        ?int $expiresAfterSeconds = null,
        private ?AttemptThrottle $attemptThrottle = null,
    ) {
        $expiresAfterSeconds ??= 3600;
        $this->expiresAfterSeconds = $expiresAfterSeconds;
    }

    /**
     * @throws DateMalformedStringException
     */
    public function execute(BeginPasswordResetData $beginPasswordResetData): PasswordResetChallenge
    {
        $throttleKey = $this->throttleKey(email: $beginPasswordResetData->email, ipAddress: $beginPasswordResetData->ipAddress);

        try {
            $this->attemptThrottle?->check(key: $throttleKey);
        } catch (AttemptThrottleExceeded $attemptThrottleExceeded) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.password_reset.throttled',
                occurredAt: $this->clock->now(),
                context   : [
                    'email' => strtolower(string: $beginPasswordResetData->email),
                    'ip_address' => $beginPasswordResetData->ipAddress,
                    'user_agent' => $beginPasswordResetData->userAgent,
                    'retry_after' => $attemptThrottleExceeded->retryAfter(),
                ],
            ));

            return PasswordResetChallenge::hidden();
        }

        $this->attemptThrottle?->recordAttempt(key: $throttleKey);
        $user = $this->userSource->findByEmail(email: $beginPasswordResetData->email);

        if (! $user instanceof User || ! $user->isActive()) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.password_reset.requested',
                occurredAt: $this->clock->now(),
                context   : [
                    'email' => strtolower(string: $beginPasswordResetData->email),
                    'dispatched' => false,
                    'ip_address' => $beginPasswordResetData->ipAddress,
                    'user_agent' => $beginPasswordResetData->userAgent,
                ],
            ));

            return PasswordResetChallenge::hidden();
        }

        $passwordResetChallenge = $this->passwordResetStore->issue(
            userId   : $user->getId(),
            expiresAt: $this->clock->now()->modify(modifier: sprintf('+%d seconds', $this->expiresAfterSeconds)),
        );

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.password_reset.requested',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->getId()->value,
                'dispatched' => true,
                'ip_address' => $beginPasswordResetData->ipAddress,
                'user_agent' => $beginPasswordResetData->userAgent,
            ],
        ));

        return $passwordResetChallenge;
    }

    private function throttleKey(#[SensitiveParameter] string $email, #[SensitiveParameter] ?string $ipAddress): string
    {
        $normalizedEmail = strtolower(string: trim(string: $email));

        if ($ipAddress === null || $ipAddress === '') {
            return 'password_reset:'.$normalizedEmail;
        }

        return 'password_reset:'.$normalizedEmail.'|'.trim(string: $ipAddress);
    }
}
