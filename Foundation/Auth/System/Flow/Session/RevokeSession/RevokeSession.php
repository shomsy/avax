<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\RevokeSession;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryUnavailable;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Revokes one tracked session owned by the current user.
 */
final readonly class RevokeSession
{
    private SessionRegistryInterface|null $sessionRegistry;
    private Clock                         $clock;
    private AuditLogInterface             $auditLog;
    private CurrentAuthentication         $currentAuthentication;
    private IdentityInterface             $identity;

    public function __construct(
        IdentityInterface                                   $identity,
        #[SensitiveParameter] CurrentAuthentication         $currentAuthentication,
        AuditLogInterface                                   $auditLog,
        Clock                                               $clock,
        #[SensitiveParameter] SessionRegistryInterface|null $sessionRegistry = null
    )
    {
        $this->identity              = $identity;
        $this->currentAuthentication = $currentAuthentication;
        $this->auditLog              = $auditLog;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
    }

    /**
     * @throws Unauthenticated
     */
    public function execute(#[SensitiveParameter] string $sessionId) : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($this->sessionRegistry === null) {
            throw SessionRegistryUnavailable::forSessionManagementFlow();
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if ($record === null || ! $record->userId->equals(other: new UserId(value: $user->id))) {
            return;
        }

        $now = $this->clock->now();
        $this->sessionRegistry->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'user_revoke');

        if ($context->sessionId() === $sessionId) {
            $this->identity->clear(context: $context);
            $this->currentAuthentication->clear();

        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.session.revoked',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $user->id,
                                                           'session_id' => $sessionId,
                                                           'reason'     => 'user_revoke',
                                                       ]
                                       ));
    }
}
