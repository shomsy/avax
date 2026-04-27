<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\StartSession;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;
use Avax\Components\Session\System\Capabilities\Security\SessionId;

final class StartSession
{
    public function __construct(
        private readonly SessionStore $store,
    ) {
    }

    public function start(): SessionId
    {
        $sessionId = SessionId::generate();

        $this->store->open(sessionId: $sessionId->toString());

        return $sessionId;
    }
}