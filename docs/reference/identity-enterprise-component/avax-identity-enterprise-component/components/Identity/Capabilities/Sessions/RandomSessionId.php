<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Sessions;

use Avax\Components\Identity\Foundation\Values\SessionId;

final class RandomSessionId implements GenerateSessionId
{
    public function generate(): SessionId
    {
        return SessionId::fromString(bin2hex(random_bytes(32)));
    }
}
