<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Capabilities\Sessions;

use Avax\Components\Identity\Foundation\State\ResettableIdentityState;
use Avax\Components\Identity\Foundation\Values\SessionId;

final class InMemorySessionStore implements SessionStore, ResettableIdentityState
{
    /** @var array<string, IdentitySession> */
    private array $sessions = [];

    public function find(SessionId $sessionId): IdentitySession|null
    {
        return $this->sessions[$sessionId->toString()] ?? null;
    }

    public function save(IdentitySession $session): void
    {
        $this->sessions[$session->sessionId()->toString()] = $session;
    }

    public function remove(SessionId $sessionId): void
    {
        unset($this->sessions[$sessionId->toString()]);
    }

    public function reset(): void
    {
        $this->sessions = [];
    }
}
