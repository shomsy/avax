<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\Recover;

use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Session\SessionRegistryInterface;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\Diagnostics\AuditEvent;
use Avax\Auth\System\Flows\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flows\Mfa\Challenge\MfaChallengeStoreInterface;
use Avax\Auth\System\Flows\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Completes password reset by consuming a one-time token.
 */
final readonly class ResetPassword
{
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private MfaChallengeStoreInterface|null $mfaChallengeStore;
    private SessionRegistryInterface|null   $sessionRegistry;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private PasswordResetStoreInterface     $passwordResetStore;
    private PasswordHasher                  $passwordHasher;
    private UserSourceInterface             $userSource;

    public function __construct(
        UserSourceInterface                                   $userSource,
        #[SensitiveParameter] PasswordHasher                  $passwordHasher,
        #[SensitiveParameter] PasswordResetStoreInterface     $passwordResetStore,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        MfaChallengeStoreInterface|null                       $mfaChallengeStore = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null
    )
    {
        $this->userSource         = $userSource;
        $this->passwordHasher     = $passwordHasher;
        $this->passwordResetStore = $passwordResetStore;
        $this->auditLog           = $auditLog;
        $this->clock              = $clock;
        $this->sessionRegistry    = $sessionRegistry;
        $this->mfaChallengeStore  = $mfaChallengeStore;
        $this->refreshTokenStore  = $refreshTokenStore;
    }

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
