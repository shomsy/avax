<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store;

use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;

/**
 * GenerateSessionId — contract for session ID generation.
 *
 * Adapted from the enterprise reference package.
 */
interface GenerateSessionId
{
    public function generate(): SessionId;
}
