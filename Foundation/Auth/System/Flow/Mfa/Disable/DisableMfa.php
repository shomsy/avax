<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Mfa\Disable;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Disables MFA after fresh proof and clears recovery material.
 */
final readonly class DisableMfa
{
    public function __construct(
        private CurrentAuthentication           $currentAuthentication,
        private RequireFreshMfa                 $requireFreshMfa,
        private MfaStoreInterface               $mfaStore,
        private MfaChallengeStoreInterface      $mfaChallengeStore,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    /**
     * @throws Unauthenticated
     * @throws \Avax\Auth\System\Flow\Mfa\FreshMfaRequired
     */
    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if (! $user->mfaEnabled) {
            return;
        }

        $this->requireFreshMfa->execute();
        $userId = new UserId($user->id);
        $this->mfaStore->disable($userId);
        $this->mfaChallengeStore->forgetForUser($userId);
        $this->refreshTokenStore?->revokeUser($userId);

        $this->currentAuthentication->store(AuthenticationContext::authenticated(
            user                : new AuthenticatedUser(
                                      id           : $user->id,
                                      email        : $user->email,
                                      username     : $user->username,
                                      roles        : $user->roles,
                                      permissions  : $user->permissions,
                                      emailVerified: $user->emailVerified,
                                      mfaEnabled   : false
                                  ),
            mode                : $context->mode(),
            sessionId           : $context->sessionId(),
            accessTokenId       : $context->accessTokenId(),
            accessTokenExpiresAt: $context->accessTokenExpiresAt(),
            refreshTokenId      : $context->refreshTokenId()
        ));

        $this->auditLog->record(new AuditEvent(
                                    name      : 'auth.mfa.disabled',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id' => $user->id,
                                                ]
                                ));
    }
}
