<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\StartSession;

use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

final class LoadSessionState
{
    public function __construct(
        private readonly SessionStore $store,
    ) {
    }

    public function load(string $sessionId): array
    {
        return $this->store->open(sessionId: $sessionId)->all();
    }
}