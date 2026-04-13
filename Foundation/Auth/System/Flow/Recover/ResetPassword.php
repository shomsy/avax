<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Recover;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Completes password reset by consuming a one-time token.
 */
final readonly class ResetPassword
{
    public function __construct(
        private UserSourceInterface                                    $userSource,
        #[\SensitiveParameter] private PasswordHasher                  $passwordHasher,
        #[\SensitiveParameter] private PasswordResetStoreInterface     $passwordResetStore,
        private AuditLogInterface                                      $auditLog,
        private Clock                                                  $clock,
        #[\SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        private MfaChallengeStoreInterface|null                        $mfaChallengeStore = null,
        #[\SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    public function execute(ResetPasswordData $data) : bool
    {
        $userId = $this->passwordResetStore->consume(token: $data->token, now: $this->clock->now());

        if ($userId === null) {
            $this->auditLog->record(event: new AuditEvent(
                                        name      : 'auth.password_reset.failed',
                                        occurredAt: $this->clock->now(),
                                        context   : [
                                                        'reason'     => 'invalid_token',
                                                        'ip_address' => $data->ipAddress,
                                                        'user_agent' => $data->userAgent,
                                                    ]
                                    ));

            return false;
        }

        $this->userSource->updatePassword(
            id          : $userId,
            passwordHash: $this->passwordHasher->hash(password: $data->newPassword)
        );
        $this->sessionRegistry?->revokeForUser(userId: $userId, revokedAt: $this->clock->now(), reason: 'password_reset');
        $this->mfaChallengeStore?->forgetForUser(userId: $userId);
        $this->refreshTokenStore?->revokeUser(userId: $userId);

        $this->auditLog->record(event: new AuditEvent(
                                    name      : 'auth.password_reset.completed',
                                    occurredAt: $this->clock->now(),
                                    context   : [
                                                    'user_id'    => $userId->value,
                                                    'ip_address' => $data->ipAddress,
                                                    'user_agent' => $data->userAgent,
                                                ]
                                ));

        return true;
    }
}
