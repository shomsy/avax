<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration\Graphs;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\GenerateSessionId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\SessionStore;
use Avax\Components\Identity\Auth\System\Flows\StartSession\StartSession;

/**
 * SessionGraph — composition root for session management.
 *
 * Adapted from the enterprise reference package.
 * Groups the StartSession flow with the SessionStore and GenerateSessionId
 * for complete session lifecycle management.
 */
final readonly class SessionGraph
{
    public function __construct(
        private StartSession $startSession,
        private SessionStore $sessions,
        private GenerateSessionId $sessionIds,
    ) {}

    public function startSession(): StartSession
    {
        return $this->startSession;
    }

    public function sessions(): SessionStore
    {
        return $this->sessions;
    }

    public function sessionIds(): GenerateSessionId
    {
        return $this->sessionIds;
    }
}
