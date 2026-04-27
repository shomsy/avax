<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\StartSession;

use Avax\Components\Session\System\Capabilities\Security\SessionId;

final class CreateSessionId
{
    public function create(): SessionId
    {
        return SessionId::generate();
    }
}