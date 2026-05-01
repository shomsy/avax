<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Recover;

use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\MfaRecoveryFailed;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Records\MfaRecoveryRecord;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

/**
 * Confirms MFA recovery and safely resets the MFA method.
 */
final readonly class ConfirmMfaRecovery
{
    public function __construct(
        private MfaStoreInterface $mfaStore,
        private MfaChallengeStoreInterface $mfaChallengeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter]
        private ?SessionRegistryInterface $sessionRegistry = null,
        #[SensitiveParameter]
        private ?RefreshTokenStoreInterface $refreshTokenStore = null,
        #[SensitiveParameter]
        private ?CurrentAuthentication $currentAuthentication = null,
        private ?IdentityInterface $identity = null,
    ) {}

    /**
     * @throws MfaRecoveryFailed
     */
    public function execute(ConfirmMfaRecoveryData $confirmMfaRecoveryData) : void
    {
        $tokenHash = $this->hash(token: $confirmMfaRecoveryData->token);
        $record = $this->mfaStore->findRecovery(tokenHash: $tokenHash);

        if (! $record instanceof MfaRecoveryRecord) {
            throw MfaRecoveryFailed::invalidToken();
        }

        $now = $this->clock->now();

        if ($record->isExpiredAt(moment: $now)) {
            $this->mfaStore->forgetRecovery(tokenHash: $tokenHash);

            throw MfaRecoveryFailed::expiredToken();
        }

        $this->mfaStore->disable(userId: $record->userId);
        $this->mfaStore->forgetRecovery(tokenHash: $tokenHash);

        $this->mfaChallengeStore->forgetForUser(userId: $record->userId);
        $this->sessionRegistry?->revokeForUser(userId: $record->userId, revokedAt: $now, reason: 'mfa_recovery');
        $this->refreshTokenStore?->revokeUser(userId: $record->userId);

        $context = $this->currentAuthentication?->read();

        if ($context instanceof AuthenticationContext && $context->user()?->id === $record->userId->value) {
            $this->identity?->clear(context: $context);
            $this->currentAuthentication->clear();
        }

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.recovery.used',
            occurredAt: $now,
            context   : [
                            'user_id' => $record->userId->value,
                            'ip_address' => $confirmMfaRecoveryData->ipAddress,
                            'user_agent' => $confirmMfaRecoveryData->userAgent,
            ],
        ));
        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.reset',
            occurredAt: $now,
            context   : [
                'user_id' => $record->userId->value,
                'reason' => 'recovery',
            ],
        ));
    }

    private function hash(#[SensitiveParameter] string $token): string
    {
        return hash(algo: 'sha256', data: $token);
    }
}
