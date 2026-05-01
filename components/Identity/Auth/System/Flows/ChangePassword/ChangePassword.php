<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangePassword;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Components\Identity\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Security\Hashing\System\Capabilities\PasswordHashing\PasswordHasher;
use SensitiveParameter;

/**
 * High-level orchestrator for the password change process.
 *
 * Banal: The file that changes passwords.
 */
final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface $userSource,
        #[SensitiveParameter]
        private PasswordHasher $passwordHasher,
        #[SensitiveParameter]
        private IdentityInterface $identity,
        #[SensitiveParameter]
        private CurrentAuthentication $currentAuthentication,
        private AuditLogInterface $auditLog,
        private Clock $clock,
        #[SensitiveParameter]
        private ?SessionRegistryInterface $sessionRegistry = null,
        private ?MfaChallengeStoreInterface $mfaChallengeStore = null,
        #[SensitiveParameter]
        private ?RefreshTokenStoreInterface $refreshTokenStore = null,
        private ?LoginRateLimit $rateLimit = null,
        private ?RequireFreshMfa $requireFreshMfa = null,
    ) {}

    /**
     * @throws PasswordChangeFailed
     * @throws Unauthenticated
     * @throws RateLimitException
     */
    public function execute(ChangePasswordData $data): void
    {
        $context = $this->currentAuthentication->read();
        $currentUser = $context->user();

        if ($currentUser === null) {
            throw new Unauthenticated;
        }

        $user = $this->userSource->findById(id: new UserId(value: $currentUser->id));

        if ($user === null || ! $user->isActive()) {
            throw new Unauthenticated;
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
            ],
        ));
    }
}
