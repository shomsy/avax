<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangePassword;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * High-level orchestrator for the password change process.
 *
 * Banal: The file that changes passwords.
 */
final readonly class ChangePassword
{
    public function __construct(
        private UserSourceInterface                                    $userSource,
        #[SensitiveParameter] private PasswordHasher                   $passwordHasher,
        #[SensitiveParameter] private IdentityInterface                $identity,
        #[\SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                      $auditLog,
        private Clock                                                  $clock,
        #[\SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        private MfaChallengeStoreInterface|null                        $mfaChallengeStore = null,
        #[\SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null,
        private LoginRateLimit|null                                    $rateLimit = null,
        private RequireFreshMfa|null                                   $requireFreshMfa = null
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
