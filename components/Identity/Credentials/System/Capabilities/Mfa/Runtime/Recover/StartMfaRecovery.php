<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover;

use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Access\System\Capabilities\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaRecoveryRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

/**
 * Starts a user MFA recovery flow with anti-enumeration behavior.
 */
final readonly class StartMfaRecovery
{
    private int $expiresAfterSeconds;

    public function __construct(
        private UserSourceInterface $userSource,
        private MfaStoreInterface $mfaStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        int $expiresAfterSeconds = null,
        private ?AttemptThrottle $attemptThrottle = null,
    ) {
        $expiresAfterSeconds ??= 900;
        $this->expiresAfterSeconds = $expiresAfterSeconds;
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BeginMfaRecoveryData $data): MfaRecoveryChallenge
    {
        $throttleKey = $this->throttleKey(email: $data->email, ipAddress: $data->ipAddress);

        try {
            $this->attemptThrottle?->check(key: $throttleKey);
        } catch (AttemptThrottleExceeded $exception) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.mfa.recovery.throttled',
                occurredAt: $this->clock->now(),
                context   : [
                                'email'      => strtolower(string: $data->email),
                                'ip_address' => $data->ipAddress,
                                'user_agent' => $data->userAgent,
                    'retry_after' => $exception->retryAfter(),
                ],
            ));

            return MfaRecoveryChallenge::hidden();
        }

        $this->attemptThrottle?->recordAttempt(key: $throttleKey);
        $user = $this->userSource->findByEmail(email: $data->email);

        if ($user === null || ! $user->isActive() || ! $this->mfaStore->isEnabled(userId: $user->getId())) {
            $this->auditLog->record(event: new AuditEvent(
                name      : 'auth.mfa.recovery.started',
                occurredAt: $this->clock->now(),
                context   : [
                                'email' => strtolower(string: $data->email),
                    'dispatched' => false,
                    'ip_address' => $data->ipAddress,
                    'user_agent' => $data->userAgent,
                ],
            ));

            return MfaRecoveryChallenge::hidden();
        }

        return $this->issue(userId: $user->getId()->value, data: $data);
    }

    private function throttleKey(#[SensitiveParameter] string $email, #[SensitiveParameter] ?string $ipAddress): string
    {
        $normalizedEmail = strtolower(string: trim(string: $email));

        if ($ipAddress === null || $ipAddress === '') {
            return 'mfa_recovery:' . $normalizedEmail;
        }

        return 'mfa_recovery:' . $normalizedEmail . '|' . trim(string: $ipAddress);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    private function issue(int $userId, BeginMfaRecoveryData $data): MfaRecoveryChallenge
    {
        $plainToken = bin2hex(string: random_bytes(length: 32));
        $expiresAt = $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds");
        $tokenHash = $this->hash(token: $plainToken);
        $this->mfaStore->saveRecovery(record: new MfaRecoveryRecord(
            tokenHash: $tokenHash,
            userId   : new UserId(value: $userId),
            expiresAt: $expiresAt,
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.recovery.started',
            occurredAt: $this->clock->now(),
            context   : [
                            'user_id' => $userId,
                'dispatched' => true,
                'ip_address' => $data->ipAddress,
                'user_agent' => $data->userAgent,
            ],
        ));

        return new MfaRecoveryChallenge(
            dispatched: true,
            token     : $plainToken,
            expiresAt : $expiresAt,
        );
    }

    private function hash(#[SensitiveParameter] string $token): string
    {
        return hash(algo: 'sha256', data: $token);
    }
}
