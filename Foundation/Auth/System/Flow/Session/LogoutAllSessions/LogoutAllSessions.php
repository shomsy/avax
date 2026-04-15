<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\LogoutAllSessions;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryUnavailable;
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
    private RefreshTokenStoreInterface|null $refreshTokenStore;
    private SessionRegistryInterface|null   $sessionRegistry;
    private Clock                           $clock;
    private AuditLogInterface               $auditLog;
    private CurrentAuthentication           $currentAuthentication;
    private IdentityInterface               $identity;

    public function __construct(
        IdentityInterface                                     $identity,
        #[SensitiveParameter] CurrentAuthentication           $currentAuthentication,
        AuditLogInterface                                     $auditLog,
        Clock                                                 $clock,
        #[SensitiveParameter] SessionRegistryInterface|null   $sessionRegistry = null,
        #[SensitiveParameter] RefreshTokenStoreInterface|null $refreshTokenStore = null
    )
    {
        $this->identity              = $identity;
        $this->currentAuthentication = $currentAuthentication;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
        $this->refreshTokenStore     = $refreshTokenStore;
    }

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
                                                       ]
                                       ));
    }
}
