<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Configuration\Graphs;

use Avax\Components\Identity\Capabilities\Sessions\SessionStore;
use Avax\Components\Identity\Flows\StartSession\StartSession;

final readonly class SessionGraph
{
    public function __construct(private StartSession $startSession, private SessionStore $sessions) {}

    public function startSession(): StartSession
    {
        return $this->startSession;
    }

    public function sessions(): SessionStore
    {
        return $this->sessions;
    }
}
