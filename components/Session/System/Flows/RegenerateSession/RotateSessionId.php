<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Flows\RegenerateSession;

use Avax\Components\Session\System\Capabilities\Security\SessionId;

final class RotateSessionId
{
    public function rotate(SessionId $oldId): SessionId
    {
        return SessionId::generate();
    }
}