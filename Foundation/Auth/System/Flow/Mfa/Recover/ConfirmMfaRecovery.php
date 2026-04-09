<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryFailed;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Confirms MFA recovery and safely resets the MFA method.
 */
final readonly class ConfirmMfaRecovery
{
    public function __construct(
        private MfaStoreInterface               $mfaStore,
        private MfaChallengeStoreInterface      $mfaChallengeStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private CurrentAuthentication|null      $currentAuthentication = null,
        private IdentityInterface|null          $identity = null
    ) {}

    /**
     * @throws MfaRecoveryFailed
     */
    public function execute(ConfirmMfaRecoveryData $data) : void
    {
        $tokenHash = $this->hash($data->token);
        $record    = $this->mfaStore->findRecovery($tokenHash);

        if ($record === null) {
            throw MfaRecoveryFailed::invalidToken();
        }

        $now = $this->clock->now();

        if ($record->isExpiredAt($now)) {
            $this->mfaStore->forgetRecovery($tokenHash);
            throw MfaRecoveryFailed::expiredToken();
        }

        $this->mfaStore->disable($record->userId);
        $this->mfaStore->forgetRecovery($tokenHash);
        $this->mfaChallengeStore->forgetForUser($record->userId);
        $this->refreshTokenStore?->revokeUser($record->userId);

        $context = $this->currentAuthentication?->read();

        if ($context?->user()?->id === $record->userId->value) {
            $this->identity?->clear($context);
            $this->currentAuthentication?->clear();
        }

        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.recovery.used',
                                    occurredAt: $now,
                                    context   : [
                                                    'user_id'    => $record->userId->value,
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));
        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.reset',
                                    occurredAt: $now,
                                    context   : [
                                                    'user_id' => $record->userId->value,
                                                    'reason'  => 'recovery',
                                                ]
                                ));
    }

    private function hash(#[SensitiveParameter] string $token) : string
    {
        return hash('sha256', $token);
    }
}
