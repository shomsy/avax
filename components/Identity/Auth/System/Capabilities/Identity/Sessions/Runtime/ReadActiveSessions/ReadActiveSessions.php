<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\System\Foundation\Clock;
use SensitiveParameter;

/**
 * Reads the current user's tracked sessions.
 */
final readonly class ReadActiveSessions
{
    public function __construct(
        #[SensitiveParameter] private CurrentAuthentication         $currentAuthentication,
        private Clock                                               $clock,
        #[SensitiveParameter] private SessionRegistryInterface|null $sessionRegistry = null
    ) {}

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function execute() : array
    {
        $context = $this->currentAuthentication->read();
        $user    = $context->user();

        if ($user === null) {
            throw new Unauthenticated();
        }

        if ($this->sessionRegistry === null) {
            throw SessionRegistryUnavailable::forSessionManagementFlow();
        }

        $currentSessionId = $context->sessionId();
        $now              = $this->clock->now();
        $sessions         = [];

        foreach ($this->sessionRegistry->listForUser(userId: new UserId(value: $user->id)) as $record) {
            if (! $record->isActiveAt(moment: $now)) {
                continue;
            }

            $sessions[] = $this->toBoundary(record: $record, currentSessionId: $currentSessionId);
        }

        return $sessions;
    }

    private function toBoundary(SessionRecord $record, #[SensitiveParameter] string|null $currentSessionId) : ActiveSession
    {
        return new ActiveSession(
            sessionId        : $record->sessionId,
            createdAt        : $record->createdAt,
            lastSeenAt       : $record->lastSeenAt,
            idleExpiresAt    : $record->idleExpiresAt,
            absoluteExpiresAt: $record->absoluteExpiresAt,
            ipAddress        : $record->ipCreated,
            userAgent        : $record->userAgentCreated,
            current          : $record->sessionId === $currentSessionId,
            revokedAt        : $record->revokedAt,
            revokeReason     : $record->revokeReason
        );
    }
}
