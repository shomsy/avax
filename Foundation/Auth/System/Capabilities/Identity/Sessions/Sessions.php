<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Sessions;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ActiveSession;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\System\Capabilities\Identity\Sessions\Runtime\RevokeSession\RevokeSession;
use SensitiveParameter;

final readonly class Sessions
{
    public function __construct(
        #[SensitiveParameter] private LogoutAllSessions  $logoutAllSessions,
        #[SensitiveParameter] private ReadActiveSessions $readActiveSessions,
        #[SensitiveParameter] private RevokeSession      $revokeSession
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function logoutAllSessions() : void
    {
        $this->logoutAllSessions->execute();
    }

    /**
     * @return list<ActiveSession>
     * @throws Unauthenticated
     */
    public function readActiveSessions() : array
    {
        return $this->readActiveSessions->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function revokeSession(#[SensitiveParameter] string $sessionId) : void
    {
        $this->revokeSession->execute(sessionId: $sessionId);
    }
}
