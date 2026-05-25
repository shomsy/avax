<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Flows\StartSession;

use Avax\Components\Identity\Capabilities\Sessions\GenerateSessionId;
use Avax\Components\Identity\Capabilities\Sessions\IdentitySession;
use Avax\Components\Identity\Capabilities\Sessions\SessionStore;
use Avax\Components\Identity\Foundation\Time\Clock;

final readonly class StartSession
{
    public function __construct(private GenerateSessionId $sessionIds, private SessionStore $sessions, private Clock $clock) {}

    public function start(StartSessionRequest $request): StartedSession
    {
        $now = $this->clock->now();
        $session = new IdentitySession(
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
