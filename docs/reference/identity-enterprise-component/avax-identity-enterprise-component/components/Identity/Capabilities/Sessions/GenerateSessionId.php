<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Sessions;

use Avax\Components\Identity\Foundation\Values\SessionId;

interface GenerateSessionId
{
    public function generate(): SessionId;
}
