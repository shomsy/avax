<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\ChangePassword;

use Avax\Components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Components\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * High-level orchestrator for the password change process.
 *
 * Banal: The file that changes passwords.
 */
final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface                                   $userSource,
        #[SensitiveParameter] private PasswordHasher                  $passwordHasher,
        #[SensitiveParameter] private IdentityInterface               $identity,
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        private MfaChallengeStoreInterface|null                       $mfaChallengeStore = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private LoginRateLimit|null                                   $rateLimit = null,
        private RequireFreshMfa|null                                  $requireFreshMfa = null
    ) {}

    /**
     * @throws PasswordChangeFailed
     * @throws Unauthenticated
     * @throws RateLimitException
     */
    public function execute(ChangePasswordData $data) : void
    {
        $context     = $this->currentAuthentication->read();
        $currentUser = $context->user();

        if ($currentUser === null) {
            throw new Unauthenticated();
        }

        $user = $this->userSource->findById(id: new UserId(value: $currentUser->id));

        if ($user === null || ! $user->isActive()) {
            throw new Unauthenticated();
        }

        if ($currentUser->mfaEnabled) {
            if ($this->requireFreshMfa === null) {
                throw new FreshMfaRequired(maxAgeSeconds: 300);
            }

            $this->requireFreshMfa->execute();
        }

        $this->rateLimit?->check(identifier: (string) $user->getId());

        if (! $this->passwordHasher->verify(password: $data->currentPassword, hash: $user->getPasswordHash())) {
            $this->rateLimit?->recordFailed(identifier: (string) $user->getId());

            throw PasswordChangeFailed::currentPasswordMismatch();
        }

        $newHash = $this->passwordHasher->hash(password: $data->newPassword);

        $this->userSource->updatePassword(id: $user->getId(), passwordHash: $newHash);
        $this->sessionRegistry?->revokeForUser(userId: $user->getId(), revokedAt: $this->clock->now(), reason: 'password_changed');
        $this->mfaChallengeStore?->forgetForUser(userId: $user->getId());
        $this->refreshTokenStore?->revokeUser(userId: $user->getId());
        $this->identity->clear(context: $context);
        $this->currentAuthentication->clear();

        $this->rateLimit?->reset(identifier: (string) $user->getId());
        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.password.changed',
                                           occurredAt: $this->clock->now(),
                                           context   : [
                                                           'user_id' => $user->getId()->value,
                                                       ]
                                       ));
    }
}
