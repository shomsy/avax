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
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\MfaStoreInterface;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Disables MFA after fresh proof and clears recovery material.
 */
final readonly class DisableMfa
{
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private MfaChallengeStoreInterface      $mfaChallengeStore;
    private MfaStoreInterface               $mfaStore;
    private RequireFreshMfa                 $requireFreshMfa;
    private CurrentAuthentication           $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        RequireFreshMfa                                       $requireFreshMfa,
        MfaStoreInterface                                     $mfaStore,
        MfaChallengeStoreInterface                            $mfaChallengeStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->requireFreshMfa       = $requireFreshMfa;
        $this->mfaStore              = $mfaStore;
        $this->mfaChallengeStore     = $mfaChallengeStore;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->refreshTokenStore     = $refreshTokenStore;
    }

    /**
     * @throws Unauthenticated
     * @throws FreshMfaRequired
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
        $userId = new UserId(value: $user->id);
        $this->mfaStore->disable(userId: $userId);
        $this->mfaChallengeStore->forgetForUser(userId: $userId);
        $this->refreshTokenStore?->revokeUser(userId: $userId);

        $this->currentAuthentication->store(context: AuthenticationContext::authenticated(
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

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.mfa.disabled',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id' => $user->id,
                                                       ]
                                       ));
    }
}
