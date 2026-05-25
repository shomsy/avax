<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Store;

use Avax\Components\Identity\Auth\System\Foundation\Ids\SessionId;

/**
 * SessionStore — contract for session persistence during runtime.
 *
 * Adapted from the enterprise reference package.
 * Used by the StartSession flow to persist new sessions.
 */
interface SessionStore
{
    public function find(SessionId $sessionId): IdentitySession|null;

    public function save(IdentitySession $session): void;

    public function remove(SessionId $sessionId): void;
}
