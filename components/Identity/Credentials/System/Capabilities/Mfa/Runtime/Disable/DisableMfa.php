<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Disable;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use SensitiveParameter;

/**
 * Disables MFA after fresh proof and clears recovery material.
 */
final readonly class DisableMfa
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private MfaStoreInterface $mfaStore,
        private MfaChallengeStoreInterface $mfaChallengeStore,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter]
        private ?RefreshTokenStoreInterface $refreshTokenStore = null,
    ) {}

    /**
     * @throws Unauthenticated
     * @throws FreshMfaRequired
     */
    public function execute(): void
    {
        $context = $this->currentAuthentication->read();
        $user = $context->user();

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
                mfaEnabled   : false,
            ),
            mode                : $context->mode(),
            sessionId           : $context->sessionId(),
            accessTokenId       : $context->accessTokenId(),
            accessTokenExpiresAt: $context->accessTokenExpiresAt(),
            refreshTokenId      : $context->refreshTokenId(),
        ));

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.mfa.disabled',
            occurredAt: $this->clock->now(),
            context   : [
                'user_id' => $user->id,
            ],
        ));
    }
}
