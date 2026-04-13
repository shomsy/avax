<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\LogoutAllSessions;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Revokes every tracked session for the current user.
 */
final readonly class LogoutAllSessions
{
    public function __construct(
        private IdentityInterface                                     $identity,
        #[SensitiveParameter] private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface                                     $auditLog,
        private Clock                                                 $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] private RefreshTokenStoreInterface|null $refreshTokenStore = null
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute() : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        $userId = new UserId(value: $user->id);
        $now    = $this->clock->now();

        $this->sessionRegistry?->revokeForUser(userId: $userId, revokedAt: $now, reason: 'logout_all');
        $this->refreshTokenStore?->revokeUser(userId: $userId);
        $this->identity->clear(context: $context);
        $this->currentAuthentication->clear();

        $this->auditLog->record(event: new AuditEvent(
            name      : 'auth.sessions.revoked',
            occurredAt: $now,
            context   : [
                'user_id' => $user->id,
                'reason'  => 'logout_all',
            ]
        ));
    }
}
