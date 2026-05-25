<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Sessions;

use Avax\Components\Identity\Foundation\Values\SessionId;

interface SessionStore
{
    public function find(SessionId $sessionId): IdentitySession|null;

    public function save(IdentitySession $session): void;

    public function remove(SessionId $sessionId): void;
}
