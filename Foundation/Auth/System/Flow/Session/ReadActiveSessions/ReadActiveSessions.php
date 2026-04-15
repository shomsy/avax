<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session\ReadActiveSessions;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\Session\SessionRegistryInterface;
use Avax\Auth\System\Capability\Session\SessionRegistryUnavailable;
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
    private SessionRegistryInterface|null $sessionRegistry;
    private Clock                         $clock;
    private CurrentAuthentication         $currentAuthentication;

    public function __construct(
        #[SensitiveParameter] CurrentAuthentication         $currentAuthentication,
        Clock                                               $clock,
        #[SensitiveParameter] SessionRegistryInterface|null $sessionRegistry = null
    )
    {
        $this->currentAuthentication = $currentAuthentication;
        $this->clock                 = $clock;
        $this->sessionRegistry       = $sessionRegistry;
    }

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
