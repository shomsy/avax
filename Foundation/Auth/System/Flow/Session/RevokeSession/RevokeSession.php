<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\RevokeSession;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\AuditEvent;
use Avax\Auth\System\Flow\Diagnostics\AuditLogInterface;
use Avax\Auth\System\Foundation\Clock;

/**
 * Revokes one tracked session owned by the current user.
 */
final readonly class RevokeSession
{
    public function __construct(
        private IdentityInterface             $identity,
        private CurrentAuthentication         $currentAuthentication,
        private AuditLogInterface             $auditLog,
        private Clock                         $clock,
        private SessionRegistryInterface|null $sessionRegistry = null
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute(string $sessionId) : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($this->sessionRegistry === null) {
            return;
        }

        $record = $this->sessionRegistry->find($sessionId);

        if ($record === null || ! $record->userId->equals(new UserId($user->id))) {
            return;
        }

        $now = $this->clock->now();
        $this->sessionRegistry->revoke($sessionId, $now, 'user_revoke');

        if ($context->sessionId() === $sessionId) {
            $this->identity->clear($context);
            $this->currentAuthentication->clear();
        }

        $this->auditLog->record(new AuditEvent(
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
