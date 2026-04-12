<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryChallenge;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryRecord;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Starts a user MFA recovery flow with anti-enumeration behavior.
 */
final readonly class StartMfaRecovery
{
    public function __construct(
        private UserSourceInterface $userSource,
        private MfaStoreInterface   $mfaStore,
        private AuditLogInterface   $auditLog,
        private Clock               $clock,
        private int                 $expiresAfterSeconds = 900,
        private AttemptThrottle|null $attemptThrottle = null
    ) {}

    public function execute(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        $throttleKey = $this->throttleKey($data->email, $data->ipAddress);

        try {
            $this->attemptThrottle?->check($throttleKey);
        } catch (AttemptThrottleExceeded $exception) {
            $this->auditLog->record(new AuditEvent(
                name      : 'auth.mfa.recovery.throttled',
                occurredAt: $this->clock->now(),
                context   : [
                    'email'       => strtolower($data->email),
                    'ip_address'  => $data->ipAddress,
                    'user_agent'  => $data->userAgent,
                    'retry_after' => $exception->retryAfter(),
                ]
            ));

            return MfaRecoveryChallenge::hidden();
        }

        $this->attemptThrottle?->recordAttempt($throttleKey);
        $user = $this->userSource->findByEmail($data->email);

        if ($user === null || ! $user->isActive() || ! $this->mfaStore->isEnabled($user->getId())) {
            $this->auditLog->record(new AuditEvent(
                name      : 'auth.mfa.recovery.started',
                occurredAt: $this->clock->now(),
                context   : [
                    'email'      => strtolower($data->email),
                    'dispatched' => false,
                    'ip_address' => $data->ipAddress,
                    'user_agent' => $data->userAgent,
                ]
            ));

            return MfaRecoveryChallenge::hidden();
        }

        return $this->issue($user->getId()->value, $data);
    }

    private function issue(int $userId, BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt  = $this->clock->now()->modify("+{$this->expiresAfterSeconds} seconds");
        $tokenHash  = $this->hash($plainToken);
        $this->mfaStore->saveRecovery(new MfaRecoveryRecord(
                                          tokenHash: $tokenHash,
                                          userId   : new \Avax\Auth\System\Capability\User\UserId($userId),
                                          expiresAt: $expiresAt
                                      ));
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.recovery.started',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'    => $userId,
                                                    'dispatched' => true,
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));

        return new MfaRecoveryChallenge(
            dispatched: true,
            token     : $plainToken,
            expiresAt : $expiresAt
        );
    }

    private function hash(#[SensitiveParameter] string $token) : string
    {
        return hash('sha256', $token);
    }

    private function throttleKey(string $email, string|null $ipAddress) : string
    {
        $normalizedEmail = strtolower(trim($email));

        if ($ipAddress === null || $ipAddress === '') {
            return 'mfa_recovery:' . $normalizedEmail;
        }

        return 'mfa_recovery:' . $normalizedEmail . '|' . trim($ipAddress);
    }
}
