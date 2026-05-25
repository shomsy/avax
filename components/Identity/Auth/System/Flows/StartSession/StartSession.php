<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\StartSession;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\GenerateSessionId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\SessionStore;
use Avax\Components\Identity\Auth\System\Foundation\Time\ClockInterface;

/**
 * StartSession — flow for starting authenticated user sessions.
 *
 * Adapted from the enterprise reference package.
 * Generates a session ID, creates an IdentitySession with the requested
 * TTL and tenant, persists it to the session store, and returns StartedSession.
 */
final readonly class StartSession
{
    public function __construct(
        private GenerateSessionId $sessionIds,
        private SessionStore $sessions,
        private ClockInterface $clock,
    ) {}

    public function start(StartSessionRequest $request): StartedSession
    {
        $now = $this->clock->now();
        $session = new \Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store\IdentitySession(
            sessionId: $this->sessionIds->generate(),
            userId: $request->userId(),
            createdAt: $now,
            expiresAt: $now->add($request->ttl()),
            tenantId: $request->tenantId(),
        );

        $this->sessions->save($session);

        return new StartedSession($session);
    }
}
