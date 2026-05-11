<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions;

use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Reads the current user's tracked sessions.
 */
final readonly class ReadActiveSessions
{
    public function __construct(
        #[SensitiveParameter]
        private CurrentAuthentication     $currentAuthentication,
        private Clock                     $clock,
        #[SensitiveParameter]
        private SessionRegistryInterface|null $sessionRegistry = null,
    ) {}

    /**
     * @return list<ActiveSession>
     *
     * @throws Unauthenticated
     */
    public function execute() : array
    {
        $authenticationContext = $this->currentAuthentication->read();
        $user                  = $authenticationContext->user();

        if (! $user instanceof AuthenticatedUser) {
            throw new Unauthenticated();
        }

        if (! $this->sessionRegistry instanceof SessionRegistryInterface) {
            throw SessionRegistryUnavailable::forSessionManagementFlow();
        }

        $currentSessionId = $authenticationContext->sessionId();
        $now              = $this->clock->now();
        $sessions         = [];

        foreach ($this->sessionRegistry->listForUser(userId: new UserId(value: $user->id)) as $sessionRecord) {
            if (! $sessionRecord->isActiveAt(moment: $now)) {
                continue;
            }

            $sessions[] = $this->toBoundary(currentSessionId: $currentSessionId, record: $sessionRecord);
        }

        return $sessions;
    }

    private function toBoundary(SessionRecord $sessionRecord, #[SensitiveParameter] ?string $currentSessionId) : ActiveSession
    {
        return new ActiveSession(
            sessionId        : $sessionRecord->sessionId,
            createdAt        : $sessionRecord->createdAt,
            lastSeenAt       : $sessionRecord->lastSeenAt,
            idleExpiresAt    : $sessionRecord->idleExpiresAt,
            absoluteExpiresAt: $sessionRecord->absoluteExpiresAt,
            ipAddress        : $sessionRecord->ipCreated,
            userAgent        : $sessionRecord->userAgentCreated,
            current          : $sessionRecord->sessionId === $currentSessionId,
            revokedAt        : $sessionRecord->revokedAt,
            revokeReason     : $sessionRecord->revokeReason,
        );
    }
}
