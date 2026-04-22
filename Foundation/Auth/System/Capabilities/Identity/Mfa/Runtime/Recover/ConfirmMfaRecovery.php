<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaRecoveryFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Confirms MFA recovery and safely resets the MFA method.
 */
final readonly class ConfirmMfaRecovery
{
    public function __construct(
        private MfaStoreInterface                                     $mfaStore,
        private MfaChallengeStoreInterface                            $mfaChallengeStore,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        #[SensitiveParameter] private CurrentAuthentication|null      $currentAuthentication = null,
        private IdentityInterface|null                                $identity = null
    )
    {
    }

    /**
     * @throws MfaRecoveryFailed
     */
    public function execute(ConfirmMfaRecoveryData $data) : void
    {
        $tokenHash = $this->hash(token: $data->token);
        $record    = $this->mfaStore->findRecovery(tokenHash: $tokenHash);

        if ($record === null) {
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

        if ($context !== null && $context->user()?->id === $record->userId->value) {
            $this->identity?->clear(context: $context);
            $this->currentAuthentication->clear();
        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.recovery.used',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $record->userId->value,
                                                           'ip_address' => $data->ipAddress,
                                                           'user_agent' => $data->userAgent,
                                                       ]
                                       ));
        $this->auditLog->record(event: new AuditEvent(
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
