<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryUnavailable;
use Avax\Components\Identity\Tokens\System\Capabilities\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Revokes every tracked session for the current user.
 */
final readonly class LogoutAllSessions
{
    public function __construct(
        private IdentityInterface               $identity,
        #[SensitiveParameter]
        private CurrentAuthentication           $currentAuthentication,
        private AuditLogInterface               $auditLog,
        private Clock                           $clock,
        #[SensitiveParameter]
        private SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter]
        private RefreshTokenStoreInterface|null $refreshTokenStore = null,
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

        if ($this->sessionRegistry === null) {
            throw SessionRegistryUnavailable::forSessionManagementFlow();
        }

        $userId = new UserId(value: $user->id);
        $now    = $this->clock->now();

        $this->sessionRegistry->revokeForUser(userId: $userId, revokedAt: $now, reason: 'logout_all');
        $this->refreshTokenStore?->revokeUser(userId: $userId);
        $this->identity->clear(context: $context);
        $this->currentAuthentication->clear();

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.sessions.revoked',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id' => $user->id,
                                                           'reason'  => 'logout_all',
                                                       ],
                                       ));
    }
}
