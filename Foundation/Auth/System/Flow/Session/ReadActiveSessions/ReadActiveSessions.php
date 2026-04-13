<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\ReadActiveSessions;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Session\ActiveSession;
use Avax\Auth\System\Foundation\Clock;
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
            return [];
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
