<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store;

use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;

/**
 * RandomSessionId — cryptographically secure session ID generator.
 *
 * Adapted from the enterprise reference package.
 * Generates 32-byte random session IDs as hex strings.
 */
final class RandomSessionId implements GenerateSessionId
{
    public function generate(): SessionId
    {
        return new SessionId(bin2hex(random_bytes(32)));
    }
}
