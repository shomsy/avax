<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Starts password reset without leaking user existence.
 */
final readonly class BeginPasswordReset
{
    public function __construct(
        private UserSourceInterface                                $userSource,
        #[\SensitiveParameter] private PasswordResetStoreInterface $passwordResetStore,
        private AuditLogInterface                                  $auditLog,
        private Clock                                              $clock,
        private int                                                $expiresAfterSeconds = 3600,
        private AttemptThrottle|null                               $attemptThrottle = null
    ) {}

    public function execute(BeginPasswordResetData $data) : PasswordResetChallenge
    {
        $throttleKey = $this->throttleKey(email: $data->email, ipAddress: $data->ipAddress);

        try {
            $this->attemptThrottle?->check(key: $throttleKey);
        } catch (AttemptThrottleExceeded $exception) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.password_reset.throttled',
                occurredAt: $this->clock->now(),
                context   : [
                    'email'       => strtolower($data->email),
                    'ip_address'  => $data->ipAddress,
                    'user_agent'  => $data->userAgent,
                    'retry_after' => $exception->retryAfter(),
                ]
            ));

            return PasswordResetChallenge::hidden();
        }

        $this->attemptThrottle?->recordAttempt(key: $throttleKey);
        $user = $this->userSource->findByEmail(email: $data->email);

        if ($user === null || ! $user->isActive()) {
            $this->auditLog->record(event: new AuditEvent(
                                        name      : 'auth.password_reset.requested',
                                        occurredAt: $this->clock->now(),
                                        context   : [
                                                        'email'      => strtolower($data->email),
                                                        'dispatched' => false,
                                                        'ip_address' => $data->ipAddress,
                                                        'user_agent' => $data->userAgent,
                                                    ]
                                    ));

            return PasswordResetChallenge::hidden();
        }

        $challenge = $this->passwordResetStore->issue(
            userId   : $user->getId(),
            expiresAt: $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds")
        );

        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.password_reset.requested',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'    => $user->getId()->value,
                                                    'dispatched' => true,
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));

        return $challenge;
    }

    private function throttleKey(#[\SensitiveParameter] string $email, #[\SensitiveParameter] string|null $ipAddress) : string
    {
        $normalizedEmail = strtolower(trim($email));

        if ($ipAddress === null || $ipAddress === '') {
            return 'password_reset:' . $normalizedEmail;
        }

        return 'password_reset:' . $normalizedEmail . '|' . trim($ipAddress);
    }
}
