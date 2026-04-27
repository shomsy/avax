<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\RegenerateSession;

use Avax\Components\Session\System\Capabilities\Security\SessionId;
use Avax\Components\Session\System\Capabilities\Storage\SessionStore;

final class RegenerateSession
{
    public function __construct(
        private readonly SessionStore $store,
    ) {
    }

    public function regenerate(): SessionId
    {
        $oldSessionId = $this->store->current()->id()->toString();

        $newSessionId = SessionId::generate();

        $this->store->regenerate(oldSessionId: $oldSessionId);

        return $newSessionId;
    }
}