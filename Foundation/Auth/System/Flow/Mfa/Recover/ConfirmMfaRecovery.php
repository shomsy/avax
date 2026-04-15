<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Recover;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
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
    private IdentityInterface|null          $identity;
    private CurrentAuthentication|null      $currentAuthentication;
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private SessionRegistryInterface|null   $sessionRegistry;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private MfaChallengeStoreInterface      $mfaChallengeStore;
    private MfaStoreInterface               $mfaStore;

    public function __construct(
        MfaStoreInterface                                      $mfaStore,
        MfaChallengeStoreInterface                             $mfaChallengeStore,
        AuditLogInterface                                      $auditLog,
        Clock                                                  $clock,
        #[\SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        #[\SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null,
        #[\SensitiveParameter] CurrentAuthentication|null      $currentAuthentication = null,
        IdentityInterface|null                                 $identity = null
    )
    {
        $this->mfaStore              = $mfaStore;
        $this->mfaChallengeStore     = $mfaChallengeStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
        $this->refreshTokenStore     = $refreshTokenStore;
        $this->currentAuthentication = $currentAuthentication;
        $this->identity              = $identity;
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

        if ($context?->user()?->id === $record->userId->value) {
            $this->identity?->clear(context: $context);
            $this->currentAuthentication?->clear();
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
