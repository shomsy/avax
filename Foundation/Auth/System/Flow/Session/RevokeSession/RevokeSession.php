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
        private IdentityInterface                                    $identity,
        #[\SensitiveParameter] private CurrentAuthentication         $currentAuthentication,
        private AuditLogInterface                                    $auditLog,
        private Clock                                                $clock,
        #[\SensitiveParameter] private SessionRegistryInterface|null $sessionRegistry = null
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute(#[\SensitiveParameter] string $sessionId) : void
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($this->sessionRegistry === null) {
            return;
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
