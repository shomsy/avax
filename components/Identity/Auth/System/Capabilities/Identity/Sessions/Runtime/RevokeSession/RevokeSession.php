<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditEvent;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\AuditLogInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Revokes one tracked session owned by the current user.
 */
final readonly class RevokeSession
{
    public function __construct(
        private IdentityInterface         $identity,
        #[SensitiveParameter]
        private CurrentAuthentication     $currentAuthentication,
        private AuditLogInterface         $auditLog,
        private Clock                     $clock,
        #[SensitiveParameter]
        private ?SessionRegistryInterface $sessionRegistry = null,
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function execute(#[SensitiveParameter] string $sessionId) : void
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user                  = $authenticationContext->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $this->sessionRegistry instanceof SessionRegistryInterface) {
            throw SessionRegistryUnavailable::forSessionManagementFlow();
        }

        $record = $this->sessionRegistry->find(sessionId: $sessionId);

        if (! $record instanceof SessionRecord || ! $record->userId->equals(other: new UserId(value: $user->id))) {
            return;
        }

        $now = $this->clock->now();
        $this->sessionRegistry->revoke(sessionId: $sessionId, revokedAt: $now, reason: 'user_revoke');

        if ($authenticationContext->sessionId() === $sessionId) {
            $this->identity->clear(context: $authenticationContext);
            $this->currentAuthentication->clear();

        }

        $this->auditLog->record(event: new AuditEvent(
                                           name      : 'auth.session.revoked',
                                           occurredAt: $now,
                                           context   : [
                                                           'user_id'    => $user->id,
                                                           'session_id' => $sessionId,
                                                           'reason'     => 'user_revoke',
                                                       ],
                                       ));
    }
}
