<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover;

use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottleExceeded;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Records\MfaRecoveryRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use Random\RandomException;
use SensitiveParameter;

/**
 * Starts a user MFA recovery flow with anti-enumeration behavior.
 */
final readonly class StartMfaRecovery
{
    private int                  $expiresAfterSeconds;

    public function __construct(
        private UserSourceInterface  $userSource,
        private MfaStoreInterface    $mfaStore,
        private AuditLogInterface    $auditLog,
        private Clock                $clock,
        int|null             $expiresAfterSeconds = null,
        private AttemptThrottle|null $attemptThrottle = null
    )
    {
        $expiresAfterSeconds       ??= 900;
        $this->expiresAfterSeconds = $expiresAfterSeconds;
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        $throttleKey = $this->throttleKey(email: $data->email, ipAddress: $data->ipAddress);

        try {
            $this->attemptThrottle?->check(key: $throttleKey);
        } catch (AttemptThrottleExceeded $exception) {
            $this->auditLog->record(event: new AuditEvent(
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

        $this->attemptThrottle?->recordAttempt(key: $throttleKey);
        $user = $this->userSource->findByEmail(email: $data->email);

        if ($user === null || ! $user->isActive() || ! $this->mfaStore->isEnabled(userId: $user->getId())) {
            $this->auditLog->record(event: new AuditEvent(
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

        return $this->issue(userId: $user->getId()->value, data: $data);
    }

    private function throttleKey(#[SensitiveParameter] string $email, #[SensitiveParameter] string|null $ipAddress) : string
    {
        $normalizedEmail = strtolower(trim($email));

        if ($ipAddress === null || $ipAddress === '') {
            return 'mfa_recovery:' . $normalizedEmail;
        }

        return 'mfa_recovery:' . $normalizedEmail . '|' . trim($ipAddress);
    }

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    private function issue(int $userId, BeginMfaRecoveryData $data) : MfaRecoveryChallenge
    {
        $plainToken = bin2hex(random_bytes(32));
        $expiresAt  = $this->clock->now()->modify(modifier: "+{$this->expiresAfterSeconds} seconds");
        $tokenHash  = $this->hash(token: $plainToken);
        $this->mfaStore->saveRecovery(record: new MfaRecoveryRecord(
                                                  tokenHash: $tokenHash,
                                                  userId   : new UserId(value: $userId),
                                                  expiresAt: $expiresAt
                                              ));
        $this->auditLog->record(event: new AuditEvent(
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
}
